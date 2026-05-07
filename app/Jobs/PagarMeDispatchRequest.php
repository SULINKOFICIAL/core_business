<?php

namespace App\Jobs;

use App\DTOs\PagarMe\{
    PagarMeDTO,
    SubscriptionDTO
};

use App\Services\{
    PagarMeResponseService,
    TenantService
};

use App\Services\Payments\{
    ActivePlanService,
    PagarMePayloadService,
    SubscriptionService
};

use App\Models\Subscription;

class PagarMeDispatchRequest extends InicializerJob
{
    /**
     * Variáveis Globais para a PagarMe
     */
    protected array $data;
    protected PagarMeDTO $pagarMeDTO;

    /**
     * Variaveis de Serviço
     */
    protected PagarMeResponseService $pagarMeResponseService;
    protected TenantService          $tenantService;
    protected SubscriptionService    $subscriptionService;
    protected ActivePlanService      $activePlanService;
    protected PagarMePayloadService  $pagarMePayloadService;

    public function __construct(array $data)
    {
        $this->data = $data;

        $this->pagarMeResponseService = app(PagarMeResponseService::class);
        $this->tenantService          = app(TenantService::class);
        $this->subscriptionService    = app(SubscriptionService::class);
        $this->activePlanService      = app(ActivePlanService::class);
        $this->pagarMePayloadService  = app(PagarMePayloadService::class);

        $this->pagarMeDTO = $this->pagarMeResponseService->process($data);
    }

    protected function process(): mixed
    {
        return match ($this->pagarMeDTO->type) {
            'subscription.created',
            'subscription.updated'        => $this->subscription($this->pagarMeDTO->subscription),
            'invoice.created',
            'charge.created',
            'invoice.paid',
            'charge.antifraud_approved',
            'charge.paid',
            'invoice.payment_failed',
            'charge.payment_failed'       => $this->payment($this->pagarMeDTO),
            default                       => null,
        };
    }

    /**
     * Processa eventos de criação e atualização de assinatura.
     */
    private function subscription(SubscriptionDTO $subscriptionDTO): Subscription
    {
        return $this->subscriptionService->saveSubscription($subscriptionDTO, 'pagarme');
    }

    /**
     * Processa eventos de pagamento resolvendo o contexto completo antes de persistir.
     */
    private function payment(PagarMeDTO $pagarMeDTO): void
    {
        // Resolve o tenant a partir do customer externo recebido no evento
        $tenant = $this->tenantService->findTenant($pagarMeDTO->customer->id, 'pagarme');

        // Verifica se já existe um plano ativo cadastrado
        $plan = $this->activePlanService->findActivePlan($tenant->id);

        // Resolve a assinatura local, criando caso ainda não exista
        $subscription = $this->subscriptionService->findSubscription($pagarMeDTO->subscription->id, 'pagarme', $pagarMeDTO->subscription);

        // Monta e persiste o pagamento
        $this->pagarMePayloadService->create($pagarMeDTO, $tenant, $subscription, $plan, $this->data);
    }
}