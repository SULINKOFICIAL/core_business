<?php

namespace App\Http\Controllers;

use App\Services\ERedeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador responsável por interagir com a API da eRede para operações de 
 * transações e tokenização de cartões de crédito.
 * 
 * Documentação oficial: https://developer.userede.com.br/e-rede
 */
class ERedeController extends Controller
{
    /**
     * Serviço da eRede para gerenciamento de transações e tokenização.
     *
     * @var ERedeService
     */
    protected $eRedeService;

    /**
     * Construtor da classe.
     *
     * @param ERedeService $eRedeService Serviço para interagir com a API da eRede.
     */
    public function __construct(ERedeService $eRedeService)
    {
        $this->eRedeService = $eRedeService;
    }

    public function testar(){
        // Realiza tokenização, procedimento para cobrar recorrências automáticas.
        $responseTokenization = $this->eRedeService->tokenization(
            'ramon@sulink.com.br',
            '5162928376060732',
            '03',
            '2033',
            'Ramon L I Piekarski',
            '303'
        );

        dump($responseTokenization);

        // Consulta token
        $responseConsult = $this->eRedeService->verifySolicitation($responseTokenization['tokenizationId']);

        dd($responseConsult);
    }

    /**
     * Verifica a solicitação de tokenização de um cartão de crédito.
     *
     * @param string $tokenizationId Identificador da tokenização.
     * @return void
     */
    public function verifySolicitation($tokenizationId)
    {
        $responseRede = $this->eRedeService->verifySolicitation($tokenizationId);
        dd($responseRede);
    }

    /**
     * Obtém o criptograma do cartão de crédito tokenizado.
     *
     * @param string $tokenizationId Identificador da tokenização.
     * @return void
     */
    public function cryptogram($tokenizationId)
    {
        $responseRede = $this->eRedeService->cryptogram($tokenizationId);
        dd($responseRede);
    }

    /**
     * Obtém o criptograma do cartão de crédito tokenizado.
     *
     * @param string $tokenizationId Identificador da tokenização.
     * @return void
     */
    public function webhook(Request $request)
    {
        Log::info(json_encode($request->all()));
    }
}
