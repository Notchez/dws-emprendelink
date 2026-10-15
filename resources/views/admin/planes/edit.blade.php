@extends('layouts.adminlte')

@section('content')
<div class="container-fluid pt-3">
    <div class="row mb-3">
        <div class="col-sm-12">
            <h1 class="m-0 text-dark font-weight-bold">Editar Plan: {{ $plan->nombre }}</h1>
            <p class="text-muted">Modifica los costos, comisiones y restricciones de este plan.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="max-width: 800px; margin: 0 auto;">
        <div class="card-body p-4">
            <form action="{{ route('admin.planes.update', $plan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label class="form-label font-weight-bold">Nombre del Plan</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $plan->nombre) }}" required>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label font-weight-bold">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $plan->descripcion) }}</textarea>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label font-weight-bold">Precio Mensual ($)</label>
                    <input type="number" step="0.01" name="precio_mensual" class="form-control" value="{{ old('precio_mensual', $plan->precio_mensual) }}" required>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label font-weight-bold">Porcentaje de Comisión (%)</label>
                    <input type="number" step="0.01" name="porcentaje_comision" class="form-control" value="{{ old('porcentaje_comision', $plan->porcentaje_comision) }}" required>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label font-weight-bold">Límite de Productos</label>
                    <input type="number" name="limite_productos" class="form-control" value="{{ old('limite_productos', $plan->limite_productos) }}" required>
                    <small class="text-muted">Usa 0 para productos ilimitados.</small>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.planes.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection