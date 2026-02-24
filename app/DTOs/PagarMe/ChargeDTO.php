<?php

namespace App\DTOs\PagarMe;

final class ChargeDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $recurrency,
        public readonly string $currency,
        public readonly string $status,
        public readonly int|null $paidAmount = null,
        public readonly string|null $paidAt = null,
    ) {}
}
