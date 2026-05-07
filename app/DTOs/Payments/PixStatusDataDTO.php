<?php

namespace App\DTOs\Payments;

class PixStatusDataDTO
{
    public function __construct(
        public readonly string $status,
        public readonly string $providerMessage,
        public readonly array $rawResponse,
    ) {
    }
}
