@extends('layouts.adminlte')

@section('title', $producto->exists ? 'Editar producto' : 'Nuevo producto')

@section('content_header')
    <h1 class="fw-bold">{{ $producto->exists ? 'Editar producto' : 'Nuevo producto' }}</h1>
@endsection

@section('content')
    @include('components.flash')

    <form class="card" method="POST" enctype="multipart/form-data" action="{{ $producto->exists ? route('entrepreneur.products.update', $producto) : route('entrepreneur.products.store') }}">
        <div class="card-body p-4">
            @csrf
            @if($producto->exists)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $producto->nombre) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="precio">Precio</label>
                    <input class="form-control" id="precio" name="precio" type="number" step="0.01" min="0.01" value="{{ old('precio', $producto->precio) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="categoria_id">Categoría</label>
                    <select class="form-select" id="categoria_id" name="categoria_id">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(old('categoria_id', $producto->categoria_id) == $categoria->id)>{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="imagen">Imagen</label>
                    <input
                        class="form-control"
                        id="imagen"
                        name="imagen"
                        type="file"
                        accept="image/png,image/jpeg,image/webp"
                        data-image-preview-target="#product-image-preview"
                    >
                    <div class="form-text">JPG, PNG o WebP. Máximo 2 MB.</div>
                </div>

                <div class="col-12">
                    <img
                        id="product-image-preview"
                        class="image-upload-preview"
                        src="{{ $producto->imagen_url ? asset('storage/'.$producto->imagen_url) : '' }}"
                        alt="Vista previa de {{ $producto->nombre ?: 'la imagen del producto' }}"
                        @if(! $producto->imagen_url) hidden @endif
                    >
                </div>

                <div class="col-12">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4">{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>

                <div class="col-12">
                    <input type="hidden" name="estado" value="0">
                    <div class="form-check">
                        <input class="form-check-input" id="estado" type="checkbox" name="estado" value="1" @checked(old('estado', $producto->exists ? $producto->estado : true))>
                        <label class="form-check-label" for="estado">Producto activo</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white d-flex gap-2">
            <button class="btn btn-primary">Guardar</button>
            <a class="btn btn-outline-secondary" href="{{ route('entrepreneur.products.index') }}">Cancelar</a>
        </div>
    </form>
@endsection
