<?php

namespace App\Http\Controllers;

use App\Models\ClientCard;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class ApisPaymentsController extends Controller
{
    public function cards(Request $request)
    {
        // Recebe dados
        $data = $request->all();

        // Obtém dados do cliente
        $client = $data['client'];

        // Obtém cartões do cliente
        $cards = $client->cards()->orderBy('created_at', 'DESC')->get();

        // Inicia Json
        $cardsJson = [];

        // Formata dados Json
        foreach ($cards as $card) {
            $cardData['id'] = $card->id;
            $cardData['main'] = $card->main;
            $cardData['name'] = $card->name;
            $cardData['number'] = '**** **** **** ' . substr($card->number, -4);
            $cardData['expiration'] = str_pad($card->expiration_month, 2, '0', STR_PAD_LEFT) . '/' . $card->expiration_year;

            // Obtém dados
            $cardsJson[] = $cardData;
        }

        return response()->json($cardsJson, 200);
    }

    /**
     * Função responsável por processar pagamentos dos sistemas miCores.
     * Utilizamos junto a ele a integração através da eRede.
     */
    public function orderPayment(Request $request, OrderService $service)
    {
        // Obtém os dados enviados no formulário
        $data = $request->all();

        // Verifica se veio o id do pedido
        if (isset($data['order_id'])) {
            // Obtem o pedido
            $order = Order::find($data['order_id']);

            // Se ele existir atualiza para pendente
            if ($order) {
                $order->update([
                    'status' => 'pending'
                ]);
            }
        } else {
            $order = Order::where('client_id', $data['client']->id)->orderBy('id', 'DESC')->first();
        }

        // Encontra o cartão do cliente para reutilizar
        if (isset($data['card_id'])) {
            // Encontra o cartão do cliente
            $card = ClientCard::where('client_id', $data['client']->id)->where('id', $data['card_id'])->first();

            // Se não encontrar o cliente
            if (!$card) {
                return response()->json(['message' => 'Cartão não encontrado para esse cliente'], 404);
            }
        } else if (isset($data['card']) && (!isset($data['card']['name']) || !isset($data['card']['number']) || !isset($data['card']['expiration']) || !isset($data['card']['cvv']))) {
            return response()->json(['message' => 'Parâmetros faltando'], 400);
        }

        // Verifica se veio todos os dados do cartão
        if (isset($data['card']) && (isset($data['card']['name']) && isset($data['card']['number']) && isset($data['card']['expiration']) && isset($data['card']['cvv']))) {
            // Limpa os dados do cartão
            $data['card']['number'] = (int) str_replace(' ', '', $data['card']['number']);

            // Busca o cartão do cliente
            $card = ClientCard::where('client_id', $data['client']->id)->where('number', $data['card']['number'])->first();

            if (!$card) {
                // Salvamos o cartão do cliente
                $card = ClientCard::create([
                    'client_id' => $data['client']->id,
                    'main' => true,
                    'name' => $data['card']['name'],
                    'number' => $data['card']['number'],
                    'expiration_month' => substr($data['card']['expiration'], 0, 2),
                    'expiration_year' => '20' . substr($data['card']['expiration'], -2),
                ]);
            }
        }

        // Retorna o cliente atualizado
        $response = $service->createOrderPayment(
            $order,
            $data['client'],
            $data['client_info'],
            $card,
            isset($data['card']['cvv']) ? $data['card']['cvv'] : null,
            $data['billing_cycle'],
            isset($data['card']['address']) ? $data['card']['address'] : null
        );

        return response()->json([
            'message' => $response
        ], 200);
    }
}
