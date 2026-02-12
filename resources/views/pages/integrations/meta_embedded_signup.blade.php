<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Onboarding Meta</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #222;
        }
        .container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        h1 {
            margin: 0 0 8px;
            font-size: 24px;
        }
        p {
            margin: 0 0 18px;
            color: #555;
        }
        .meta {
            font-size: 12px;
            color: #777;
            margin-bottom: 18px;
            word-break: break-all;
        }
        .row {
            display: flex;
            gap: 10px;
        }
        button {
            border: 0;
            background: #1b84ff;
            color: #fff;
            border-radius: 8px;
            padding: 10px 14px;
            cursor: pointer;
            font-weight: 600;
        }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .status {
            margin-top: 14px;
            font-size: 14px;
        }
        .ok { color: #15803d; }
        .err { color: #b91c1c; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Conectar conta Meta</h1>
            <p>O fluxo de Embedded Signup roda neste domínio fixo da central.</p>
            <div class="meta">
                Tenant: {{ $host }}<br>
                Sessão: {{ $signupSession }}
            </div>
            <div class="row">
                <button id="btn-start" type="button">Iniciar integração</button>
            </div>
            <div id="status" class="status">Aguardando início...</div>
        </div>
    </div>
    <script>

        const signupSession     = @json($signupSession);
        const signedState       = @json($signedState);
        const host              = @json($host);
        const embeddedConfigId  = @json($embeddedConfigId);
        const metaAppId         = @json($metaAppId);
        const graphVersion      = @json($graphVersion);
        const callbackUrl       = '/api/integracoes/meta/onboarding/callback';

        const statusEl = document.getElementById('status');
        const startButton = document.getElementById('btn-start');
        const signupData = {
            wabaId: null,
            phoneNumberId: null,
        };

        /**
         * Atualiza a mensagem de status exibida na tela.
         */
        function setStatus(message, isError = false) {
            statusEl.textContent = message;
            statusEl.className = 'status ' + (isError ? 'err' : 'ok');
        }

        /**
         * 
         */
        window.fbAsyncInit = function () {
            FB.init({
                appId: metaAppId,
                autoLogAppEvents: true,
                xfbml: false,
                version: graphVersion
            });
        };

        /**
         * Inicia o fluxo de Embedded Signup da Meta via SDK.
         */
        function startSignup() {
            startButton.disabled = true;
            FB.login(
                function() {
                    setStatus('Aguardando retorno do Embedded Signup...');
                },
                {
                    config_id: embeddedConfigId,
                    response_type: 'code',
                    override_default_response_type: true,
                    state: signupSession,
                    extras: {
                        featureType: 'whatsapp_business_app_onboarding',
                        sessionInfoVersion: '3',
                        version: 'v3'
                    }
                }
            );
        }

        /**
         * Envia para a central o evento bruto recebido do postMessage.
         */
        async function sendRawEventToCentral(event) {
            // Mantém o conteúdo bruto para o backend validar e interpretar.
            const rawMessage = typeof event.data === 'string' ? event.data : JSON.stringify(event.data);

            const response = await fetch(callbackUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    signed_state: signedState,
                    origin: event.origin,
                    raw_message: rawMessage
                })
            });

            return response.json();
        }

        window.addEventListener('message', async (event) => {
            // Loga o payload recebido do postMessage para debug do Embedded Signup.
            console.log('Embedded Signup message:', event);

            const callbackResponse = await sendRawEventToCentral(event).catch(() => null);
            if (!callbackResponse) {
                setStatus('Falha ao enviar callback para a central.', true);
                return;
            }

            // Se o backend concluir o fluxo, segue para o callback final no miCore.
            if (callbackResponse.redirect_url) {
                window.location.href = callbackResponse.redirect_url;
            }
        });

        startButton.addEventListener('click', startSignup);
    </script>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
</body>
</html>
