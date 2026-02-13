<?php

namespace App\DTOs\PagarMe;

final class CardDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $brand,
        public readonly string $lastDigits,
        public readonly string $firstDigits,
        public readonly int $expYear,
        public readonly int $expMonth,
        public readonly string $holder,
    ) {}
}
