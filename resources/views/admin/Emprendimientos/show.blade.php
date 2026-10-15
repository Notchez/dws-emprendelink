@extends('layouts.adminlte')

@section('content')
<div class="container-fluid pt-3">
    <div class="row mb-3">
        <div class="col-sm-12">
            <h1 class="m-0 text-dark font-weight-bold">Detalle del emprendedor</h1>
            <p class="text-muted">Consulta la información y administra el estado del emprendimiento.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0" style="max-width: 800px; margin: 0 auto;">
        <div class="card-body p-4">
            <!-- Información del Emprendimiento -->
            <h5 class="font-weight-bold mb-3 text-primary">Información del emprendimiento</h5>
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Emprendimiento:</div>
                <div class="col-md-8 font-weight-bold">{{ $emprendimiento->nombre }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Propietario:</div>
                <div class="col-md-8 font-weight-bold">{{ $emprendimiento->usuario->name ?? 'N/A' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Correo:</div>
                <div class="col-md-8 font-weight-bold">{{ $emprendimiento->usuario->email ?? 'N/A' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Teléfono:</div>
                <div class="col-md-8 font-weight-bold">{{ $emprendimiento->telefono_contacto ?? '7000-0000' }}</div>
            </div>
            <div class="row mb-2">
    <div class="col-md-4 text-muted">Plan:</div>
    <div class="col-md-8 font-weight-bold">
        @php
            $suscripcionShow = $emprendimiento->suscripciones()->latest()->first();
            $nombrePlanShow = ($suscripcionShow && $suscripcionShow->plan) ? $suscripcionShow->plan->nombre : 'Básico';
        @endphp
        {{ $nombrePlanShow }}
    </div>
</div>
            <div class="row mb-4">
                <div class="col-md-4 text-muted">Estado actual:</div>
                <div class="col-md-8 font-weight-bold">
                    @php
                        $suscripcionActual = $emprendimiento->suscripciones()->latest()->first();
                        $estadoActual = $suscripcionActual ? $suscripcionActual->estado : 'activa';
                    @endphp
                    <span class="badge {{ strtolower($estadoActual) == 'activa' ? 'bg-success' : 'bg-danger' }} px-3 py-1">
                        {{ ucfirst($estadoActual) }}
                    </span>
                </div>
            </div>

            <hr>

            <!-- Información Financiera -->
            <!-- Información Financiera -->
<h5 class="font-weight-bold mb-3 text-primary">Información financiera</h5>
<div class="row mb-2">
    <div class="col-md-4 text-muted">Ventas generadas:</div>
    <div class="col-md-8 font-weight-bold">
        @php
            // Suma el 'monto_base' de todas las comisiones registradas para este emprendimiento
            $totalVentas = $emprendimiento->comisiones ? $emprendimiento->comisiones->sum('monto_base') : 0.00;
        @endphp
        ${{ number_format($totalVentas, 2) }}
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-4 text-muted">Comisión pendiente:</div>
    <div class="col-md-8 font-weight-bold">
        @php
            // Suma el total de comisión de los registros pendientes (o el total general de comisiones)
            $totalComision = $emprendimiento->comisiones ? $emprendimiento->comisiones->where('estado', 'pendiente')->sum('total_comision') : 0.00;
        @endphp
        ${{ number_format($totalComision, 2) }}
    </div>
</div>

            <hr>

            <!-- Formulario para Modificar Estado (RF-08) -->
            <form action="{{ route('admin.emprendimientos.status', $emprendimiento->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group mb-3">
                    <label class="form-label font-weight-bold">Cambiar estado de la suscripción</label>
                    <select name="estado" class="form-control">
                        <option value="activa" {{ strtolower($estadoActual) == 'activa' ? 'selected' : '' }}>Activa</option>
                        <option value="vencida" {{ strtolower($estadoActual) == 'vencida' ? 'selected' : '' }}>Vencida</option>
                        <option value="cancelada" {{ strtolower($estadoActual) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>
                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.emprendimientos.index') }}" class="btn btn-secondary me-2">Volver</a>
                    <button type="submit" class="btn btn-primary px-4">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection