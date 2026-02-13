<?php

namespace App\DTOs\PagarMe;

final class CustomerDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $document,
        public readonly string $type,
        public readonly PhoneDTO $phone,
    ) {}
}
