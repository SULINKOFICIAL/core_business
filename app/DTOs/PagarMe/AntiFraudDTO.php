<?php

namespace App\DTOs\PagarMe;

final class AntifraudDTO
{
    public function __construct(
        public readonly string $status,
        public readonly string $score,
        public readonly string $provider,
    ) {}
}
