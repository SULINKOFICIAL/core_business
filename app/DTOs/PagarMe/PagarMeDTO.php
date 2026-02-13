<?php

namespace App\DTOs\PagarMe;

final class PagarMeDTO
{
    public function __construct(
        public readonly string $type,
        public readonly ChargeDTO $charge,
        public readonly InvoiceDTO $invoice,
        public readonly TransactionDTO $transaction,
        public readonly CustomerDTO $customer,
    ) {}
}
