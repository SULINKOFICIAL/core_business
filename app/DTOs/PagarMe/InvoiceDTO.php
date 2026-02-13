<?php

namespace App\DTOs\PagarMe;

final class InvoiceDTO
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $subscriptionId,
        public readonly string $status,
        public readonly int $amount,
        public readonly string $dueAt,
        public readonly string $createdAt,
        public readonly string $method,
    ) {}
}
