
@extends('layouts.adminlte')

@section('title', 'Mis categorías')

@section('content_header')
    <h1>Mis categorías</h1>
    <p class="text-muted">
        Administra las categorías de los productos de tu emprendimiento.
    </p>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Revisa los datos ingresados:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario para registrar categorías -->
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="card-title">Registrar nueva categoría</h2>
        </div>

        <div class="card-body">
            <form action="{{ route('entrepreneur.categories.store') }}"
                  method="POST">

                @csrf

                <div class="mb-3">
                    <label for="nombre" class="form-label">
                        Nombre de la categoría
                    </label>

                    <input type="text"
                           id="nombre"
                           name="nombre"
                           class="form-control"
                           value="{{ old('nombre') }}"
                           maxlength="100"
                           required>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">
                        Descripción
                    </label>

                    <textarea id="descripcion"
                              name="descripcion"
                              rows="3"
                              maxlength="255"
                              class="form-control">{{ old('descripcion') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Guardar categoría
                </button>
            </form>
        </div>
    </div>

    <!-- Listado de categorías -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Categorías registradas</h2>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($categorias as $categoria)
                            <tr>
                                <td>{{ $categoria->nombre }}</td>
                                <td>{{ $categoria->descripcion ?: 'Sin descripción' }}</td>

                                <td>
                                    @if ($categoria->estado)
                                        <span class="badge bg-success">
                                            Activa
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            Inactiva
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <!-- Formulario para editar -->
                                    <form action="{{ route('entrepreneur.categories.update', $categoria) }}"
                                          method="POST"
                                          class="mb-2">

                                        @csrf
                                        @method('PUT')

                                        <input type="text"
                                               name="nombre"
                                               value="{{ $categoria->nombre }}"
                                               maxlength="100"
                                               class="form-control form-control-sm mb-1"
                                               aria-label="Nombre de la categoría"
                                               required>

                                        <input type="text"
                                               name="descripcion"
                                               value="{{ $categoria->descripcion }}"
                                               maxlength="255"
                                               class="form-control form-control-sm mb-1"
                                               aria-label="Descripción de la categoría">

                                        <button type="submit"
                                                class="btn btn-warning btn-sm">
                                            Actualizar
                                        </button>
                                    </form>

                                    <!-- Formulario para eliminar -->
                                    <form action="{{ route('entrepreneur.categories.destroy', $categoria) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar esta categoría?')">

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
                                <td colspan="4" class="text-center text-muted">
                                    Todavía no tienes categorías registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
