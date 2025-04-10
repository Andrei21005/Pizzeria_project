@extends('layouts.app')

@section('content')
<title>Каталог</title>
<div class="container mt-5">
    <h1 class="mb-4">Наше меню</h1>
    <div class="row">
        @foreach($pizzas as $pizza)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="{{ asset($pizza->image_url) }}" class="card-img-top w-30 h-30" alt="{{ $pizza->name }}">
                <div class="card-body">
                    <h5 class="card-title">{{ $pizza->name }}</h5>
                    <p class="card-text">{{ $pizza->ingredients }}</p>
                    
                    <div class="mb-3">
                        <h6>Цена:</h6>
                        @if($pizza->sizes->isNotEmpty())
                            От {{ $pizza->sizes->first()->price }} руб.
                        @else
                            Цена не указана
                        @endif
                    </div>
                    
                    <a href="{{ route('pizzas.show', $pizza->id) }}" class="btn btn-danger">
                        Подробнее
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection