<?php

namespace App\Services;

use App\DTOs\PagarMe\{
    PagarMeDTO,
    ChargeDTO,
    InvoiceDTO,
    TransactionDTO,
    CustomerDTO,
    PhoneDTO,
    CardDTO,
    AntifraudDTO,
    AcquirerDTO
};

class PagarMeResponseService
{
    /**
     * Normaliza os dados recebidos do Webhook da PagarMe
     */
    public function process(array $data): ?PagarMeDTO
    {
        $type = $this->getTypeRequest($data);

        return match ($type) {
            'charge.paid',
            'charge.antifraud_approved' => new PagarMeDTO(
                type: $type,
                charge: $this->getCharge($type, $data),
                invoice: $this->getInvoice($type, $data),
                transaction: $this->getTransaction($type, $data),
                customer: $this->getCustomer($type, $data),
            ),
            default => null,
        };
    }

    public function getTypeRequest(array $data): string
    {
        return $data['type'];
    }

    public function getInvoice(string $type, array $data): InvoiceDTO
    {
        return match ($type) {
            'charge.paid',
            'charge.antifraud_approved' => new InvoiceDTO(
                id: $data['data']['invoice']['id'],
                subscriptionId: $data['data']['invoice']['subscriptionId'] ?? null,
                status: $data['data']['invoice']['status'],
                amount: $data['data']['invoice']['amount'],
                dueAt: $data['data']['invoice']['due_at'],
                createdAt: $data['data']['invoice']['created_at'],
                method: $data['data']['invoice']['payment_method'],
            ),
        };
    }

    public function getTransaction(string $type, array $data): TransactionDTO
    {
        return match ($type) {
            'charge.paid',
            'charge.antifraud_approved' => new TransactionDTO(
                id: $data['data']['last_transaction']['id'],
                status: $data['data']['last_transaction']['status'],
                type: $data['data']['last_transaction']['transaction_type'],
                success: $data['data']['last_transaction']['success'],
                amount: $data['data']['last_transaction']['amount'],
                installments: $data['data']['last_transaction']['installments'],
                antifraud: new AntifraudDTO(
                    status: $data['data']['last_transaction']['antifraud_response']['status'],
                    score: $data['data']['last_transaction']['antifraud_response']['score'],
                    provider: $data['data']['last_transaction']['antifraud_response']['provider_name'],
                ),
                acquirer: new AcquirerDTO(
                    message: $data['data']['last_transaction']['acquirer_message'],
                    nsu: $data['data']['last_transaction']['acquirer_nsu'],
                    tid: $data['data']['last_transaction']['acquirer_tid'],
                ),
                gatewayId: $data['data']['last_transaction']['gateway_id'],
                operationType: $data['data']['last_transaction']['operation_type'],
                card: new CardDTO(
                    id: $data['data']['last_transaction']['card']['id'],
                    brand: $data['data']['last_transaction']['card']['brand'],
                    lastDigits: $data['data']['last_transaction']['card']['last_four_digits'],
                    firstDigits: $data['data']['last_transaction']['card']['first_six_digits'],
                    expYear: $data['data']['last_transaction']['card']['exp_year'],
                    expMonth: $data['data']['last_transaction']['card']['exp_month'],
                    holder: $data['data']['last_transaction']['card']['holder_name'],
                ),
            ),
        };
    }

    public function getCustomer(string $type, array $data): CustomerDTO
    {
        return match ($type) {
            'charge.paid',
            'charge.antifraud_approved' => new CustomerDTO(
                id: $data['data']['customer']['id'],
                name: $data['data']['customer']['name'],
                email: $data['data']['customer']['email'],
                document: $data['data']['customer']['document'],
                type: $data['data']['customer']['type'],
                phone: new PhoneDTO(
                    country: $data['data']['customer']['phones']['mobile_phone']['country_code'],
                    ddd: $data['data']['customer']['phones']['mobile_phone']['area_code'],
                    number: $data['data']['customer']['phones']['mobile_phone']['number'],
                ),
            ),
        };
    }

    public function getCharge(string $type, array $data): ChargeDTO
    {
        return match ($type) {
            'charge.paid',
            'charge.antifraud_approved' => new ChargeDTO(
                id: $data['data']['id'],
                code: $data['data']['code'],
                paidAmount: $data['data']['paid_amount'],
                paidAt: $data['data']['paid_at'],
                recurrency: $data['data']['recurrence_cycle'],
                currency: $data['data']['currency'],
                status: $data['data']['status'],
            ),
        };
    }

}