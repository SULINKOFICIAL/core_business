<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\OrderTransaction;
use App\Services\OrderService;
use App\Services\PagHiperService;
use Carbon\Carbon;

class PaymentPlanService
{
    public function __construct(
        private OrderService $orderService,
        private PagHiperPixProviderService $pixProviderService,
        private PagHiperService $pagHiperService,
    ) {
    }

    /**
     *
     * Processa pagamento de plano no fluxo novo por provider.
     *
     */
    public function processPlanPayment($tenant, string $billingCycle, array $clientInfo): array
    {
        $plan  = $this->orderService->getPlanInProgress($tenant);
        $order = $this->orderService->getOrderInProgress($tenant, $plan);

        return $this->createPixCharge($tenant, $plan, $order, $billingCycle, $clientInfo);
    }

    /**
     *
     * Consulta status canônico da transação PIX e atualiza snapshot local.
     *
     */
    public function getPixStatus($tenant, string $providerTransactionId): array
    {
        $transaction = OrderTransaction::with(['order'])
            ->where('provider', 'paghiper')
            ->where('provider_method', 'pix')
            ->where('provider_transaction_id', $providerTransactionId)
            ->first();

        if (!$transaction || !$transaction->order || $transaction->order->tenant_id != $tenant->id) {
            return [
                'success' => false,
                'message' => 'Transação PIX não encontrada.',
            ];
        }

        $notificationId = $this->resolveNotificationId($transaction);
        $rawResponse = $this->pagHiperService->notification('https://pix.paghiper.com', $providerTransactionId, $notificationId);
        $statusNode = $rawResponse['status_request'];
        $status = $this->normalizeStatus($statusNode['status']);

        $response = $transaction->response;
        $response['status_request'] = $statusNode;

        $transaction->update([
            'status'                => $status,
            'response'              => $response,
            'raw_response_snapshot' => $rawResponse,
            'paid_at'               => $status === 'approved' ? now() : $transaction->paid_at,
        ]);

        $transaction->order->update([
            'status'           => $this->resolveOrderStatus($status),
            'provider_message' => $statusNode['response_message'],
            'paid_at'          => $status === 'approved' ? now() : $transaction->order->paid_at,
        ]);

        return $this->buildPixPaymentResponse($transaction->fresh(['order']));
    }

    /**
     *
     * Gera cobrança PIX e persiste transação local com idempotência.
     *
     */
    private function createPixCharge($tenant, $plan, Order $order, string $billingCycle, array $clientInfo): array
    {
        $this->orderService->recalculateOrderTotals($order);
        $order->refresh();

        if (!$order->total_amount || $order->total_amount <= 0) {
            return [
                'success' => false,
                'message' => 'Pedido sem valor para gerar PIX.',
            ];
        }

        $existingPending = OrderTransaction::where('order_id', $order->id)
            ->where('provider', 'paghiper')
            ->where('provider_method', 'pix')
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if ($existingPending && $this->isTransactionStillValid($existingPending)) {
            return $this->buildPixPaymentResponse($existingPending);
        }

        $chargeData = $this->pixProviderService->createCharge($order, $tenant, $clientInfo);

        if ($chargeData->status === 'failed') {
            return [
                'success' => false,
                'message' => $chargeData->providerMessage,
            ];
        }

        $orderStatus = $chargeData->status === 'approved' ? 'paid' : 'pending';

        $order->update([
            'status'           => $orderStatus,
            'provider'         => $chargeData->provider,
            'provider_method'  => $chargeData->providerMethod,
            'provider_message' => $chargeData->providerMessage,
            'currency'         => $order->currency,
        ]);

        $plan->update([
            'progress' => $chargeData->status === 'approved' ? 'completed' : 'draft',
        ]);

        $rawResponse = $chargeData->rawResponse;
        $rawResponse['_meta'] = [
            'billing_cycle' => $billingCycle,
            'created_at'    => now()->toDateTimeString(),
        ];

        $transaction = OrderTransaction::updateOrCreate([
            'provider'                => $chargeData->provider,
            'provider_transaction_id' => $chargeData->providerTransactionId,
        ], [
            'order_id'              => $order->id,
            'subscription_id'       => $order->subscription_id,
            'provider_method'       => $chargeData->providerMethod,
            'status'                => $chargeData->status,
            'currency'              => $order->currency,
            'amount'                => $order->total_amount,
            'response'              => $rawResponse,
            'raw_response_snapshot' => $rawResponse,
            'paid_at'               => $chargeData->status === 'approved' ? now() : null,
        ]);

        return $this->buildPixPaymentResponse($transaction);
    }

