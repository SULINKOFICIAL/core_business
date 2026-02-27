<?php

namespace App\DTOs\PagarMe;

final class CycleDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly int $cycle,
        public readonly string $billingAt,
        public readonly string $nextBillingAt,
    ) {}
}
