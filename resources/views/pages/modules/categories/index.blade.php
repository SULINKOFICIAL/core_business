@extends('layouts.app')

@section('title', 'Categorias de Módulos')

@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded" style="background: #1c283e">
                <tr class="fw-bold fs-6 text-white px-7">
                    <th class="text-start" style="width: 40%">Nome</th>
                    <th class="text-start">Status</th>
                    <th class="text-start">Criado Em</th>
                    <th class="text-start" style="width: 10%">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td class="text-start">
                            {{ $category->name }}
                        </td>
                        <td class="text-start">
                            @if ($category->status == 0)
                                <span class="badge badge-light-danger">Desabilitado</span>
                                @else
                                <span class="badge badge-light-success">Habilitado</span>
                            @endif
                        </td>
                        <td class="text-start text-gray-600">
                            {{ $category->created_at->format('d/m/Y') }}
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-4 align-items-center justify-content-center">
                                <a href="{{ route('modules.categories.edit', $category->id) }}" class="btn btn-sm btn-primary btn-active-success fw-bolder text-uppercase py-2">
                                    Editar
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
    <a href="{{ route('modules.categories.create') }}" class="btn btn-sm btn-primary btn-active-success">
        Adicionar Categoria
    </a>
</div>
@endsection
