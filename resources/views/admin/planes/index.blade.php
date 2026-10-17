@extends('layouts.adminlte')

@section('title', 'Planes')

@section('content_header')
    <div class="d-flex justify-content-between gap-3">
        <div>
            <h1 class="fw-bold">Planes</h1>
            <p class="text-body-secondary">Desactivar un plan no altera suscripciones, pedidos ni comisiones existentes.</p>
        </div>
        <a class="btn btn-primary align-self-start" href="{{ route('admin.planes.create') }}">Nuevo plan</a>
    </div>
@endsection

@section('content')
    @include('components.flash')

    <div class="row g-3">
        @forelse($planes as $plan)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between gap-2">
                            <h2 class="h5 fw-bold">{{ $plan->nombre }}</h2>
                            <span class="badge {{ $plan->estado ? 'text-bg-success' : 'text-bg-secondary' }} align-self-start">
                                {{ $plan->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <p class="price">
                            ${{ number_format($plan->precio_mensual, 2) }}
                            <small class="text-body-secondary fs-6">/mes</small>
                        </p>
                        <p>{{ $plan->descripcion }}</p>
                        <ul class="text-body-secondary">
                            <li>{{ number_format($plan->porcentaje_comision, 2) }}% de comisión</li>
                            <li>{{ $plan->limite_productos }} productos activos</li>
                            <li>{{ $plan->suscripciones_count }} suscripciones</li>
                        </ul>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center gap-2">
                        <a href="{{ route('admin.planes.edit', $plan) }}">Editar plan</a>
                        <form method="POST" action="{{ route('admin.planes.status', $plan) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="estado" value="{{ $plan->estado ? 0 : 1 }}">
                            <button class="btn btn-sm {{ $plan->estado ? 'btn-outline-danger' : 'btn-outline-primary' }}">
                                {{ $plan->estado ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 empty-state">No hay planes.</div>
        @endforelse
    </div>
@endsection
