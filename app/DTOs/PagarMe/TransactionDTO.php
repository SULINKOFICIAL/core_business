<?php

namespace App\DTOs\PagarMe;

final class TransactionDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly string $type,
        public readonly bool $success,
        public readonly int $amount,
        public readonly int $installments,
        public readonly AcquirerDTO $acquirer,
        public readonly string $gatewayId,
        public readonly string $operationType,
        public readonly CardDTO $card,
        public readonly ?AntifraudDTO $antifraud = null,
    ) {}
}
