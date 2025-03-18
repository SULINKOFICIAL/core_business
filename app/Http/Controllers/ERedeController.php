<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use GuzzleHttp\Client as Guzzle;


/**
 * Classe responsável por interagir com a API da eRede para realizar operações
 * relacionadas a transações e tokenização de cartões de crédito.
 * Documentação oficial: https://developer.userede.com.br/e-rede
 */
class ERedeController extends Controller
{
    /**
     * Tokeniza um cartão de crédito, armazenando-o com segurança na API da eRede.
     * 
     * @param string $email            E-mail do titular do cartão.
     * @param string $number           Número do cartão de crédito.
     * @param string $expirationMonth  Mês de expiração do cartão (MM).
     * @param string $expirationYear   Ano de expiração do cartão (AAAA).
     * @param string $cardName         Nome do titular impresso no cartão.
     * @param string $securityCode     Código de segurança (CVV).
     * @param int    $storageCard      Indica se o cartão deve ser armazenado (0 = não, 1 = sim).
     * @return mixed Resposta da API contendo os dados do cartão tokenizado.
     */
    public function tokenization($email, $number, $expirationMonth, $expirationYear, $cardName, $securityCode, $storageCard = 0){

        // Realiza solicitação
        return $this->guzzle(
            'post', 
            env('REDE_TOKEN_URL') . '/token-service/v1/tokenization', 
            [
                'email'           => $email, 
                'cardNumber'      => $number, 
                'expirationMonth' => $expirationMonth, 
                'expirationYear'  => $expirationYear, 
                'cardholderName'  => $cardName, 
                'securityCode'    => $securityCode, 
                'storageCard'     => $storageCard, 
            ]);
    }

    /**
     * Chama a função transaction() com parâmetros fictícios para realizar uma transação.
     * 
     * @return mixed Resposta da API contendo os detalhes da transação.
     */
    public function simulateTransaction() {

        // Obtém o cliente
        $client = Client::find(1);

        // Obtém o cartão do cliente
        $clientCard = $client->cards[0];

        // Definindo parâmetros fictícios
        // $tokenizationId  = null;      // ID fictício do cartão tokenizado
        $amount          = 1050;      // Valor fictício da transação
        $reference       = 'ref0003'; // Referência fictícia

        // Obtém cartão do cliente
        if($clientCard){
            $card = [
                'name'   => $clientCard->name, 
                'number' => $clientCard->number, 
                'month'  => $clientCard->expiration_month, 
                'year'   => $clientCard->expiration_year, 
            ];
        } else {
            // Simula um cartão
            $card = [
                'name'   => 'John Snow', 
                'number' => '5448280000000007', 
                'month'  => 1, 
                'year'   => 2028, 
                'ccv'    => '123', 
            ];
        }

        // Chama a função transaction passando os parâmetros fictícios
        $response = $this->transaction(
            $amount, 
            $reference, 
            $clientCard->tokenization_id,
            $card,
        );

        dd($response);

    }


    /**
     * Realiza a transação de pagamento com o cartão tokenizado.
     * 
     * @param string $tokenizationId ID do cartão tokenizado.
     * @param float  $amount        Valor da transação.
     * @param string $reference     Referência da transação.
     * @param string $cardholderName Nome do titular do cartão.
     * @param string $cardNumber    Número do cartão.
     * @param int    $expirationMonth Mês de expiração do cartão (MM).
     * @param int    $expirationYear  Ano de expiração do cartão (AAAA).
     * @param string $securityCode   Código de segurança do cartão (CVV).
     * @return mixed Resposta da API contendo os detalhes da transação.
     */
    public function transaction($amount, $reference, $tokenizationId = null, $card = null) {


        // Monta os dados básicos da transação
        $transactionData = [
            'capture'                => false,
            'kind'                   => 'credit',
            'reference'              => $reference,
            'amount'                 => $amount,
            'softDescriptor'         => 'PD01',
            'subscription'           => false,
            'origin'                 => 1,
            'distributorAffiliation' => 73373853,
            'brandTid'               => 'string',
            'transactionCredentials' => [
                'credentialId' => '01'
            ]
        ];

        // Formata os dados do cartão
        $transactionData['cardholderName']  = $card['name'];
        $transactionData['cardNumber']      = $card['number'];
        $transactionData['expirationMonth'] = $card['month'];
        $transactionData['expirationYear']  = $card['year'];

        /**
         * Se veio o TokenizationId significa que será
         * uma transação que será feita de forma automática,
         * ou seja, não será necessário CCV.
         */
        if($tokenizationId){

            // Gera o token criptografado para que a transação seja realizada.
            $encryptedToken = $this->cryptogram($tokenizationId);

            // Formata os demais dados necessários
            $transactionData['tokenCryptogram'] = $encryptedToken['cryptogramInfo']['tokenCryptogram'];
            $transactionData['tokenizationId']  = $tokenizationId;
            $transactionData['storageCard']     = 1;
            $transactionData['securityAuthentication'] = [
                'sai' => "01",
            ];
            $transactionData['transactionCredentials'] = [
                'credentialId' => "01",
            ];
        }

        // Caso não seja uma transação via token
        if (!$tokenizationId) {
            $transactionData['securityCode'] = $card['ccv'];
            $transactionData['storageCard']  = 0;
        }

        // Envia a solicitação para a eRede
        return $this->guzzle(
            'post', 
            env('REDE_URL') . '/v1/transactions', 
            $transactionData
        );
    }


    /**
     * Verifica dados sobre o token gerado.
     * 
     * @param string $tokenizationId ID do cartão tokenizado.
     * @return mixed Resposta da API contendo o cryptogram.
     */
    public function verifySolicitation($tokenizationId){
        return $this->guzzle('get', env('REDE_TOKEN_URL') . '/token-service/v1/tokenization/' . $tokenizationId);
    }


    /**
     * Obtém um cryptogram (criptograma de segurança) para um cartão tokenizado.
     * 
     * @param string $tokenizationId ID do cartão tokenizado.
     * @return mixed Resposta da API contendo o cryptogram.
     */
    public function cryptogram($tokenizationId){

        // Realiza solicitação
        return $this->guzzle(
            'post', 
            env('REDE_TOKEN_URL') . '/token-service/v1/cryptogram/' . $tokenizationId, 
            [
                'subscription' => true
            ]);

    }

    /**
     * Realiza uma solicitação Guzzle com autenticação Bearer
     *
     * @param string $method Método HTTP (get, post, etc)
     * @param string $url URL para a solicitação
     * @param object $client Objeto cliente contendo informações do cliente
     * @param array|null $data Dados opcionais para incluir na requisição
     * @return array Resposta da API
     */
    public function guzzle($method, $url, $data = null)
    {

        try {

            // Instancia o Guzzle
            $guzzle = new Guzzle();
    
            // Inicializa os parâmetros da requisição
            $options = [
                'auth' => [env('REDE_PV'), env('REDE_TOKEN')],
            ];
    
            // Se houver dados, adiciona ao corpo da requisição
            if ($data !== null) {
                $options['json'] = $data;
            }
    
            // Realiza a solicitação
            $response = $guzzle->$method("$url", $options);

            // Obtém e decodifica o corpo da resposta
            return json_decode($response->getBody()->getContents(), true);

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            // Captura a resposta
            $response = $e->getResponse();

            // Obtém o corpo da requisição
            return json_decode($response->getBody()->getContents(), true);
            
        }
    }
    

}
