@extends('layouts.app')

@section('title', 'Painel')

@section('content')
<div class="card">
    <div class="card-body">
        <p class="text-center m-0">Sem contas cadastradas</p>
    </div>
</div>
<div class="d-flex justify-content-end mt-4">
    <a href="{{ route('clients.create') }}" class="btn btn-primary btn-active-danger">
        Adicionar Cliente
    </a>
</div>
@endsection
