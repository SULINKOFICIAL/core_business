<?php

namespace App\DTOs\Payments;

class PaymentDataDTO
{
    public function __construct(
        public readonly string $provider,
        public readonly int $tenant_id,
        public readonly int $subscription_id,
        public readonly ?int $plan_id,
        public readonly OrderDataDTO $order,
        public readonly TransactionDataDTO $transaction,
        public readonly ?CycleDataDTO $cycle = null,
    ) {}
}
