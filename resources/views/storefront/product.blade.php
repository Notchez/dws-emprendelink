@extends('layouts.storefront')

@section('title', $producto->nombre)

@section('content')
    <a href="{{ route('catalog.business', ['emprendimiento' => $producto->emprendimiento->slug]) }}" class="text-decoration-none">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver al catálogo
    </a>

    <div class="row g-5 mt-1 align-items-start">
        <div class="col-lg-6">
            @if($producto->imagen_url)
                <img class="product-detail-image rounded-4" src="{{ asset('storage/'.$producto->imagen_url) }}" alt="{{ $producto->nombre }}">
            @else
                <div class="product-placeholder rounded-4"><i class="bi bi-image" aria-hidden="true"></i></div>
            @endif
        </div>

        <div class="col-lg-6">
            <span class="badge badge-soft">{{ $producto->categoria?->nombre ?? 'General' }}</span>
            <h1 class="display-6 fw-bold mt-3">{{ $producto->nombre }}</h1>
            <p class="price fs-2">${{ number_format($producto->precio, 2) }}</p>
            <p class="text-body-secondary">{{ $producto->descripcion ?: 'Sin descripción disponible.' }}</p>
            <p><strong>Emprendimiento:</strong> {{ $producto->emprendimiento->nombre }}</p>

            @auth
                @if(auth()->user()->hasRole(\App\Enums\UserRole::CLIENTE))
                    <form method="POST" action="{{ route('cart.store', $producto) }}" class="mt-4">
                        @csrf
                        <label class="form-label" for="cantidad">Cantidad</label>
                        <div class="d-flex gap-2">
                            <input class="form-control" style="max-width:7rem" id="cantidad" name="cantidad" type="number" min="1" max="99" value="1">
                            <button class="btn btn-primary flex-grow-1"><i class="bi bi-bag-plus me-2" aria-hidden="true"></i>Agregar al carrito</button>
                        </div>
                    </form>
                @else
                    <div class="alert alert-info mt-4">Las cuentas administrativas y de emprendedor no pueden comprar.</div>
                @endif
            @else
                <div class="alert alert-light border mt-4">Inicia sesión como cliente para agregar productos. <a href="{{ route('login') }}">Ingresar</a></div>
            @endauth
        </div>
    </div>
@endsection
