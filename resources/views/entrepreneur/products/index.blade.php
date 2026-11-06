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

    <div class="row g-3 entrepreneur-products-grid">
        @forelse($productos as $producto)
            <div class="col-12 col-sm-6 col-lg-4 col-xxl-3">
                <article class="card product-card entrepreneur-product-card h-100">
                    @if($producto->imagen_url)
                        <img class="admin-product-image entrepreneur-product-image" src="{{ asset('storage/'.$producto->imagen_url) }}" alt="{{ $producto->nombre }}">
                    @else
                        <div class="product-placeholder entrepreneur-product-placeholder"><i class="bi bi-image" aria-hidden="true"></i></div>
                    @endif

                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <span class="badge badge-soft">{{ $producto->categoria?->nombre ?? 'Sin categoría' }}</span>
                            <span class="badge {{ $producto->estado ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $producto->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <h2 class="h5 fw-bold mb-1">{{ $producto->nombre }}</h2>
                        <p class="price mb-3">${{ number_format($producto->precio, 2) }}</p>

                        <div class="d-flex flex-wrap gap-2 mt-auto entrepreneur-product-actions">
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
                            <button class="btn btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteProduct-{{ $producto->id }}" aria-label="Eliminar {{ $producto->nombre }}">
                                <i class="bi bi-trash" aria-hidden="true"></i>
                            </button>
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

    @foreach($productos as $producto)
        <div class="modal fade" id="deleteProduct-{{ $producto->id }}" tabindex="-1" aria-labelledby="deleteProductLabel-{{ $producto->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
                <div class="modal-header"><h2 class="modal-title fs-5" id="deleteProductLabel-{{ $producto->id }}">Eliminar producto</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                <div class="modal-body">¿Deseas eliminar <strong>{{ $producto->nombre }}</strong>? Se conservará en el historial de pedidos.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="{{ route('entrepreneur.products.destroy', $producto) }}">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Eliminar</button></form>
                </div>
            </div></div>
        </div>
    @endforeach

    <div class="mt-4">{{ $productos->links() }}</div>
@endsection
