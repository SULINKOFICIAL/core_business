<?php

namespace App\DTOs\PagarMe;

final class AcquirerDTO
{
    public function __construct(
        public readonly string $message,
        public readonly string $nsu,
        public readonly string $tid,
    ) {}
}
