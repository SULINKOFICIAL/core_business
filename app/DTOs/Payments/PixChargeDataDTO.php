<?php

namespace App\DTOs\Payments;

class PixChargeDataDTO
{
    public function __construct(
        public readonly string $provider,
        public readonly string $providerMethod,
        public readonly string $providerTransactionId,
        public readonly string $status,
        public readonly string $providerMessage,
        public readonly ?string $qrCodeBase64,
        public readonly ?string $copyPaste,
        public readonly ?string $expiresAt,
        public readonly array $rawResponse,
    ) {
    }
}
