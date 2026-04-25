@extends('layouts.app')

@section('title', 'Importação em Massa de Módulos')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Importação em massa de módulos
</p>

<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">Edição em massa com IA</h3>
    </div>
    <div class="card-body">
        <p class="text-gray-700 mb-3">
            Copie o JSON base, envie para a IA preencher novas descrições/benefícios e importe o JSON final.
        </p>
        <div class="row">
            <div class="col-12 mb-4">
                <label class="form-label fw-bold text-gray-700">JSON base dos módulos (com ID)</label>
                <textarea class="form-control form-control-solid font-monospace" rows="12" id="modules-bulk-template-json" readonly></textarea>
                <div class="d-flex gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-light-primary" id="copy-modules-bulk-template">Copiar JSON base</button>
                    <a href="{{ route('modules.bulk.template') }}" target="_blank" class="btn btn-sm btn-light">Abrir JSON em nova aba</a>
                </div>
            </div>
            <div class="col-12">
                <form method="POST" action="{{ route('modules.bulk.import') }}">
                    @csrf
                    <label class="form-label fw-bold text-gray-700">JSON preenchido pela IA para importar</label>
                    <textarea
                        class="form-control form-control-solid font-monospace"
                        name="bulk_modules_json"
                        rows="12"
                        placeholder='Cole aqui o JSON no formato {"modules":[...]}'
                        required
                    >{{ old('bulk_modules_json') }}</textarea>
                    <div class="form-text">
                        A importação atualiza descrição e benefícios usando o campo <code>id</code>. Os benefícios existentes de cada módulo importado serão removidos antes da recriação.
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-sm btn-primary btn-active-success">
                            Importar JSON em massa
                        </button>
                        <a href="{{ route('modules.index') }}" class="btn btn-sm btn-light">
                            Voltar para módulos
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-footer')
    @parent
    <script>
        $(function () {
            const templateData = @json($modulesBulkTemplate, JSON_UNESCAPED_UNICODE);
            const templateText = JSON.stringify(templateData, null, 2);
            const templateTextarea = $('#modules-bulk-template-json');

            templateTextarea.val(templateText);

            $('#copy-modules-bulk-template').on('click', function () {
                navigator.clipboard.writeText(templateText).then(function () {
                    toastr.success('JSON base copiado com sucesso.');
                }).catch(function () {
                    toastr.error('Não foi possível copiar o JSON.');
                });
            });
        });
    </script>
@endsection
