@extends('layouts.adminlte')

@section('title', 'Mis productos')

@section('content_header')
    <h1>Mis productos</h1>
    <p class="text-muted">
        Administra los productos de tu emprendimiento.
    </p>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Revisa los datos ingresados:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Registrar producto</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('entrepreneur.products.store') }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text"
                           name="nombre"
                           id="nombre"
                           class="form-control"
                           maxlength="150"
                           value="{{ old('nombre') }}"
                           required>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">
                        Descripción
                    </label>
                    <textarea name="descripcion"
                              id="descripcion"
                              class="form-control"
                              rows="3">{{ old('descripcion') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="precio" class="form-label">
                        Precio ($)
                    </label>
                    <input type="number"
                           name="precio"
                           id="precio"
                           class="form-control"
                           min="0"
                           max="999999.99"
                           step="0.01"
                           value="{{ old('precio') }}"
                           required>
                </div>

                <div class="mb-3">
                    <label for="categoria_id" class="form-label">
                        Categoría
                    </label>
                    <select name="categoria_id"
                            id="categoria_id"
                            class="form-select">
                        <option value="">Sin categoría</option>

                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}"
                                @selected(old('categoria_id') == $categoria->id)>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="imagen" class="form-label">
                        Fotografía del producto
                    </label>

                    <input type="file"
                           name="imagen"
                           id="imagen"
                           class="form-control"
                           accept=".jpg,.jpeg,.png,.webp">

                    <small class="text-muted">
                        Formatos permitidos: JPG, PNG y WebP.
                        Máximo 2 MB.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">
                    Guardar producto
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Productos registrados</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Fotografía</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($productos as $producto)
                            <tr>
                                <td>
                                    @if ($producto->imagen_url)
                                        <img src="{{ asset('storage/' . $producto->imagen_url) }}"
                                             alt="{{ $producto->nombre }}"
                                             style="width: 80px; height: 80px; object-fit: cover;"
                                             class="rounded">
                                    @else
                                        <span class="text-muted">
                                            Sin fotografía
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $producto->nombre }}
                                </td>

                                <td>
                                    {{ $producto->descripcion ?: 'Sin descripción' }}
                                </td>

                                <td>
                                    ${{ number_format($producto->precio, 2) }}
                                </td>

                                <td>
                                    {{ $producto->categoria?->nombre ?? 'Sin categoría' }}
                                </td>

                                <td>
                                    @if ($producto->estado)
                                        <span class="badge bg-success">
                                            Activo
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            Inactivo
                                        </span>
                                    @endif

                                    <form action="{{ route('entrepreneur.products.toggle-status', $producto) }}"
                                          method="POST"
                                          class="mt-2">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="btn btn-sm {{ $producto->estado ? 'btn-secondary' : 'btn-success' }}">
                                            {{ $producto->estado ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </td>

                                <td>
                                    <form action="{{ route('entrepreneur.products.update', $producto) }}"
                                          method="POST"
                                          enctype="multipart/form-data"
                                          class="mb-2">
                                        @csrf
                                        @method('PUT')

                                        <input type="text"
                                               name="nombre"
                                               value="{{ $producto->nombre }}"
                                               class="form-control form-control-sm mb-1"
                                               maxlength="150"
                                               required
                                               aria-label="Nombre del producto">

                                        <textarea name="descripcion"
                                                  class="form-control form-control-sm mb-1"
                                                  aria-label="Descripción del producto">{{ $producto->descripcion }}</textarea>

                                        <input type="number"
                                               name="precio"
                                               value="{{ $producto->precio }}"
                                               min="0"
                                               max="999999.99"
                                               step="0.01"
                                               class="form-control form-control-sm mb-1"
                                               required
                                               aria-label="Precio del producto">

                                        <select name="categoria_id"
                                                class="form-select form-select-sm mb-2"
                                                aria-label="Categoría del producto">
                                            <option value="">
                                                Sin categoría
                                            </option>

                                            @foreach ($categorias as $categoria)
                                                <option value="{{ $categoria->id }}"
                                                    @selected($producto->categoria_id == $categoria->id)>
                                                    {{ $categoria->nombre }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <div class="mb-2">
                                            <label class="form-label">
                                                Cambiar fotografía
                                            </label>

                                            <input type="file"
                                                   name="imagen"
                                                   class="form-control form-control-sm"
                                                   accept=".jpg,.jpeg,.png,.webp">

                                            <small class="text-muted">
                                                Opcional. Máximo 2 MB.
                                            </small>
                                        </div>

                                        <button type="submit"
                                                class="btn btn-warning btn-sm">
                                            Actualizar
                                        </button>
                                    </form>

                                    <form action="{{ route('entrepreneur.products.destroy', $producto) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este producto?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-danger btn-sm">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"
                                    class="text-center text-muted">
                                    Todavía no tienes productos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
