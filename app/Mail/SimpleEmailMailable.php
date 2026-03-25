<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SimpleEmailMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Recebe assunto, dados do template e a view usada no corpo do e-mail.
     * Isso permite reaproveitar o mesmo mailable para envios simples do sistema.
     */
    public function __construct(
        public string $subjectLine,
        public array $data = [],
        public string $template = 'emails.simple',
    ) {
    }

    /**
     * Define o envelope com o assunto final da mensagem.
     * O assunto é isolado aqui para seguir o padrão atual do Laravel 11.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    /**
     * Define a view Blade e injeta os dados necessários no template.
     * O assunto também é enviado para uso opcional no HTML.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->template,
            // Mescla o assunto no payload para a view acessar tudo no mesmo contexto.
            with: array_merge($this->data, [
                'subjectLine' => $this->subjectLine,
            ]),
        );
    }

    /**
     * Mantém a assinatura padrão do mailable sem anexos neste fluxo.
     * O método fica pronto para expansão futura sem alterar contrato.
     */
    public function attachments(): array
    {
        return [];
    }
}
