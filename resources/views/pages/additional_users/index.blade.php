@extends('layouts.app')

@section('title', 'Usuário adicional')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-end mb-5">
            <a href="{{ route('additional.users.create') }}" class="btn btn-primary">Novo item</a>
        </div>

        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead>
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="text-start">Quantidade</th>
                    <th class="text-start">Preço</th>
                    <th class="text-start">Status</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->quantity }}</td>
                        <td>R$ {{ number_format((float) $item->price, 2, ',', '.') }}</td>
                        <td>
                            @if($item->status)
                                <span class="badge badge-light-success">Ativo</span>
                            @else
                                <span class="badge badge-light-danger">Inativo</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('additional.users.edit', $item->id) }}" class="btn btn-sm btn-icon btn-light-primary">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-6">Nenhum registro cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
