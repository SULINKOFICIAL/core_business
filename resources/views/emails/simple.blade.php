<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine ?? 'Mensagem' }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #eef2f5; font-family: Arial, Helvetica, sans-serif; color: #1f2937; line-height: 1.6;">
    {{-- Cria a moldura externa com cor institucional inspirada no layout da Meta. --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #eef2f5;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                {{-- Centraliza o email e aplica o bloco verde escuro do layout principal. --}}
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 760px; background-color: #0d1422; background-image: linear-gradient(181deg, #1c283e, #0d1422); border-radius: 18px; overflow: hidden;">
                    <tr>
                        <td style="padding: 34px 40px 18px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <img
                                            src="{{ asset('assets/media/images/logo_white.png') }}"
                                            alt="Logo Central"
                                            style="display: block; max-width: 170px; width: 100%; height: auto;"
                                        >
                                    </td>
                                    <td align="right" style="font-size: 14px; color: #c6d4ea;">
                                        comunica&ccedil;&atilde;o do sistema
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 24px 24px 24px;">
                            {{-- Mantem o conteudo principal em um card branco semelhante ao email de referencia. --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 18px;">
                                <tr>
                                    <td style="padding: 36px 36px 20px 36px;">
                                            <p style="margin: 0 0 18px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 1.4px; color: #315f9f; font-weight: 700;">
                                                Atualiza&ccedil;&atilde;o MiCore
                                            </p>

                                        <h1 style="margin: 0 0 24px 0; font-size: 28px; line-height: 1.2; color: #172026; font-weight: 700;">
                                            {{ $subjectLine ?? 'Mensagem do sistema' }}
                                        </h1>

                                        @if(!empty($recipient_name))
                                            <p style="margin: 0 0 24px 0; font-size: 16px; color: #2d3a40;">
                                                Ol&aacute;, {{ $recipient_name }}.
                                            </p>
                                        @else
                                            <p style="margin: 0 0 24px 0; font-size: 16px; color: #2d3a40;">
                                                Ol&aacute;.
                                            </p>
                                        @endif

                                        {{-- Renderiza textos longos com quebra de linha para comunicados maiores. --}}
                                        <div style="font-size: 16px; color: #253238;">
                                            {!! nl2br(e($message_body ?? 'Esta &eacute; uma mensagem enviada pelo sistema.')) !!}
                                        </div>

                                        {{-- Exibe o CTA em formato de botao quando o envio tiver link configurado. --}}
                                        @if(!empty($cta_url) && !empty($cta_label))
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 28px;">
                                                <tr>
                                                    <td style="border-radius: 999px; background-color: #1f5eff;">
                                                        <a href="{{ $cta_url }}" style="display: inline-block; padding: 14px 24px; font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none;">
                                                            {{ $cta_label }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 0 36px 36px 36px;">
                                        {{-- Fecha o email com um rodape discreto para contexto operacional. --}}
                                        <div style="padding-top: 22px; border-top: 1px solid #e5eaee; font-size: 12px; color: #6b7280;">
                                            Este e-mail foi enviado automaticamente pelo sistema MiCore.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
