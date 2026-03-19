@extends('layouts.app')

@section('title', 'Erros da Aplicação')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Erros da Aplicação
</p>
<div class="card">
    <div class="card-header">
        <h2 class="card-title align-items-start flex-column">
            <span class="card-label text-uppercase text-center fw-bolder text-gray-700 m-0">Laravel Log</span>
            <span class="text-gray-500 fw-semibold fs-7">Esse é o arquivo gerado no <span class="text-primary">laravel.log</span></span>
        </h2>
        <div class="card-toolbar">
            <button id="copyLaravelLog" type="button" class="btn btn-sm btn-primary btn-active-danger text-uppercase fw-bolder me-2">
                Copiar
            </button>
            <a href="{{ route('errors.archive.log') }}" class="btn btn-sm btn-primary btn-active-danger text-uppercase fw-bolder js-archive-log-confirm">
                Arquivar Log
            </a>
        </div>
    </div>
    <div class="card-body py-1">
        <div class="bg-light bg-dark rounded p-4 h-700px scroll-y" id="laravelErrorsShow">
            <pre style="color: #0ff000; text-shadow: 5px 5px 5px rgb(0 0 0 / 50%);">{{ $logFileHtml }}</pre>
        </div>
    </div>
</div>
@endsection

@section('custom-footer')
@parent
<script>

    $(document).ready(function () {
        const copyButton = document.getElementById('copyLaravelLog');
        const logContent = document.querySelector('#laravelErrorsShow pre');

        /**
         * Faz a cópia via fallback quando a Clipboard API não estiver disponível.
         * Isso evita falha em navegadores que não suportam a API moderna.
         */
        function fallbackCopyText(text) {
            // Cria um campo temporário para executar a cópia legado.
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'absolute';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);

            // Seleciona o conteúdo e tenta copiar para a área de transferência.
            textarea.select();
            const copied = document.execCommand('copy');

            // Remove o elemento temporário após a tentativa.
            document.body.removeChild(textarea);

            return copied;
        }

        /**
         * Copia o conteúdo do log usando a API moderna com fallback compatível.
         * A função lança erro quando nenhum método disponível consegue copiar.
         */
        async function copyText(text) {
            // Prioriza a API nativa quando estiver disponível em contexto seguro.
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
                return;
            }

            // Usa o fallback legado quando a API moderna não existir.
            const copied = fallbackCopyText(text);
            if (!copied) {
                throw new Error('Falha ao copiar texto');
            }
        }

        /**
         * Atualiza o rótulo do botão para dar feedback rápido da ação executada.
         * O texto volta ao padrão após um curto intervalo para não poluir a interface.
         */
        function updateCopyButtonLabel(label) {
            if (!copyButton) {
                return;
            }

            // Exibe o status atual da ação no próprio botão.
            copyButton.textContent = label;

            setTimeout(function() {
                // Restaura o texto padrão depois do feedback visual.
                copyButton.textContent = 'Copiar';
            }, 2000);
        }

        if (copyButton && logContent) {
            copyButton.addEventListener('click', async function() {
                // Lê o texto atual do log e remove espaços desnecessários nas bordas.
                const textToCopy = logContent.innerText.trim();

                // Evita cópia vazia quando o arquivo estiver limpo.
                if (!textToCopy) {
                    toastr.warning('Não há conteúdo para copiar.');
                    return;
                }

                // Bloqueia cliques repetidos durante a operação assíncrona.
                copyButton.disabled = true;
                updateCopyButtonLabel('Copiando...');

                try {
                    // Copia o texto e informa sucesso para o usuário.
                    await copyText(textToCopy);
                    updateCopyButtonLabel('Copiado!');
                    toastr.success('Log copiado com sucesso.');
                } catch (error) {
                    // Informa falha quando a cópia não puder ser concluída.
                    updateCopyButtonLabel('Erro ao copiar');
                    toastr.error('Não foi possível copiar o log.');
                } finally {
                    // Garante o retorno do botão ao estado interativo.
                    copyButton.disabled = false;
                }
            });
        }

        // Posiciona a área de log no final para mostrar os erros mais recentes.
        $('#laravelErrorsShow').animate({ scrollTop: $('#laravelErrorsShow pre').height() }, 'slow');
    });

    $(document).on('click', '.js-archive-log-confirm', function (event) {
        // Exige confirmação antes de arquivar o arquivo atual de log.
        if (!window.confirm('Deseja mesmo arquivar o log da aplicação?')) {
            event.preventDefault();
        }
    });

</script>
@endsection
