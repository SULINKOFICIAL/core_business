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
    AcquirerDTO,
    CycleDTO,
    SubscriptionDTO
};
use Illuminate\Support\Facades\Cache;

class PagarMeResponseService
{
    /**
     * Normaliza os dados recebidos do Webhook da PagarMe
     */
    public function process(array $data)
    {
        // Pega o tipo do evento
        $type = $this->getTypeRequest($data);

        // Pega os dados do evento
        $pagarMeDTO = match ($type) {
            'charge.created',
            'invoice.created',
            'subscription.created',
            'charge.paid',
            'invoice.paid',
            'charge.payment_failed',
            'invoice.payment_failed',
            'charge.antifraud_approved',
            'subscription.updated' => new PagarMeDTO(
                type: $type,
                charge: $this->getCharge($type, $data),
                invoice: $this->getInvoice($type, $data),
                cycle: $this->getCycle($type, $data),
                transaction: $this->getTransaction($type, $data),
                customer: $this->getCustomer($type, $data),
                subscription: $this->getSubscription($type, $data),
            ),
            default => null,
        };

        // Se não houver dados, retorna false
        if (!$pagarMeDTO) {
            return false;
        }

        // Pega a chave de duplicidade
        $key = $pagarMeDTO->getIdempotencyKey();

        /**
         * Salva em cache por 30 segundos, se encontrar
         * o mesmo registro dentro desse período, retorna
         * false para identificar que é duplicata.
         */
        if (env('APP_ENV') == 'production' && !Cache::add("pagarme:$key", true, 300)) {
            return false;
        }

        return $pagarMeDTO;
    }

    public function getTypeRequest(array $data): string
    {
        return $data['type'];
    }

    public function getInvoice(string $type, array $data): ?InvoiceDTO
    {
        return match ($type) {
            'charge.created',
            'charge.paid',
            'charge.antifraud_approved',
            'charge.payment_failed' => new InvoiceDTO(
                id: $data['data']['invoice']['id'],
                status: $data['data']['invoice']['status'],
                amount: $data['data']['invoice']['amount'],
                dueAt: $data['data']['invoice']['due_at'],
                createdAt: $data['data']['invoice']['created_at'],
                method: $data['data']['invoice']['payment_method'],
            ),
            'invoice.paid',
            'invoice.created',
            'invoice.payment_failed' => new InvoiceDTO(
                id: $data['data']['id'],
                status: $data['data']['status'],
                amount: $data['data']['amount'],
                dueAt: $data['data']['due_at'],
                createdAt: $data['data']['created_at'],
                method: $data['data']['payment_method'],
            ),
            default => null,
        };
    }

    public function getCycle(string $type, array $data): ?CycleDTO
    {
        return match ($type) {
            'invoice.paid',
            'invoice.created',
            'invoice.payment_failed' => new CycleDTO(
                id: $data['data']['cycle']['id'],
                status: $data['data']['cycle']['status'],
                startDate: $data['data']['cycle']['start_at'],
                endDate: $data['data']['cycle']['end_at'],
                cycle: $data['data']['cycle']['cycle'],
                billingAt: $data['data']['cycle']['billing_at'],
                nextBillingAt: $data['data']['subscription']['next_billing_at'],
            ),
            default => null,
        };
    }

