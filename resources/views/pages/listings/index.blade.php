@extends('layouts.app')

@section('title', 'Lista de Recursos')

@section('content')
    <p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
        Lista de Recursos
    </p>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
                <thead class="rounded" style="background: #1c283e">
                    <tr class="fw-bold fs-6 text-white px-7">
                        <th class="text-start" width="60%">Nome</th>
                        <th class="text-center px-0">Slug</th>
                        <th class="text-center px-0">Criado Em</th>
                        <th class="text-center px-0">Status</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listings as $listing)
                        <tr>
                            <td>{{ $listing->name }}</td>
                            <td class="text-center">{{ $listing->slug }}</td>
                            <td class="text-center">{{ $listing->created_at->format('d/m/Y')}}</td>
                            <td class="text-center">
                                @if ($listing->status == 0)
                                    <span class="badge badge-light-danger">Desabilitado</span>  
                                    @else
                                    <span class="badge badge-light-success">Habilitado</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('listings.edit', $listing->id) }}" class="btn btn-sm btn-icon ms-0 text-gray-600 w-45px" title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>    
<div class="d-flex mt-4">
    <a href="{{ route('listings.create') }}" class="btn btn-sm btn-primary btn-active-success">
        Criar Lista
    </a>
</div>
@endsection