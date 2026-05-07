<?php

namespace App\Services\Payments;

use App\DTOs\PagarMe\PagarMeDTO;
use App\DTOs\Payments\{
    CycleDataDTO,
    OrderDataDTO,
    PaymentDataDTO,
    TransactionDataDTO
};
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantPlan;

class PagarMePayloadService
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Normaliza os dados brutos da PagarMe, monta os DTOs e delega a persistência ao PaymentService.
     */
    public function create(PagarMeDTO $pagarMeDTO, Tenant $tenant, Subscription $subscription, ?TenantPlan $plan, array $rawRequest): void
    {
        // PagarMe trafega valores em centavos; converte para reais
        $amount = $pagarMeDTO->charge?->paidAmount ? $pagarMeDTO->charge->paidAmount / 100 : 0;
        $paidAt = $pagarMeDTO->charge?->paidAt ?? null;

        // Monta o DTO do pedido
        $orderData = new OrderDataDTO(
            status:           $pagarMeDTO->charge->status,
            provider_method:  $pagarMeDTO->invoice->method,
            provider_message: $pagarMeDTO->transaction?->acquirer->message ?? null,
            currency:         $pagarMeDTO->charge->currency,
            total_amount:     $amount,
            paid_at:          $paidAt,
        );

        // Monta o DTO da transação
        $transactionData = new TransactionDataDTO(
            provider_method:         $pagarMeDTO->invoice->method,
            provider_transaction_id: $pagarMeDTO->charge->id,
            gateway_code:            $pagarMeDTO->transaction?->gatewayId ?? null,
            status:                  $pagarMeDTO->charge->status,
            currency:                $pagarMeDTO->charge->currency,
            recurrency:              $pagarMeDTO->charge->recurrency,
            amount:                  $amount,
            paid_at:                 $paidAt,
            response:                $rawRequest,
        );

        // Monta o DTO do ciclo quando presente (assinaturas recorrentes)
        $cycleData = null;
        if ($pagarMeDTO->cycle) {
            $cycleData = new CycleDataDTO(
                provider_cycle_id: $pagarMeDTO->cycle->id,
                start_date:        $pagarMeDTO->cycle->startDate,
                end_date:          $pagarMeDTO->cycle->endDate,
                status:            $pagarMeDTO->cycle->status,
                cycle:             $pagarMeDTO->cycle->cycle,
                billing_at:        $pagarMeDTO->cycle->billingAt,
                next_billing_at:   $pagarMeDTO->cycle->nextBillingAt,
            );
        }

        // Agrega os DTOs e delega a criação ao serviço universal
        $paymentData = new PaymentDataDTO(
            provider:        'pagarme',
            tenant_id:       $tenant->id,
            subscription_id: $subscription->id,
            plan_id:         $plan?->id,
            order:           $orderData,
            transaction:     $transactionData,
            cycle:           $cycleData,
        );

        $this->paymentService->create($paymentData);
    }
}
