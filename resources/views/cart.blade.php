@extends('layouts.app')

@section('content')
<title>Корзина</title>
<div class="container mt-5">
    <h1>Ваша корзина</h1>
    <div class="cart-container" id='cart-container'>
        @include('partials.cart') 
    </div>
    <a href="{{ route('pizzas.catalog') }}" class="btn btn-secondary mt-4">← Назад к каталогу</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script src="{{ asset('js/cart.js') }}"></script>
@endsection