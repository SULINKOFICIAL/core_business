<?php

namespace App\Http\Controllers;

use App\Models\ClientCard;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class ApisPaymentsController extends Controller
{

    /**
     * Processa o pagamento do pedido em andamento do cliente.
     * Resolve pedido/cartão e delega a cobrança para o serviço de pedidos.
     */
    public function orderPayment(Request $request, OrderService $service)
    {
        // Obtém dados enviados pelo front.
        $data = $request->all();

        // Obtem o pacote do cliente
        $package = $service->getPackageInProgress($data['client']);

        // Busca o pedido em andamento
        $order = $service->getOrderInProgress($data['client'], $package);

        // Resolve cartão (existente ou novo) com validações de payload.
        $cardResult = $this->resolveCardFromRequest($data);

        if ($cardResult['error']) {
            // Retorna erro de validação de cartão para o front.
            return response()->json(['message' => $cardResult['error']], $cardResult['status']);
        }

        // Extrai cartão validado para envio ao serviço de pagamento.
        $card = $cardResult['card'];

        // Processa pagamento junto ao serviço de pedidos.
        $response = $service->createOrderPayment(
            $package,
            $order,
            $data['client'],
            $data['client_info'],
            $card,
            $this->extractCardCvv($data),
            $data['billing_cycle'],
            $this->extractCardAddress($data)
        );

        return response()->json([
            'message' => $response
        ], 200);
    }

    /**
     * Resolve o cartão que será utilizado no pagamento.
     * Aceita cartão existente por id ou cria/reutiliza cartão enviado no payload.
     */
    private function resolveCardFromRequest(array &$data): array
    {
        if (isset($data['card_id'])) {
            // Busca cartão existente do próprio cliente.
            $existingCard = ClientCard::where('client_id', $data['client']->id)
                ->where('id', $data['card_id'])
                ->first();

            if (!$existingCard) {
                // Impede uso de cartão inexistente para o cliente.
                return ['card' => null, 'error' => 'Cartão não encontrado para esse cliente', 'status' => 404];
            }

            return ['card' => $existingCard, 'error' => null, 'status' => 200];
        }

        // Rejeita payload parcial de cartão novo.
        if ($this->hasIncompleteCardPayload($data)) {
            return ['card' => null, 'error' => 'Parâmetros faltando', 'status' => 400];
        }

        // Exige cartão quando não for informado card_id.
        if (!$this->hasNewCardPayload($data)) {
            return ['card' => null, 'error' => 'Cartão não informado', 'status' => 400];
        }

        // Normaliza número para comparação/armazenamento.
        $data['card']['number'] = $this->normalizeCardNumber($data['card']['number']);

        // Reaproveita cartão já salvo para esse cliente.
        $card = ClientCard::where('client_id', $data['client']->id)
            ->where('number', $data['card']['number'])
            ->first();

        if (!$card) {
            // Cria novo cartão quando ainda não existe.
            $card = ClientCard::create([
                'client_id' => $data['client']->id,
                'main' => true,
                'name' => $data['card']['name'],
                'number' => $data['card']['number'],
                'expiration_month' => substr($data['card']['expiration'], 0, 2),
                'expiration_year' => '20' . substr($data['card']['expiration'], -2),
            ]);
        }

        return ['card' => $card, 'error' => null, 'status' => 200];
    }

    /**
     * Verifica se o payload possui todos os campos obrigatórios do cartão novo.
     * Retorna true apenas quando nome, número, vencimento e cvv estão presentes.
     */
    private function hasNewCardPayload(array $data): bool
    {
        return isset($data['card'])
            && isset($data['card']['name'])
            && isset($data['card']['number'])
            && isset($data['card']['expiration'])
            && isset($data['card']['cvv']);
    }

    /**
     * Verifica se o payload de cartão foi enviado parcialmente.
     * Usado para responder erro de parâmetros faltando.
     */
    private function hasIncompleteCardPayload(array $data): bool
    {
        return isset($data['card']) && !$this->hasNewCardPayload($data);
    }

    /**
     * Normaliza o número do cartão removendo espaços.
     * Retorna o número limpo no formato inteiro.
     */
    private function normalizeCardNumber(string $number): int
    {
        return (int) str_replace(' ', '', $number);
    }

    /**
     * Extrai o CVV enviado no payload.
     * Retorna null quando não informado.
     */
    private function extractCardCvv(array $data): ?string
    {
        return $data['card']['cvv'] ?? null;
    }

    /**
     * Extrai o endereço de cobrança do cartão.
     * Retorna null quando não houver endereço no payload.
     */
    private function extractCardAddress(array $data): ?array
    {
        return $data['card']['address'] ?? null;
    }
}
