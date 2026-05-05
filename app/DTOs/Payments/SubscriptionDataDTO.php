<?php

namespace App\DTOs\Payments;

class SubscriptionDataDTO
{
    public function __construct(
        public readonly string $provider,
        public readonly string $provider_subscription_id,
        public readonly ?string $provider_card_id,
        public readonly ?string $interval,
        public readonly ?string $payment_method,
        public readonly ?string $currency,
        public readonly ?int $installments,
        public readonly ?string $status,
    ) {
    }
}

