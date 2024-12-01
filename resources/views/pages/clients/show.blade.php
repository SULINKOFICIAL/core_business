@extends('layouts.app')

@section('title', $contents->name)

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    {{ $contents->name }}
</p>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-center">
            <a href="{{ route('clients.index') }}" class="btn btn-lg btn-light mx-4">
                Voltar
            </a>
            <a href="{{ route('commands.pull') }}" class="btn btn-lg btn-primary mx-4">
                Efetuar Git Pull
            </a>
            <a href="{{ route('commands.maintenance') }}" class="btn btn-lg btn-primary mx-4">
                Modo manutancao
            </a>
            <a href="#" class="btn btn-lg btn-danger mx-4">
                Desativar Site
            </a>
        </div>
    </div>
</div>
@endsection
