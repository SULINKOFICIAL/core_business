<?php

namespace App\Services;

use App\Models\Client;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Facades\Log;

/**
 * Classe responsável por interagir com a API da eRede para realizar operações
 * relacionadas a transações e tokenização de cartões de crédito.
 * Documentação oficial: https://developer.userede.com.br/e-rede
 */
class ERedeService
{
    protected $guzzle;

    public function __construct()
    {
        $this->guzzle = new Guzzle();
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
    public function transaction($amount, $reference, $card, $ccv = null) {

        // Monta os dados básicos da transação
        $transactionData = [
            'cardholderName'         => $card->name,
            'cardNumber'             => $card->number,
            'expirationMonth'        => $card->expiration_month,
            'expirationYear'         => $card->expiration_year,
            'capture'                => true,
            'kind'                   => 'credit',
            'reference'              => $reference,
            'amount'                 => $amount,
            'softDescriptor'         => 'MICORE01',
            'subscription'           => true,
            'origin'                 => 1,
            'distributorAffiliation' => env('REDE_PV'),
            'transactionCredentials' => [
                'credentialId' => '01'
            ]
        ];

        /**
         * Se veio o brandTid significa que será uma transação que
         * será feita de forma automática ou seja, não será necessário CCV.
         */
        if ($card->brand_tid) {
            $transactionData['storageCard'] = 2;
            $transactionData['brandTid'] = $card->brand_tid;
        } else {
            $transactionData['storageCard'] = 1;
            $transactionData['securityCode'] = $ccv;
        }

        // Envia a solicitação para a eRede
        return $this->guzzleRequest(
            'post', 
            env('REDE_URL') . '/v1/transactions', 
            $transactionData
        );
    }


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
    public function tokenization($email, $number, $expirationMonth, $expirationYear, $cardName, $securityCode, $storageCard = 0, $brandTid){

        /** 
         * Regras do Storage Card
         * 0 - Não armazenar o cartão
         * 1 - Cartão sendo armazenado pela primeira vez. (Requer SecurityCode)
         * 2 - Cartão já armazenado. (Não requer o SecurityCode)
         */
        // dd($email, (int) $number, $expirationMonth, (int) $expirationYear, $cardName, (int) $securityCode, $storageCard);
        
        // Realiza solicitação
        return $this->guzzleRequest(
            'post', 
            env('REDE_TOKEN_URL') . '/v1/tokenization', 
            [
                'email'           => $email, 
                'cardNumber'      => (int) $number, 
                'expirationMonth' => sprintf('%02d', $expirationMonth),
                'expirationYear'  => (int) $expirationYear, 
                'cardholderName'  => $cardName, 
                'securityCode'    => (int) $securityCode, 
                'storageCard'     => $storageCard, 
            ]);
    }

    /**
     * Verifica dados sobre o token gerado.
     * 
     * @param string $tokenizationId ID do cartão tokenizado.
     * @return mixed Resposta da API contendo o cryptogram.
     */
    public function verifySolicitation($tokenizationId){
        return $this->guzzleRequest('get', env('REDE_TOKEN_URL') . '/v1/tokenization/' . $tokenizationId);
    }

    /**
     * Obtém um cryptogram (criptograma de segurança) para um cartão tokenizado.
     * 
     * @param string $tokenizationId ID do cartão tokenizado.
     * @return mixed Resposta da API contendo o cryptogram.
     */
    public function cryptogram($tokenizationId){

        // Realiza solicitação
        return $this->guzzleRequest(
            'post', 
            env('REDE_TOKEN_URL') . '/v1/cryptogram/' . $tokenizationId, 
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
    public function guzzleRequest($method, $url, $data = null)
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
