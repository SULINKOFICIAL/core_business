<?php

namespace App\DTOs\Payments;

class CycleDataDTO
{
    public function __construct(
        public readonly string $provider_cycle_id,
        public readonly ?string $start_date,
        public readonly ?string $end_date,
        public readonly ?string $status,
        public readonly ?string $cycle,
        public readonly ?string $billing_at,
        public readonly ?string $next_billing_at,
    ) {
    }
}

