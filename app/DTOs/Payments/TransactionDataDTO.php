<?php

namespace App\DTOs\Payments;

class TransactionDataDTO
{
    public function __construct(
        public readonly ?string $provider_method,
        public readonly string $provider_transaction_id,
        public readonly ?string $gateway_code,
        public readonly string $status,
        public readonly ?string $currency,
        public readonly ?string $recurrency,
        public readonly float $amount,
        public readonly ?string $paid_at,
        public readonly array $response,
    ) {}
}
