@extends('layouts.app')

@section('title', 'Notícias')

@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded" style="background: #1c283e">
                <tr class="fw-bold fs-6 text-white px-7">
                    <th class="text-start" style="width: 20%">Título</th>
                    <th class="text-start">Categoria</th>
                    <th class="text-start">Prioridade</th>
                    <th class="text-start">Exibir durante</th>
                    <th class="text-start">CTA</th>
                    <th class="text-start">Status</th>
                    <th class="text-start">Criado Em</th>
                    <th class="text-start" style="width: 10%">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($news as $new)
                    <tr>
                        <td class="text-start">
                            <a href="{{ route('news.show', $new->id) }}" class="text-gray-700 text-hover-primary fw-bold">
                                {{ $new->title }}
                            </a>
                        </td>
                        <td class="text-start">
                            <a href="{{ route('news.show', $new->id) }}" class="text-gray-700 text-hover-primary fw-bold">
                                {{ $new->category }}
                            </a>
                        </td>
                        <td>
                            @if ($new->priority == 'high')
                                <span class="badge badge-light-success">Alta</span>
                            @elseif ($new->priority == 'medium')
                                <span class="badge badge-light-warning">Média</span>
                            @else
                                <span class="badge badge-light-danger">Baixa</span>
                            @endif
                        </td>
                        <td class="text-start">
                            <p class="text-gray-700 fw-bold mb-0">
                                {{ $new->start_date->format('d/m/Y') }} até {{ $new->end_date->format('d/m/Y') }}
                            </p>
                        </td>
                        <td class="text-start">
                            <a href="{{ $new->cta_url }}" class="badge badge-success bg-hover-primary" target="_blank">
                                {{ $new->cta_text }}
                            </a>
                        </td>
                        <td class="text-start">
                            @if ($new->status == 0)
                                <span class="badge badge-light-danger">Desabilitado</span>  
                                @else
                                <span class="badge badge-light-success">Habilitado</span>
                            @endif
                        </td>
                        <td class="text-start text-gray-600">
                            {{ $new->created_at->format('d/m/Y') }}
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-4 align-items-center justify-content-center">
                                <a href="{{ route('news.edit', $new->id) }}" class="btn btn-sm btn-primary btn-active-success fw-bolder text-uppercase py-2">
                                    Editar
                                </a>
                                <a href="{{ route('news.show', $new->id) }}" class="btn btn-sm btn-primary btn-active-success fw-bolder text-uppercase py-2">
                                    Visualizar
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-between mt-4">
    <a href="{{ route('news.create') }}" class="btn btn-sm btn-primary btn-active-success">
        Adicionar Notícia
    </a>
</div>
@endsection