    /**
     *
     * Monta payload canônico consumido pelo coresulink na etapa PIX.
     *
     */
    private function buildPixPaymentResponse(OrderTransaction $transaction): array
    {
        $raw     = $transaction->response;
        $pixNode = $raw['pix_create_request'] ?? [];
        $status  = $this->normalizeStatus($transaction->status);

        return [
            'success'         => true,
            'payment_flow'    => 'pix',
            'transaction_id'  => $transaction->provider_transaction_id,
            'status'          => $status,
            'provider'        => $transaction->provider,
            'provider_method' => $transaction->provider_method,
            'message'         => $pixNode['response_message'] ?? null,
            'pix'             => [
                'qr_code_base64' => $pixNode['pix_code']['qrcode_base64'] ?? null,
                'copy_paste'     => $pixNode['pix_code']['emv'] ?? null,
                'expires_at'     => $this->resolveExpirationFromTransaction($transaction),
            ],
        ];
    }

    /**
     *
     * Resolve vencimento do QR Code a partir do snapshot salvo da criação PIX.
     *
     */
    private function resolveExpirationFromTransaction(OrderTransaction $transaction): ?string
    {
        $response         = $transaction->response;
        $pixCreateRequest = $response['pix_create_request'] ?? [];
        $dueDate          = $pixCreateRequest['due_date'] ?? null;

        if (!$dueDate) {
            return null;
        }

        try {
            return Carbon::parse($dueDate)->toDateTimeString();
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     *
     * Define se a transação pendente ainda pode ser reaproveitada no checkout.
     *
     */
    private function isTransactionStillValid(OrderTransaction $transaction): bool
    {
        $expiresAt = $this->resolveExpirationFromTransaction($transaction);

        if (!$expiresAt) {
            return true;
        }

        try {
            return Carbon::parse($expiresAt)->isFuture();
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     *
     * Uniformiza variações de status para o contrato exposto à interface.
     *
     */
    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower($status);

        return match ($normalized) {
            'paid', 'approved'      => 'approved',
            'pending'               => 'pending',
            'canceled', 'cancelled' => 'canceled',
            'failed'                => 'failed',
            'expired'               => 'expired',
            default                 => $normalized,
        };
    }

    /**
     *
     * Traduz status de transação para status final de pedido.
     *
     */
    private function resolveOrderStatus(string $transactionStatus): string
    {
        return match ($transactionStatus) {
            'approved' => 'paid',
            'pending'  => 'pending',
            'canceled' => 'canceled',
            'expired'  => 'expired',
            'failed'   => 'failed',
            default    => 'pending',
        };
    }

    /**
     *
     * Resolve o notification_id salvo na última consulta ou no snapshot da transação.
     *
     */
    private function resolveNotificationId(OrderTransaction $transaction): string
    {
        $response = $transaction->response;

        if (isset($response['status_request']) && isset($response['status_request']['notification_id'])) {
            return $response['status_request']['notification_id'];
        }

        if (isset($response['notification_request']) && isset($response['notification_request']['notification_id'])) {
            return $response['notification_request']['notification_id'];
        }

        return $transaction->provider_transaction_id;
    }
}
