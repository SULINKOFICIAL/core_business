<?php

namespace App\Services\Payments;

use App\DTOs\Payments\PixChargeDataDTO;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;

class PagHiperPixProviderService
{
    /**
     * Cria a cobrança PIX no provider e normaliza para DTO canônico.
     */
    public function createCharge(Order $order, Tenant $tenant, array $clientInfo): PixChargeDataDTO
    {
        $apiKey = env('PAG_HIPER_API_KEY');

        $email    = $clientInfo['email'] ?? null;
        $name     = $clientInfo['name'] ?? null;
        $document = $clientInfo['document'] ?? null;
        $phone    = $clientInfo['phone'] ?? null;

        $payload = [
            'apiKey'            => $apiKey,
            'order_id'          => 'plan-order-' . $order->id . '-' . now()->timestamp,
            'payer_email'       => $email,
            'payer_name'        => $name,
            'payer_cpf_cnpj'    => $document,
            'payer_phone'       => $this->resolvePhone($phone),
            'fixed_description' => true,
            'days_due_date'     => 2,
            'items'             => [
                [
                    'description' => 'Assinatura miCore - Pedido #' . $order->id,
                    'quantity'    => 1,
                    'item_id'     => $order->id,
                    'price_cents' => $this->toCents($order->total_amount),
                ],
            ],
            'notification_url'  => env('PAG_HIPER_WEBHOOK_URL'),
        ];

        $response = Http::acceptJson()
            ->timeout(20)
            ->post(env('PAG_HIPER_PIX_CREATE_URL', 'https://pix.paghiper.com/invoice/create/'), $payload);

        /**
         * O provider encapsula os dados úteis dentro de `pix_create_request`.
         * A camada de orquestração consome somente esse nó canônico.
         */
        $responseData = $response->json();
        $resultNode   = $responseData['pix_create_request'] ?? [];
        $resultValue  = $resultNode['result'] ?? null;

        if ($response->failed() || $resultValue !== 'success') {
            return new PixChargeDataDTO(
                provider:              'paghiper',
                providerMethod:        'pix',
                providerTransactionId: $resultNode['transaction_id'] ?? '',
                status:                'failed',
                providerMessage:       $resultNode['response_message'] ?? 'Falha ao gerar cobrança PIX.',
                qrCodeBase64:          null,
                copyPaste:             null,
                expiresAt:             null,
                rawResponse:           $responseData,
            );
        }

        $status          = $this->normalizeStatus($resultNode['status'] ?? null);
        $providerMessage = $resultNode['response_message'] ?? null;

        return new PixChargeDataDTO(
            provider:              'paghiper',
            providerMethod:        'pix',
            providerTransactionId: $resultNode['transaction_id'] ?? '',
            status:                $status,
            providerMessage:       $providerMessage ?? 'PIX gerado com sucesso.',
            qrCodeBase64:          $resultNode['pix_code']['qrcode_base64'] ?? null,
            copyPaste:             $resultNode['pix_code']['emv'] ?? null,
            expiresAt:             $this->resolveExpiresAt($resultNode['due_date'] ?? null),
            rawResponse:           $responseData,
        );
    }

    /**
     * Converte valor monetário decimal para centavos no formato aceito pelo provider.
     */
    private function toCents($amount): int
    {
        $value = is_numeric($amount) ? $amount : 0;
        return intval(round($value * 100));
    }

    /**
     * Monta o telefone em formato numérico contínuo para o payload da PagHiper.
     */
    private function resolvePhone($phone): string
    {
        $countryCode = $phone['country_code'] ?? '';
        $areaCode    = $phone['area_code'] ?? '';
        $number      = $phone['phone'] ?? '';

        return onlyNumbers($countryCode . $areaCode . $number);
    }

    /**
     * Normaliza a data de vencimento retornada pelo provider para datetime local.
     */
    private function resolveExpiresAt($providerDueDate): ?string
    {
        if (!$providerDueDate) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($providerDueDate)->toDateTimeString();
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * Traduz status da PagHiper para o status canônico interno do billing.
     */
    private function normalizeStatus($providerStatus): string
    {
        $statusText = $providerStatus ?? '';
        $normalized = strtolower($statusText);

        return match ($normalized) {
            'paid'                   => 'approved',
            'pending', 'created', 'waiting' => 'pending',
            'canceled', 'cancelled'  => 'canceled',
            'expired', 'overdue'     => 'expired',
            'failed', 'error'        => 'failed',
            default                  => $normalized,
        };
    }
}
