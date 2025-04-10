@extends('layouts.app')

@section('content')
<title>{{ $pizza->name }}</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <img src="{{ asset($pizza->image_url) }}" class="img-fluid" alt="{{ $pizza->name }}">
            </div>
            <div class="col-md-6">
                <h1>{{ $pizza->name }}</h1>
                <p class="lead">{{ $pizza->ingredients }}</p>
                
                <div class="mb-4">
                    <h3>Пищевая ценность (на 100г):</h3>
                    <ul>
                        <li>Белки: {{ $pizza->proteins }} г</li>
                        <li>Жиры: {{ $pizza->fats }} г</li>
                        <li>Углеводы: {{ $pizza->carbohydrates }} г</li>
                    </ul>
                </div>
                
                <div class="mt-4">
                    <h3>Доступные размеры:</h3>
                    @if($pizza->sizes->count() > 0)
                        <div class="list-group mt-3">
                            @foreach($pizza->sizes as $size)
                            <div class="list-group-item mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1">{{ $size->size_name }}</h5>
                                        <small class="text-muted">
                                            {{ $size->diameter }} см • {{ $size->weight}} г
                                        </small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill addBtn">
                                        {{ $size->price }} руб.
                                    </span>
                                    <button data-id="{{$pizza->id}}" data-size ="{{$size->size_name}}" onclick='addToCart(event)' type ='button' class = 'btn btn-danger rounded-pill fw-bold'>Добавить в корзину</button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mt-3">
                            Нет доступных размеров для этой пиццы
                        </div>
                    @endif
                </div>
                
                <a href="{{ route('pizzas.catalog') }}" class="btn btn-secondary">
                    ← Назад к меню
                </a>
            </div>
        </div>
    </div>
</body>
<script src="{{ asset('js/cart.js') }}"></script>
@endsection

