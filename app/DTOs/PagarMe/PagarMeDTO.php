<?php

namespace App\DTOs\PagarMe;

final class PagarMeDTO
{
    public function __construct(
        public readonly string $type,
        public readonly ?ChargeDTO $charge = null,
        public readonly ?InvoiceDTO $invoice = null,
        public readonly ?CycleDTO $cycle = null,
        public readonly ?TransactionDTO $transaction = null,
        public readonly CustomerDTO $customer,
        public readonly ?SubscriptionDTO $subscription = null,
    ) {}
}