    public function getTransaction(string $type, array $data): ?TransactionDTO
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
                    status: $data['data']['last_transaction']['antifraud_response']['status'] ?? '',
                    score: $data['data']['last_transaction']['antifraud_response']['score'] ?? '',
                    provider: $data['data']['last_transaction']['antifraud_response']['provider_name'] ?? '',
                ),
                acquirer: new AcquirerDTO(
                    message: $data['data']['last_transaction']['acquirer_message'] ?? '',
                    nsu: $data['data']['last_transaction']['acquirer_nsu'] ?? '',
                    tid: $data['data']['last_transaction']['acquirer_tid'] ?? '',
                ),
                gatewayId: $data['data']['last_transaction']['gateway_id'] ?? '',
                operationType: $data['data']['last_transaction']['operation_type'] ?? '',
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
            'charge.created',
            'charge.payment_failed' => new TransactionDTO(
                id: $data['data']['last_transaction']['id'],
                status: $data['data']['last_transaction']['status'],
                type: $data['data']['last_transaction']['transaction_type'],
                success: $data['data']['last_transaction']['success'],
                amount: $data['data']['last_transaction']['amount'],
                installments: $data['data']['last_transaction']['installments'],
                acquirer: new AcquirerDTO(
                    message: $data['data']['last_transaction']['acquirer_message'] ?? '',
                    nsu: $data['data']['last_transaction']['acquirer_nsu'] ?? '',
                    tid: $data['data']['last_transaction']['acquirer_tid'] ?? '',
                ),
                gatewayId: $data['data']['last_transaction']['gateway_id'] ?? '',
                operationType: $data['data']['last_transaction']['operation_type'] ?? '',
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
            'invoice.created',
            'invoice.payment_failed' => new TransactionDTO(
                id: $data['data']['charge']['last_transaction']['id'],
                status: $data['data']['charge']['last_transaction']['status'],
                type: $data['data']['charge']['last_transaction']['transaction_type'],
                success: $data['data']['charge']['last_transaction']['success'],
                amount: $data['data']['charge']['last_transaction']['amount'],
                installments: $data['data']['charge']['last_transaction']['installments'],
                acquirer: new AcquirerDTO(
                    message: $data['data']['charge']['last_transaction']['acquirer_message'] ?? '',
                    nsu: $data['data']['charge']['last_transaction']['acquirer_nsu'] ?? '',
                    tid: $data['data']['charge']['last_transaction']['acquirer_tid'] ?? '',
                ),
                gatewayId: $data['data']['charge']['last_transaction']['gateway_id'] ?? '',
                operationType: $data['data']['charge']['last_transaction']['operation_type'] ?? '',
                card: new CardDTO(
                    id: $data['data']['charge']['last_transaction']['card']['id'],
                    brand: $data['data']['charge']['last_transaction']['card']['brand'],
                    lastDigits: $data['data']['charge']['last_transaction']['card']['last_four_digits'],
                    firstDigits: $data['data']['charge']['last_transaction']['card']['first_six_digits'],
                    expYear: $data['data']['charge']['last_transaction']['card']['exp_year'],
                    expMonth: $data['data']['charge']['last_transaction']['card']['exp_month'],
                    holder: $data['data']['charge']['last_transaction']['card']['holder_name'],
                ),
            ),
            default => null,
        };
    }

    public function getCustomer(string $type, array $data): ?CustomerDTO
    {
        return match ($type) {
            'charge.created',
            'charge.paid',
            'charge.antifraud_approved',
            'charge.payment_failed',
            'invoice.created',
            'invoice.paid',
            'invoice.payment_failed',
            'subscription.created',
            'subscription.updated' => new CustomerDTO(
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

    public function getSubscription(string $type, array $data): ?SubscriptionDTO
    {
        return match ($type) {
            'subscription.created',
            'subscription.updated',
            => new SubscriptionDTO(
                id: $data['data']['id'],
                interval: $data['data']['interval'],
                intervalCount: $data['data']['interval_count'],
                method: $data['data']['payment_method'],
                status: $data['data']['status'],
                installments: $data['data']['installments'],
                currency: $data['data']['currency'],
                cardId: $data['data']['card']['id'],
                price: $data['data']['items'][0]['pricing_scheme']['price'],
            ),
            'invoice.created',
            'invoice.paid',
            'invoice.payment_failed' => new SubscriptionDTO(
                id: $data['data']['subscription']['id'],
                interval: $data['data']['subscription']['interval'],
                intervalCount: $data['data']['subscription']['interval_count'],
                method: $data['data']['subscription']['payment_method'],
                status: $data['data']['subscription']['status'],
                installments: $data['data']['subscription']['installments'],
                currency: $data['data']['subscription']['currency'],
                price: $data['data']['items'][0]['pricing_scheme']['price'],
            ),
            'charge.created',
            'charge.payment_failed',
            'charge.paid',
            'charge.antifraud_approved' => new SubscriptionDTO(
                id: $data['data']['invoice']['subscriptionId'],
            ),
        };
    }

    public function getCharge(string $type, array $data): ?ChargeDTO
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
            'charge.created',
            'charge.payment_failed' => new ChargeDTO(
                id: $data['data']['id'],
                code: $data['data']['code'],
                recurrency: $data['data']['recurrence_cycle'],
                currency: $data['data']['currency'],
                status: $data['data']['status'],
            ),
            'invoice.created',
            'invoice.paid',
            'invoice.payment_failed' => new ChargeDTO(
                id: $data['data']['charge']['id'],
                code: $data['data']['charge']['code'],
                recurrency: $data['data']['charge']['recurrence_cycle'],
                currency: $data['data']['charge']['currency'],
                status: $data['data']['charge']['status'],
            ),
            default => null,
        };
    }
}
