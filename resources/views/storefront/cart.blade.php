@extends('layouts.storefront')

@section('title', 'Carrito')

@section('content')
    <h1 class="h2 fw-bold mb-4">Mi carrito</h1>
    <div class="alert alert-danger" data-cart-error role="alert" hidden></div>

    <div class="row g-4">
        <div class="col-lg-8" data-cart-items>
            @forelse($items->groupBy(fn ($item) => $item['product']->emprendimiento->nombre) as $business => $group)
                <section class="card mb-3" data-cart-group>
                    <div class="card-header bg-white fw-semibold">{{ $business }}</div>
                    <div class="card-body">
                        @foreach($group as $item)
                            <div class="d-flex gap-3 align-items-center @unless($loop->last) border-bottom pb-3 mb-3 @endunless" data-cart-line>
                                <div class="flex-grow-1">
                                    <span class="badge badge-soft">{{ $item['product']->categoria?->nombre ?? 'General' }}</span>
                                    <h2 class="h6 fw-bold my-2">{{ $item['product']->nombre }}</h2>
                                    <span class="text-body-secondary">{{ number_format($item['product']->precio, 2) }} c/u</span>
                                    <strong class="d-block mt-1" data-cart-line-subtotal>${{ number_format($item['subtotal'], 2) }}</strong>
                                </div>
                                <form method="POST" action="{{ route('cart.update', $item['product']) }}" class="js-cart-quantity-form">
                                    @csrf
                                    @method('PATCH')
                                    <label class="form-label small" for="qty-{{ $item['product']->id }}">Cantidad</label>
                                    <input class="form-control" style="width: 5rem" id="qty-{{ $item['product']->id }}" name="cantidad" type="number" min="0" max="99" value="{{ $item['quantity'] }}">
                                </form>
                                <form method="POST" action="{{ route('cart.destroy', $item['product']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-link text-danger" aria-label="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="card empty-state" data-cart-empty>
                    <i class="bi bi-bag fs-1"></i>
                    <h2 class="h5 mt-3">Tu carrito está vacío</h2>
                    <a href="{{ route('catalog.index') }}">Explorar catálogo</a>
                </div>
            @endforelse

            @if($items->isNotEmpty())
                <div class="card empty-state" data-cart-empty hidden>
                    <i class="bi bi-bag fs-1"></i>
                    <h2 class="h5 mt-3">Tu carrito está vacío</h2>
                    <a href="{{ route('catalog.index') }}">Explorar catálogo</a>
                </div>
            @endif
        </div>

        <aside class="col-lg-4" data-cart-summary @if($items->isEmpty()) hidden @endif>
            <div class="card position-sticky" style="top: 6rem">
                <div class="card-body">
                    <h2 class="h5 fw-bold">Resumen de compra</h2>
                    <div class="d-flex justify-content-between border-bottom py-3">
                        <span>Total</span>
                        <strong class="price" data-cart-total>${{ number_format($total, 2) }}</strong>
                    </div>
                    <p class="small text-body-secondary mt-2 mb-0" data-cart-status aria-live="polite"></p>
                    <a href="{{ route('checkout.create') }}" class="btn btn-primary w-100 mt-3" data-cart-checkout>Continuar pedido</a>
                    <form method="POST" action="{{ route('cart.clear') }}" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-secondary w-100">Vaciar carrito</button>
                    </form>
                </div>
            </div>
        </aside>
    </div>
@endsection
