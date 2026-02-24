<?php

namespace App\DTOs\PagarMe;

final class PagarMeDTO
{
    public function __construct(
        public readonly string $type,
        public readonly ?ChargeDTO $charge = null,
        public readonly InvoiceDTO $invoice,
        public readonly TransactionDTO $transaction,
        public readonly CustomerDTO $customer,
    ) {}

    public function getIdempotencyKey(): string
    {
        $parts = [
            $this->type,
            $this->invoice->id,
            $this->transaction->id,
            $this->transaction->status,
            $this->transaction->card->id,
        ];

        // Se tiver charge, adiciona
        if ($this->charge) {
            $parts[] = $this->charge->id;
            $parts[] = $this->charge->status;
        }

        return hash('sha256', implode('|', $parts));
    }

}

