<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\ERedeService;

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
}
