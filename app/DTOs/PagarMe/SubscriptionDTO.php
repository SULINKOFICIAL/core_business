<?php

namespace App\DTOs\PagarMe;

final class SubscriptionDTO
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $interval = null,
        public readonly ?int $intervalCount = null,
        public readonly ?string $method = null,
        public readonly ?string $status = null,
        public readonly ?int $installments = null,
        public readonly ?string $currency = null,
        public readonly ?int $price = null,
        public readonly ?string $cardId = null,
    ) {}
}
