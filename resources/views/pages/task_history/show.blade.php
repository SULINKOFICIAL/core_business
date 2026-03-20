@extends('layouts.app')

@section('title', 'Detalhes do Histórico de Tarefas')

@section('custom-head')
    @parent
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js" integrity="sha512-7Z9J3l1+EYfeaPKcGXu3MS/7T+w19WtKQY/n+xzmw4hZhJ9tyYmcUS+4QqAlzhicE5LAfMQSF3iFTK9bQdTxXg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js" integrity="sha512-SkmBfuA2hqjzEVpmnMt/LINrjop3GKWqsuLSSB3e7iBmYK7JuWw4ldmmxwD9mdm2IRTTi0OxSAfEGvgEi0i2Kw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-6">
    <div>
        <h3 class="fw-bold mb-1">Execução #{{ $dispatch->id }}</h3>
        <div class="text-gray-600 fs-7">
            Job:
            <span class="badge badge-light-primary">
                @if (empty($dispatch->job_name))
                    -
                @elseif ($dispatch->job_name === 'manual_batch')
                    Lote manual de tarefas
                @else
                    {{ ucwords(str_replace('_', ' ', (string) $dispatch->job_name)) }}
                @endif
            </span>
            | Origem: {{ $dispatch->source === 'manual' ? 'Manual' : 'Agendado' }}
            | Data: {{ optional($dispatch->started_at ?? $dispatch->created_at)->format('d/m/Y H:i:s') ?? '-' }}
        </div>
    </div>

    <a href="{{ route('task.history.index') }}" class="btn btn-light">Voltar</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-row-bordered align-middle gy-4">
                <thead>
                    <tr class="fw-bold text-gray-700">
                        <th>Job</th>
                        <th>Cliente</th>
                        <th class="text-center">Status</th>
                        <th>Mensagem</th>
                        <th>Retorno</th>
                        <th>Data/Hora</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>
                                <span class="badge badge-light-primary">
                                    @if (empty($item->job_name))
                                        -
                                    @elseif ($item->job_name === 'manual_batch')
                                        Lote manual de tarefas
                                    @else
                                        {{ ucwords(str_replace('_', ' ', (string) $item->job_name)) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if ($item->client)
                                    <a href="{{ route('clients.show', $item->client->id) }}" class="text-gray-800 text-hover-primary fw-bold">
                                        {{ $item->client->name }}
                                    </a>
                                    <div class="text-gray-500 fs-8">#{{ $item->client->id }}</div>
                                @else
                                    <span class="text-gray-600">Cliente removido</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($item->success)
                                    <span class="badge badge-light-success">Sucesso</span>
                                @else
                                    <span class="badge badge-light-danger">Falha</span>
                                @endif
                                @if ($item->response_status_code)
                                    <div class="text-gray-600 fs-8 mt-1">HTTP {{ $item->response_status_code }}</div>
                                @endif
                            </td>
                            <td class="text-gray-700">{{ $item->response_message ?? '-' }}</td>
                            <td>
                                @if ($item->response_body)
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-light-primary open-json-response"
                                        data-response="{{ base64_encode($item->response_body) }}"
                                    >
                                        Visualizar retorno
                                    </button>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-gray-700">{{ optional($item->requested_at ?? $item->finished_at ?? $item->created_at)->format('d/m/Y H:i:s') ?? '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-600">Nenhum item registrado para esta execução.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection

@section('modals')
<div class="modal fade" tabindex="-1" id="modal_json_response">
    <div class="modal-dialog modal-dialog-centered mw-1000px">
        <div class="modal-content">
            <div class="modal-header py-3 bg-dark border-0">
                <h5 class="modal-title text-white">Visualizando retorno</h5>
                <div class="btn btn-icon bg-dark ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <span class="svg-icon svg-icon-2x fw-bolder">X</span>
                </div>
            </div>
            <div class="modal-body p-0"></div>
        </div>
    </div>
</div>
@endsection

@section('custom-footer')
@parent
<script>
    function decodeBase64Unicode(value) {
        try {
            const binary = atob(value);
            const percentEncoded = Array.prototype.map.call(binary, function (character) {
                return '%' + character.charCodeAt(0).toString(16).padStart(2, '0');
            }).join('');

            return decodeURIComponent(percentEncoded);
        } catch (error) {
            return atob(value);
        }
    }

    $(document).on('click', '.open-json-response', function () {
        const responseBase64 = $(this).data('response');
        const responseString = decodeBase64Unicode(responseBase64);

        try {
            const formattedJson = JSON.stringify(JSON.parse(responseString), null, 4);
            const highlightedJson = `<pre class="m-0 rounded-0 rounded-bottom-2"><code class="language-json">${formattedJson}</code></pre>`;

            $('#modal_json_response .modal-body').html(highlightedJson);
            $('#modal_json_response').modal('show');
            Prism.highlightAll();
        } catch (error) {
            $('#modal_json_response .modal-body').html('<p class="text-danger p-4 m-0">Retorno inválido para JSON.</p>');
            $('#modal_json_response').modal('show');
        }
    });
</script>
@endsection
