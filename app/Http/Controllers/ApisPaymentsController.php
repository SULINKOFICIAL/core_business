<?php

namespace App\Http\Controllers;

use App\Models\TenantCard;
use App\Services\Payments\PaymentPlanService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class ApisPaymentsController extends Controller
{

    /**
     * Processa o pagamento do pedido em andamento do cliente.
     * Resolve pedido/cartão e delega a cobrança para o serviço de pedidos.
     */
    public function orderPayment(Request $request, OrderService $service, PaymentPlanService $paymentPlanService)
    {
        /**
         * Payload já normalizado pelo checkout do modal de assinatura.
         */
        $data = $request->all();

        $paymentType = $data['payment_type'];
        $billingCycle = $data['billing_cycle'];
        $clientInfo = $data['client_info'];

        if ($paymentType === 'pix') {
            /**
             * No fluxo PIX toda a orquestração fica no PaymentPlanService,
             * mantendo o controller como camada fina de entrada/saída.
             */
            $response = $paymentPlanService->processPlanPayment(
                $data['tenant'],
                $billingCycle,
                $clientInfo,
            );

            if (!$response['success']) {
                return response()->json([
                    'message' => $response['message'],
                ], 422);
            }

            return response()->json($response, 200);
        }

        /**
         * Fluxos não PIX continuam no caminho atual de cartão/boleto.
         */
        $plan = $service->getPlanInProgress($data['tenant']);

        $order = $service->getOrderInProgress($data['tenant'], $plan);

        $cardResult = $this->resolveCardFromRequest($data);

        if ($cardResult['error']) {
            return response()->json([
                'message' => $cardResult['error'],
            ], $cardResult['status']);
        }

        $card = $cardResult['card'];

        $response = $service->createOrderPayment(
            $plan,
            $order,
            $data['tenant'],
            $clientInfo,
            $card,
            $this->extractCardCvv($data),
            $billingCycle,
        );

        return response()->json([
            'message' => $response
        ], 200);
    }

    /**
     * Consulta status canônico da transação PIX do pedido de assinatura.
     */
    public function paymentStatus(Request $request, PaymentPlanService $paymentPlanService)
    {
        $data                  = $request->all();
        $providerTransactionId = $data['transaction_id'];

        $response = $paymentPlanService->getPixStatus($data['tenant'], $providerTransactionId);

        if (!$response['success']) {
            return response()->json($response, 404);
        }

        return response()->json($response, 200);
    }

    /**
     * Resolve o cartão que será utilizado no pagamento.
     * Aceita cartão existente por id ou cria/reutiliza cartão enviado no payload.
     */
    private function resolveCardFromRequest(array &$data): array
    {
        if (isset($data['card_id'])) {
            $existingCard = TenantCard::where('tenant_id', $data['tenant']->id)
                ->where('id', $data['card_id'])
                ->first();

            if (!$existingCard) {
                return [
                    'card'   => null,
                    'error'  => 'Cartão não encontrado para esse cliente',
                    'status' => 404,
                ];
            }

            return [
                'card'   => $existingCard,
                'error'  => null,
                'status' => 200,
            ];
        }

        if ($this->hasIncompleteCardPayload($data)) {
            return [
                'card'   => null,
                'error'  => 'Parâmetros faltando',
                'status' => 400,
            ];
        }

        if (!$this->hasNewCardPayload($data)) {
            return [
                'card'   => null,
                'error'  => 'Cartão não informado',
                'status' => 400,
            ];
        }

        /**
         * Número limpo para busca e deduplicação do cartão no tenant.
         */
        $data['card']['number'] = $this->normalizeCardNumber($data['card']['number']);

        $card = TenantCard::where('tenant_id', $data['tenant']->id)
            ->where('number', $data['card']['number'])
            ->first();

        if (!$card) {
            $card = TenantCard::create([
                'tenant_id'        => $data['tenant']->id,
                'main'             => true,
                'name'             => $data['card']['name'],
                'number'           => $data['card']['number'],
                'expiration_month' => substr($data['card']['expiration'], 0, 2),
                'expiration_year'  => '20' . substr($data['card']['expiration'], -2),
            ]);
        }

        return [
            'card'   => $card,
            'error'  => null,
            'status' => 200,
        ];
    }

    /**
     * Verifica se o payload possui todos os campos obrigatórios do cartão novo.
     * Retorna true apenas quando nome, número, vencimento e cvv estão presentes.
     */
    private function hasNewCardPayload(array $data): bool
    {
        return isset($data['card'])
            && isset($data['card']['name'])
            && isset($data['card']['number'])
            && isset($data['card']['expiration'])
            && isset($data['card']['cvv']);
    }

    /**
     * Verifica se o payload de cartão foi enviado parcialmente.
     * Usado para responder erro de parâmetros faltando.
     */
    private function hasIncompleteCardPayload(array $data): bool
    {
        return isset($data['card']) && !$this->hasNewCardPayload($data);
    }

    /**
     * Normaliza o número do cartão removendo espaços.
     * Retorna o número limpo no formato inteiro.
     */
    private function normalizeCardNumber(string $number): int
    {
        return (int) str_replace(' ', '', $number);
    }

    /**
     * Extrai o CVV enviado no payload.
     * Retorna null quando não informado.
     */
    private function extractCardCvv(array $data): ?string
    {
        return $data['card']['cvv'] ?? null;
    }

}
