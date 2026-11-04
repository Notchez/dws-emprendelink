@extends('layouts.adminlte')

@section('title', 'Categorías')

@section('content_header')
    <h1 class="fw-bold">Categorías</h1>
@endsection

@section('content')
    @include('components.flash')

    <div class="row g-4">
        <!-- Formulario de Creación (Izquierda) -->
        <div class="col-lg-4">
            <form class="card shadow-sm" method="POST" action="{{ route('entrepreneur.categories.store') }}">
                <div class="card-body">
                    @csrf
                    <h2 class="h5 fw-bold mb-3">Nueva categoría</h2>
                    
                    <div class="mb-3">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input class="form-control" id="nombre" name="nombre" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="descripcion">Descripción</label>
                        <textarea class="form-control" 
                                  id="descripcion" 
                                  name="descripcion" 
                                  rows="3" 
                                  maxlength="255" 
                                  oninput="updateCharCount(this, 'create-char-count')"
                                  style="resize: none;"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Máximo 255 caracteres.</small>
                            <small class="text-muted" id="create-char-count">0 / 255</small>
                        </div>
                    </div>

                    <input type="hidden" name="estado" value="1">
                    <button class="btn btn-primary w-100">Crear categoría</button>
                </div>
            </form>
        </div>

        <!-- Listado de Categorías con Botones Modales (Derecha) -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Productos</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categorias as $categoria)
                                <tr>
                                    <td class="fw-semibold">{{ $categoria->nombre }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $categoria->productos_count }}</span>
                                    </td>
                                    <td>
                                        @if($categoria->estado)
                                            <span class="badge bg-success">Activa</span>
                                        @else
                                            <span class="badge bg-danger">Inactiva</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <!-- Botón que activa el Modal de Edición -->
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal-{{ $categoria->id }}">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>

                                        <!-- Botón que activa el Modal de Confirmación de Eliminación -->
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $categoria->id }}" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>

                                <!-- MODAL DE EDICIÓN PARA CADA CATEGORÍA -->
                                <div class="modal fade" id="editModal-{{ $categoria->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $categoria->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('entrepreneur.categories.update', $categoria) }}">
                                                @csrf 
                                                @method('PUT')
                                                
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold" id="editModalLabel-{{ $categoria->id }}">Editar categoría: {{ $categoria->nombre }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>
                                                
                                                <div class="modal-body text-start">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="nombre-{{ $categoria->id }}">Nombre</label>
                                                        <input class="form-control" id="nombre-{{ $categoria->id }}" name="nombre" value="{{ $categoria->nombre }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label" for="descripcion-{{ $categoria->id }}">Descripción</label>
                                                        <textarea class="form-control" 
                                                                  id="descripcion-{{ $categoria->id }}" 
                                                                  name="descripcion" 
                                                                  rows="3" 
                                                                  maxlength="255" 
                                                                  oninput="updateCharCount(this, 'edit-char-count-{{ $categoria->id }}')"
                                                                  style="resize: none;">{{ $categoria->descripcion }}</textarea>
                                                        <div class="d-flex justify-content-between mt-1">
                                                            <small class="text-muted">Máximo 255 caracteres.</small>
                                                            <small class="text-muted" id="edit-char-count-{{ $categoria->id }}">0 / 255</small>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label" for="estado-{{ $categoria->id }}">Estado</label>
                                                        <select class="form-select" id="estado-{{ $categoria->id }}" name="estado">
                                                            <option value="1" @selected($categoria->estado)>Activa</option>
                                                            <option value="0" @selected(!$categoria->estado)>Inactiva</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary btn-sm">Guardar cambios</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN -->
                                <div class="modal fade" id="deleteModal-{{ $categoria->id }}" tabindex="-1" aria-labelledby="deleteModalLabel-{{ $categoria->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title fw-bold text-danger" id="deleteModalLabel-{{ $categoria->id }}">
                                                    <i class="fas fa-exclamation-triangle me-1"></i> Confirmar eliminación
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            
                                            <div class="modal-body text-start">
                                                <p class="mb-1">¿Estás seguro de que deseas eliminar la categoría <strong>"{{ $categoria->nombre }}"</strong>?</p>
                                                <p class="text-muted small mb-0">Los productos asociados a esta categoría quedarán sin categoría.</p>
                                            </div>

                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                
                                                <form method="POST" action="{{ route('entrepreneur.categories.destroy', $categoria) }}" class="d-inline">
                                                    @csrf 
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Sí, eliminar</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No hay categorías registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para contadores y inicialización de caracteres -->
    <script>
    function updateCharCount(textarea, counterId) {
        const maxLength = textarea.getAttribute('maxlength');
        const currentLength = textarea.value.length;
        const counter = document.getElementById(counterId);
        if (counter) {
            counter.textContent = `${currentLength} / ${maxLength}`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Inicializar contador de creación
        const createDesc = document.getElementById('descripcion');
        if (createDesc) {
            updateCharCount(createDesc, 'create-char-count');
        }

        // Inicializar contadores de los modales en caso de tener texto previo
        @foreach($categorias as $categoria)
            const editDesc_{{ $categoria->id }} = document.getElementById('descripcion-{{ $categoria->id }}');
            if (editDesc_{{ $categoria->id }}) {
                updateCharCount(editDesc_{{ $categoria->id }}, 'edit-char-count-{{ $categoria->id }}');
            }
        @endforeach
    });
    </script>
@endsection