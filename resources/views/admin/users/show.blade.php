@extends('layouts.adminlte')

@section('title', $usuario->name)

@section('content_header')
    <h1 class="fw-bold">Detalle del usuario</h1>
@endsection

@section('content')
    @include('components.flash')

    <div class="row g-4">
        <div class="col-xl-8">
            <section class="card mb-4">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Datos de la cuenta</h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Responsable</dt>
                        <dd class="col-sm-8">{{ $usuario->name }}</dd>
                        <dt class="col-sm-4">Correo</dt>
                        <dd class="col-sm-8">{{ $usuario->email }}</dd>
                        <dt class="col-sm-4">Teléfono personal</dt>
                        <dd class="col-sm-8">{{ $usuario->telefono ?: 'No registrado' }}</dd>
                        <dt class="col-sm-4">Dirección personal</dt>
                        <dd class="col-sm-8">{{ $usuario->direccion ?: 'No registrada' }}</dd>
                        <dt class="col-sm-4">Rol</dt>
                        <dd class="col-sm-8">{{ $usuario->rol->label() }}</dd>
                        <dt class="col-sm-4">Estado de la cuenta</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $usuario->estado ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $usuario->estado ? 'Activa' : 'Suspendida' }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </section>

            <section class="card">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-0">Emprendimiento asociado</h2>
                </div>
                <div class="card-body">
                    @if($usuario->emprendimiento)
                        @php($plan = $usuario->emprendimiento->suscripcionActiva?->plan)
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Nombre del negocio</dt>
                            <dd class="col-sm-8">{{ $usuario->emprendimiento->nombre }}</dd>
                            <dt class="col-sm-4">Descripción</dt>
                            <dd class="col-sm-8">{{ $usuario->emprendimiento->descripcion ?: 'No registrada' }}</dd>
                            <dt class="col-sm-4">Teléfono de contacto</dt>
                            <dd class="col-sm-8">{{ $usuario->emprendimiento->telefono_contacto ?: 'No registrado' }}</dd>
                            <dt class="col-sm-4">Slug</dt>
                            <dd class="col-sm-8"><code>{{ $usuario->emprendimiento->slug }}</code></dd>
                            <dt class="col-sm-4">Estado del emprendimiento</dt>
                            <dd class="col-sm-8">{{ $usuario->emprendimiento->estado ? 'Activo' : 'Inactivo' }}</dd>
                            <dt class="col-sm-4">Plan actual</dt>
                            <dd class="col-sm-8">{{ $plan?->nombre ?? 'Sin plan' }}</dd>
                            <dt class="col-sm-4">Límite de productos</dt>
                            <dd class="col-sm-8">{{ $plan?->limite_productos ?? 'No aplica' }}</dd>
                            <dt class="col-sm-4">Comisión del plan</dt>
                            <dd class="col-sm-8">{{ $plan ? number_format($plan->porcentaje_comision, 2).'%' : 'No aplica' }}</dd>
                        </dl>
                    @else
                        <p class="text-body-secondary mb-0">Este usuario no tiene un emprendimiento asociado.</p>
                    @endif
                </div>
            </section>
        </div>

        <aside class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 fw-bold">Acceso</h2>
                    @if(auth()->id() === $usuario->id)
                        <p class="text-body-secondary mb-0">No puedes suspender tu propia cuenta.</p>
                    @else
                        <form method="POST" action="{{ route('admin.users.status', $usuario) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="estado" value="{{ $usuario->estado ? 0 : 1 }}">
                            <button class="btn {{ $usuario->estado ? 'btn-outline-danger' : 'btn-primary' }} w-100">
                                {{ $usuario->estado ? 'Suspender cuenta' : 'Activar cuenta' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </aside>
    </div>
@endsection
