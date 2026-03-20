<?php

namespace App\Services;

class MetaWhatsAppTemplateService
{
    /**
     * Centraliza a chamada da API de template do WhatsApp via Meta.
     * Isso evita espalhar payload e endpoint do Graph API pelo sistema.
     */
    public function __construct(
        private readonly RequestService $requestService,
    ) {
    }

    /**
     * Dispara um template para um número usando a integração Meta/WhatsApp informada.
     * O método recebe componentes opcionais para permitir reuso com templates diferentes.
     */
    public function sendTemplate(
        string $phoneNumberId,
        string $accessToken,
        string $phoneNumber,
        string $templateName,
        string $languageCode = 'pt_BR',
        array $components = [],
    ): array {
        // Usa o phone number id informado para montar o endpoint correto.
        $url = "https://graph.facebook.com/v20.0/{$phoneNumberId}/messages";

        // Mantém o payload mínimo e compatível com a API oficial de templates da Meta.
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => onlyNumbers($phoneNumber),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode,
                ],
            ],
        ];

        // Só envia componentes quando o template realmente precisar de parâmetros.
        if (! empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->requestService->request(
            'POST',
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]
        );
    }

    /**
     * Dispara o template padrao de alerta de problema no sistema.
     * O payload segue a estrutura do modelo criado na Meta com header e corpo variáveis.
     */
    public function sendSystemProblemAlert(
        string $phoneNumberId,
        string $accessToken,
        string $phoneNumber,
        string $templateName,
        string $languageCode,
        string $systemName,
        string $description,
        string $eventDate,
    ): array {
        // Monta os componentes exatamente na ordem esperada pelo template da Meta.
        $components = [
            [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $systemName,
                    ],
                ],
            ],
            [
                'type' => 'body',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $systemName,
                    ],
                    [
                        'type' => 'text',
                        'text' => $description,
                    ],
                    [
                        'type' => 'text',
                        'text' => $eventDate,
                    ],
                ],
            ],
        ];

        return $this->sendTemplate(
            $phoneNumberId,
            $accessToken,
            $phoneNumber,
            $templateName,
            $languageCode,
            $components,
        );
    }

    /**
     * Busca os números vinculados à conta dona e devolve o primeiro phone number id disponível.
     * Isso permite testar o envio apenas com WABA ID e token manual informados na configuração.
     */
    public function getPhoneNumberIdFromOwnerAccount(string $ownerAccountId, string $accessToken): ?string
    {
        $response = $this->requestPhoneNumbers($ownerAccountId, $accessToken);

        if (($response['status'] ?? 500) >= 400) {
            return null;
        }

        return $response['data']['data'][0]['id'] ?? null;
    }

    /**
     * Centraliza a consulta dos números vinculados à conta dona da Meta.
     * Isso evita duplicação do endpoint quando o serviço precisar resolver o emissor.
     */
    private function requestPhoneNumbers(string $ownerAccountId, string $accessToken): array
    {
        return $this->requestService->request(
            'GET',
            "https://graph.facebook.com/v20.0/{$ownerAccountId}/phone_numbers",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]
        );
    }
}
