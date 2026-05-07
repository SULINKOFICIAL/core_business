<?php

namespace App\Services\Payments;

use App\DTOs\PagHiper\PagHiperDTO;
use App\DTOs\Payments\{
    OrderDataDTO,
    PaymentDataDTO,
    TransactionDataDTO
};
use App\Models\Tenant;
use App\Models\TenantPlan;

class PagHiperPayloadService
{
    public function __construct(private PaymentService $paymentService){}

    /**
     *
     * Monta os DTOs de pagamento PagHiper e delega persistência ao PaymentService.
     *
     */
    public function create(PagHiperDTO $pagHiperDTO, Tenant $tenant, TenantPlan $plan, array $rawRequest): void
    {

        $orderData = new OrderDataDTO(
            status:           $pagHiperDTO->status,
            provider_method:  'paghiper',
            provider_message: null,
            currency:         'BRL',
            total_amount:     $pagHiperDTO->value,
            paid_at:          $pagHiperDTO->paidAt->format('Y-m-d H:i:s'),
        );

        $transactionData = new TransactionDataDTO(
            provider_method:         'paghiper',
            provider_transaction_id: $pagHiperDTO->transactionId,
            gateway_code:            $pagHiperDTO->orderId,
            status:                  $pagHiperDTO->status,
            currency:                'BRL',
            recurrency:              false,
            amount:                  $pagHiperDTO->value,
            paid_at:                 $pagHiperDTO->paidAt->format('Y-m-d H:i:s'),
            response:                $rawRequest,
        );

        $paymentData = new PaymentDataDTO(
            provider:        'paghiper',
            tenant_id:       $tenant->id,
            subscription_id: null,
            plan_id:         $plan->id,
            order:           $orderData,
            transaction:     $transactionData,
        );

        $this->paymentService->create($paymentData);
    }

}
