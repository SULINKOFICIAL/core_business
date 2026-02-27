<?php

namespace App\Jobs;

use App\Models\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\LogsApi;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\OrderTransaction;
use App\Models\SubscriptionCycle;
use App\Services\OrderService;
use App\Services\PagarMeResponseService;
use App\Services\PagarMeService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PagarMeDispatchRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestData;
    protected $logApiId;
    protected $logApi;

    // Número máximo de tentativas
    public $tries = 1;

    // Tempo de espera entre as tentativas (segundos)
    public $backoff = 10;

    // Tempo máximo (segundos) que o Job pode executar
    public function timeout()
    {
        return 60;
    }

    public function __construct(array $requestData, $logApiId = null)
    {
        $this->requestData = $requestData;
        $this->logApiId = $logApiId;

        // Busca o logApi
        $this->logApi = LogsApi::find($this->logApiId);
    }

    public function handle(PagarMeResponseService $pagarMeResponseService)
    {

        /**
         * DTO Formatado da PagarMe
         */
        $pagarMeDTO = $pagarMeResponseService->process($this->requestData);

        /**
         * Se for Webhook com lógica de duplicidade
         * Retorna
         */
        if (!$pagarMeDTO) {
            return true;
        }

        return match ($pagarMeDTO->type) {
            'subscription.created',
            'subscription.updated',
            => $this->subscriptionCreatedOrUpdated($pagarMeDTO),
            'invoice.created',
            'charge.created',
            'invoice.paid',
            'charge.antifraud_approved',
            'charge.paid',
            'invoice.payment_failed',
            'charge.payment_failed',
            => $this->paymentCreatedOrUpdated($pagarMeDTO),
            default => true
        };
    }

    /**
     * Função responsável por verificar
     * Se existe uma assinatura criada
     * Se não existir cria uma
     */
    public function subscriptionCreatedOrUpdated($data)
    {
        // Obtem a assinatura a partir do id
        $subscription = Subscription::where('pagarme_subscription_id', $data->subscription->id)->first();

        // Se existir uma assinatura
        if (!$subscription) {

            // Cria a assinatura
            $subscription = Subscription::create([
                'pagarme_subscription_id' => $data->subscription->id,
                'pagarme_card_id'         => $data->subscription->cardId,
                'interval'                => $data->subscription->interval,
                'payment_method'          => $data->subscription->method,
                'currency'                => $data->subscription->currency,
                'installments'            => $data->subscription->installments,
                'status'                  => $data->subscription->status,
            ]);
        } else {

            // Atualiza a assinatura
            $subscription->update([
                'pagarme_subscription_id' => $data->subscription->id,
                'pagarme_card_id'         => $data->subscription->cardId,
                'interval'                => $data->subscription->interval,
                'payment_method'          => $data->subscription->method,
                'currency'                => $data->subscription->currency,
                'installments'            => $data->subscription->installments,
                'status'                  => $data->subscription->status,
            ]);
        }

        return true;
    }

    public function paymentCreatedOrUpdated($data)
    {
        // Obtem a transação do cliente
        $transaction = OrderTransaction::where('pagarme_transaction_id', $data->charge->id)->first();

        // Verifica se existe uma transação
        if (!$transaction) {

            // Obtem o cliente
            $client = Client::where('pagarme_customer_id', $data->customer->id)->first();

            // Obtem a assinatura
            $subscription = Subscription::where('pagarme_subscription_id', $data->subscription->id)->first();

            // Cria um pedido
            $order = Order::create([
                'client_id'       => $client->id,
                'subscription_id' => $subscription->id,
                'status'          => $data->charge->status,
                'currency'        => $data->charge->currency,
                'pagarme_message' => isset($data->transaction->acquirer->message) ? $data->transaction->acquirer->message : null,
                'method'          => $data->invoice->method,
                'total_amount'    => isset($data->charge?->paidAmount) ? $data->charge->paidAmount / 100 : 0,
                'paid_at'         => isset($data->charge?->paidAt) ? $data->charge->paidAt : null,
            ]);

            // Cria a transação
            $transaction = OrderTransaction::create([
                'order_id'                => $order->id,
                'subscription_id'         => $subscription->id,
                'pagarme_transaction_id'  => $data->charge->id,
                'gateway_code'            => isset($data->transaction->gatewayId) ? $data->transaction->gatewayId : null,
                'status'                  => $data->charge->status,
                'currency'                => $data->charge->currency,
                'method'                  => $data->invoice->method,
                'recurrency'              => $data->charge->recurrency,
                'amount'                  => isset($data->charge?->paidAmount) ? $data->charge->paidAmount / 100 : 0,
                'paid_at'                 => isset($data->charge?->paidAt) ? $data->charge->paidAt : null,
                'response'                => $this->requestData,
            ]);

            // Verifica se existe um ciclo
            if (isset($data->cycle)) {
                // Cria o ciclo
                SubscriptionCycle::create([
                    'subscription_id'  => $subscription->id,
                    'pagarme_cycle_id' => $data->cycle->id,
                    'start_date'       => $data->cycle->startDate,
                    'end_date'         => $data->cycle->endDate,
                    'status'           => $data->cycle->status,
                    'cycle'            => $data->cycle->cycle,
                    'billing_at'       => $data->cycle->billingAt,
                    'next_billing_at'  => $data->cycle->nextBillingAt,
                ]);
            }
        } else {

            // Atualiza a transação
            $transaction->update([
                'status'                  => $data->charge->status,
                'currency'                => $data->charge->currency,
                'method'                  => $data->invoice->method,
                'recurrency'              => $data->charge->recurrency,
                'amount'                  => isset($data->charge?->paidAmount) ? $data->charge->paidAmount / 100 : $transaction->amount,
                'paid_at'                 => isset($data->charge?->paidAt) ? $data->charge->paidAt : $transaction->paid_at,
                'gateway_code'            => isset($data->transaction->gatewayId) ? $data->transaction->gatewayId : $transaction->gateway_code,
                'response'                => $this->requestData,
            ]);

            // Atualiza o pedido
            $transaction->order->update([
                'status'                  => $data->charge->status,
                'paid_at'                 => isset($data->charge?->paidAt) ? $data->charge->paidAt : $transaction->order->paid_at,
                'total_amount'            => isset($data->charge?->paidAmount) ? $data->charge->paidAmount / 100 : $transaction->order->total_amount,
                'pagarme_message'         => isset($data->transaction->acquirer->message) ? $data->transaction->acquirer->message : $transaction->order->pagarme_message,
                'method'                  => $data->invoice->method,
                'currency'                => $data->charge->currency,
            ]);

            // Verifica se veio o ciclo
            if (isset($data->cycle)) {
                // Verifica se existe um ciclo
                $cycle = SubscriptionCycle::where('pagarme_cycle_id', $data->cycle->id)->first();

                // Se existir um ciclo
                if ($cycle) {

                    // Atualiza o ciclo
                    $cycle->update([
                        'status'           => $data->cycle->status,
                        'billing_at'       => $data->cycle->billingAt,
                        'next_billing_at'  => $data->cycle->nextBillingAt,
                    ]);
                } else {

                    // Cria o ciclo
                    SubscriptionCycle::create([
                        'subscription_id'  => $transaction->subscription_id,
                        'pagarme_cycle_id' => $data->cycle->id,
                        'start_date'       => $data->cycle->startDate,
                        'end_date'         => $data->cycle->endDate,
                        'status'           => $data->cycle->status,
                        'cycle'            => $data->cycle->cycle,
                        'billing_at'       => $data->cycle->billingAt,
                        'next_billing_at'  => $data->cycle->nextBillingAt,
                    ]);
                }
            }
        }

        return true;
    }
}
