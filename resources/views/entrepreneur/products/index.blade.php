@extends('layouts.adminlte')

@section('title', 'Productos')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between gap-3">
        <div>
            <h1 class="fw-bold">Productos</h1>
            <p class="text-body-secondary mb-0">Administra el catálogo de tu negocio.</p>
        </div>
        <a class="btn btn-primary align-self-start" href="{{ route('entrepreneur.products.create') }}">Nuevo producto</a>
    </div>
@endsection

@section('content')
    @include('components.flash')

    <div class="row g-4">
        @forelse($productos as $producto)
            <div class="col-12 col-sm-6 col-xl-4">
                <article class="card product-card h-100">
                    @if($producto->imagen_url)
                        <img class="admin-product-image" src="{{ asset('storage/'.$producto->imagen_url) }}" alt="{{ $producto->nombre }}">
                    @else
                        <div class="product-placeholder"><i class="bi bi-image" aria-hidden="true"></i></div>
                    @endif

                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <span class="badge badge-soft">{{ $producto->categoria?->nombre ?? 'Sin categoría' }}</span>
                            <span class="badge {{ $producto->estado ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $producto->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <h2 class="h5 fw-bold">{{ $producto->nombre }}</h2>
                        <p class="price mb-4">${{ number_format($producto->precio, 2) }}</p>

                        <div class="d-flex flex-wrap gap-2 mt-auto">
                            <a class="btn btn-outline-primary flex-grow-1" href="{{ route('entrepreneur.products.edit', $producto) }}">
                                <i class="bi bi-pencil me-1" aria-hidden="true"></i>Editar
                            </a>
                            <form class="flex-grow-1" method="POST" action="{{ route('entrepreneur.products.status', $producto) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="estado" value="{{ $producto->estado ? 0 : 1 }}">
                                <button class="btn {{ $producto->estado ? 'btn-outline-danger' : 'btn-outline-success' }} w-100">
                                    {{ $producto->estado ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="card empty-state">
                    <i class="bi bi-box-seam fs-1" aria-hidden="true"></i>
                    <h2 class="h5 mt-3">No hay productos.</h2>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $productos->links() }}</div>
@endsection
