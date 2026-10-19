@extends('layouts.adminlte')

@section('title', $plan->exists ? 'Editar plan' : 'Nuevo plan')

@section('content_header')
    <h1 class="fw-bold">{{ $plan->exists ? 'Editar plan' : 'Nuevo plan' }}</h1>
@endsection

@section('content')
    @include('components.flash')

    <form class="card" method="POST" action="{{ $plan->exists ? route('admin.planes.update', $plan) : route('admin.planes.store') }}">
        @csrf
        @if($plan->exists)
            @method('PUT')
        @endif

        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $plan->nombre) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="precio_mensual">Precio mensual</label>
                    <input class="form-control" id="precio_mensual" name="precio_mensual" type="number" step="0.01" min="0" value="{{ old('precio_mensual', $plan->precio_mensual) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="porcentaje_comision">Comisión (%)</label>
                    <input class="form-control" id="porcentaje_comision" name="porcentaje_comision" type="number" step="0.01" min="0" max="100" value="{{ old('porcentaje_comision', $plan->porcentaje_comision) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="limite_productos">Límite de productos</label>
                    <input class="form-control" id="limite_productos" name="limite_productos" type="number" min="1" value="{{ old('limite_productos', $plan->limite_productos) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $plan->descripcion) }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white">
            <button class="btn btn-primary">Guardar plan</button>
        </div>
    </form>
@endsection
