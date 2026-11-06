@extends('layouts.adminlte')

@section('title', 'Categorías')

@section('content_header')
    <h1 class="fw-bold">Categorías</h1>
@endsection

@section('content')
    @include('components.flash')

    <div class="row g-4">
        <div class="col-lg-4">
            <form class="card shadow-sm" method="POST" action="{{ route('entrepreneur.categories.store') }}">
                <div class="card-body">
                    @csrf
                    <h2 class="h5 fw-bold mb-3">Nueva categoría</h2>
                    <div class="mb-3">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" maxlength="100" value="{{ old('nombre') }}" required>
                        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="descripcion">Descripción</label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="3" maxlength="255" data-character-counter="#create-char-count">{{ old('descripcion') }}</textarea>
                        @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-body-secondary">Máximo 255 caracteres.</small>
                            <small class="text-body-secondary" id="create-char-count" aria-live="polite">0 / 255</small>
                        </div>
                    </div>
                    <input type="hidden" name="estado" value="1">
                    <button class="btn btn-primary w-100">Crear categoría</button>
                </div>
            </form>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Nombre</th><th>Productos</th><th>Estado</th><th class="text-end">Acciones</th></tr>
                        </thead>
                        <tbody>
                            @forelse($categorias as $categoria)
                                <tr>
                                    <td class="fw-semibold">{{ $categoria->nombre }}</td>
                                    <td><span class="badge text-bg-secondary">{{ $categoria->productos_count }}</span></td>
                                    <td><span class="badge {{ $categoria->estado ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $categoria->estado ? 'Activa' : 'Inactiva' }}</span></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal-{{ $categoria->id }}"><i class="bi bi-pencil" aria-hidden="true"></i> Editar</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $categoria->id }}"><i class="bi bi-trash" aria-hidden="true"></i> Eliminar</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="empty-state">No hay categorías registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @foreach($categorias as $categoria)
        <div class="modal fade" id="editModal-{{ $categoria->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $categoria->id }}" aria-hidden="true">
            <div class="modal-dialog"><div class="modal-content">
                <form method="POST" action="{{ route('entrepreneur.categories.update', $categoria) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h2 class="modal-title fs-5 fw-bold" id="editModalLabel-{{ $categoria->id }}">Editar categoría: {{ $categoria->nombre }}</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="nombre-{{ $categoria->id }}">Nombre</label>
                            <input class="form-control" id="nombre-{{ $categoria->id }}" name="nombre" maxlength="100" value="{{ $categoria->nombre }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="descripcion-{{ $categoria->id }}">Descripción</label>
                            <textarea class="form-control" id="descripcion-{{ $categoria->id }}" name="descripcion" rows="3" maxlength="255" data-character-counter="#edit-char-count-{{ $categoria->id }}">{{ $categoria->descripcion }}</textarea>
                            <div class="d-flex justify-content-between mt-1"><small class="text-body-secondary">Máximo 255 caracteres.</small><small class="text-body-secondary" id="edit-char-count-{{ $categoria->id }}" aria-live="polite">0 / 255</small></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="estado-{{ $categoria->id }}">Estado</label>
                            <select class="form-select" id="estado-{{ $categoria->id }}" name="estado"><option value="1" @selected($categoria->estado)>Activa</option><option value="0" @selected(! $categoria->estado)>Inactiva</option></select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar cambios</button></div>
                </form>
            </div></div>
        </div>

        <div class="modal fade" id="deleteModal-{{ $categoria->id }}" tabindex="-1" aria-labelledby="deleteModalLabel-{{ $categoria->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
                <div class="modal-header"><h2 class="modal-title fs-5 fw-bold text-danger" id="deleteModalLabel-{{ $categoria->id }}"><i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>Confirmar eliminación</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                <div class="modal-body"><p>¿Deseas eliminar la categoría <strong>{{ $categoria->nombre }}</strong>?</p><p class="text-body-secondary small mb-0">Los productos asociados quedarán sin categoría.</p></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="{{ route('entrepreneur.categories.destroy', $categoria) }}">@csrf @method('DELETE')<button type="submit" class="btn btn-danger">Sí, eliminar</button></form>
                </div>
            </div></div>
        </div>
    @endforeach
@endsection
