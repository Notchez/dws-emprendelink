@extends('layouts.adminlte')

@section('content')
<div class="container-fluid pt-3">
    <div class="row mb-3">
        <div class="col-sm-12">
            <h1 class="m-0 text-dark font-weight-bold">Detalle del Usuario: {{ $usuario->name }}</h1>
            <p class="text-muted">Información de la cuenta y su emprendimiento asociado.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="max-width: 800px; margin: 0 auto;">
        <div class="card-body p-4">
            <h5 class="font-weight-bold mb-3 text-primary">Información Personal</h5>
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Nombre completo:</div>
                <div class="col-md-8 font-weight-bold">{{ $usuario->name }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Correo electrónico:</div>
                <div class="col-md-8 font-weight-bold">{{ $usuario->email }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Rol:</div>
                <div class="col-md-8 font-weight-bold">{{ ucfirst($usuario->rol ?? 'Emprendedor') }}</div>
            </div>
            <div class="row mb-4">
                <div class="col-md-4 text-muted">Fecha de registro:</div>
                <div class="col-md-8 font-weight-bold">{{ $usuario->created_at ? $usuario->created_at->format('d/m/Y H:i') : 'N/A' }}</div>
            </div>

            <hr>

            <h5 class="font-weight-bold mb-3 text-primary">Emprendimiento Asociado</h5>
            @if($usuario->emprendimiento)
                <div class="d-flex justify-content-between align-items-center p-3 border rounded bg-light">
                    <div>
                        <h6 class="font-weight-bold mb-1">{{ $usuario->emprendimiento->nombre }}</h6>
                        <small class="text-muted">Teléfono: {{ $usuario->emprendimiento->telefono_contacto ?? 'N/A' }}</small>
                    </div>
                    <a href="{{ route('admin.emprendimientos.show', $usuario->emprendimiento->id) }}" class="btn btn-sm btn-outline-primary px-3">
                        Ver Tienda
                    </a>
                </div>
            @else
                <p class="text-muted">Este usuario no tiene un emprendimiento registrado actualmente.</p>
            @endif

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary px-4">Volver al Listado</a>
            </div>
        </div>
    </div>
</div>
@endsection