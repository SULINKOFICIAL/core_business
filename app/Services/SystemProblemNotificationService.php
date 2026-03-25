<?php

namespace App\Services;

class SystemProblemNotificationService
{
    /**
     * Centraliza o disparo de alertas de problema para múltiplos canais.
     * O serviço recebe um incidente e envia por e-mail e WhatsApp.
     */
    public function __construct(
        private readonly EmailService $emailService,
        private readonly MetaWhatsAppTemplateService $metaWhatsAppTemplateService,
    ) {
    }

    /**
     * Dispara a notificação de incidente em todos os canais ativos.
     */
    public function notify(array $incident): array
    {
        $emailResult = $this->emailService->sendSystemProblemAlert($incident);
        $whatsAppResult = $this->metaWhatsAppTemplateService->sendSystemProblemAlertToConfiguredPhones($incident);

        return [
            'success' => ($emailResult['success'] ?? false) || ($whatsAppResult['success'] ?? false),
            'email' => $emailResult,
            'whatsapp' => $whatsAppResult,
        ];
    }

    /**
     * Dispara manualmente o alerta de problema para um telefone específico.
     * Mantém o fluxo da tela de configurações desacoplado dos detalhes da Meta.
     */
    public function sendWhatsAppTest(string $phoneNumber, string $systemName, string $description, string $eventDate): array
    {
        return $this->metaWhatsAppTemplateService->sendSystemProblemAlertTest(
            $phoneNumber,
            $systemName,
            $description,
            $eventDate,
        );
    }
}
