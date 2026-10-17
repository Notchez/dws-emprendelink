@extends('layouts.storefront')

@section('title', $emprendimiento ? $emprendimiento->nombre : 'Catálogo')

@section('content')
    <section class="mb-5">
        <span class="badge badge-soft mb-3">{{ $emprendimiento ? 'Catálogo del negocio' : 'Marketplace local' }}</span>
        <h1 class="display-6 fw-bold mb-2">{{ $emprendimiento?->nombre ?? 'Descubre productos de emprendedores' }}</h1>
        <p class="text-body-secondary">
            {{ $emprendimiento?->descripcion ?: 'Encuentra productos, apoya negocios locales y administra tus pedidos con facilidad.' }}
        </p>

        <form class="row g-2 mt-4" method="GET">
            <div class="col-12 col-md-7">
                <label class="visually-hidden" for="search">Buscar</label>
                <input class="form-control form-control-lg" id="search" name="search" value="{{ request('search') }}" placeholder="Buscar productos...">
            </div>
            <div class="col-8 col-md-3">
                <select class="form-select form-select-lg" name="categoria" aria-label="Categoría">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" @selected(request('categoria') == $categoria->id)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 col-md-2">
                <button class="btn btn-primary btn-lg w-100">Buscar</button>
            </div>
        </form>
    </section>

    <div class="d-flex align-items-end justify-content-between mb-3">
        <div>
            <h2 class="h3 fw-bold mb-1">Productos</h2>
            <p class="text-body-secondary mb-0">{{ $productos->total() }} resultados disponibles</p>
        </div>
    </div>

    <div class="row g-4">
        @forelse($productos as $producto)
            <div class="col-12 col-sm-6 col-lg-4">
                <article class="card product-card h-100">
                    @if($producto->imagen_url)
                        <img class="product-card-image" src="{{ asset('storage/'.$producto->imagen_url) }}" alt="{{ $producto->nombre }}">
                    @else
                        <div class="product-placeholder"><i class="bi bi-image" aria-hidden="true"></i></div>
                    @endif

                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between gap-2 mb-2">
                            <span class="badge badge-soft">{{ $producto->categoria?->nombre ?? 'General' }}</span>
                            <small class="text-success"><i class="bi bi-circle-fill me-1" aria-hidden="true"></i>Disponible</small>
                        </div>
                        <h3 class="h5 fw-bold">{{ $producto->nombre }}</h3>
                        <p class="text-body-secondary small flex-grow-1">{{ \Illuminate\Support\Str::limit($producto->descripcion, 100) }}</p>
                        <p class="small mb-1">Por {{ $producto->emprendimiento->nombre }}</p>
                        <p class="price">${{ number_format($producto->precio, 2) }}</p>
                        <a class="btn btn-outline-primary" href="{{ route('catalog.show', $producto) }}">Ver producto</a>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="card empty-state">
                    <i class="bi bi-search fs-1" aria-hidden="true"></i>
                    <h2 class="h5 mt-3">No encontramos productos</h2>
                    <p>Ajusta la búsqueda o elimina los filtros.</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $productos->links() }}</div>
@endsection
