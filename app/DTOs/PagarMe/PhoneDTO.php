<?php

namespace App\DTOs\PagarMe;

final class PhoneDTO
{
    public function __construct(
        public readonly string $country,
        public readonly string $ddd,
        public readonly string $number,
    ) {}
}
