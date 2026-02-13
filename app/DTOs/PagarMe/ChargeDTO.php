<?php

namespace App\DTOs\PagarMe;

final class ChargeDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly int $paidAmount,
        public readonly string $paidAt,
        public readonly string $recurrency,
        public readonly string $currency,
        public readonly string $status,
    ) {}
}
