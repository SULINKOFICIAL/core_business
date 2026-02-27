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

    public function getIdempotencyKey(): string
    {
        $parts = [$this->type];

        if ($this->charge) {
            $parts[] = $this->charge->id;
            $parts[] = $this->charge->status;
        }

        if ($this->invoice) {
            $parts[] = $this->invoice->id;
            $parts[] = $this->invoice->status;
        }

        if ($this->transaction) {
            $parts[] = $this->transaction->id;
            $parts[] = $this->transaction->status;

            if ($this->transaction->card) {
                $parts[] = $this->transaction->card->id;
            }
        }

        if ($this->subscription) {
            $parts[] = $this->subscription->id;
            $parts[] = $this->subscription->status;
        }

        return hash('sha256', implode('|', $parts));
    }
}
