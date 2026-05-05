<?php

namespace App\DTOs\Payments;

class OrderDataDTO
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $provider_method,
        public readonly ?string $provider_message,
        public readonly ?string $currency,
        public readonly float $total_amount,
        public readonly ?string $paid_at,
    ) {
    }
}

