@extends('layouts.app')

@section('title', 'Editar - Cupom')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar Cupom
</p>
<form action="{{ route('coupons.update', $coupon->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body">
            @include('pages.coupons._form')
        </div>
    </div>
    <div class="d-flex justify-content-end mt-4">
        <a href="{{ route('coupons.index') }}" class="btn btn-light text-muted me-2">
            Voltar
        </a>
        <button type="submit" class="btn btn-primary btn-active-danger">
            Salvar
        </button>
    </div>
</form>
@endsection
