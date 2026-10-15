@extends('layouts.adminlte')

@section('content')
<div class="container-fluid pt-3">
    <div class="row mb-3">
        <div class="col-sm-12">
            <h1 class="m-0 text-dark font-weight-bold">Gestión de Planes</h1>
            <p class="text-muted">Administra los precios, comisiones y límites de productos de cada plan (RF-09, RF-10).</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted">
                            <th>Plan</th>
                            <th>Descripción</th>
                            <th>Precio Mensual</th>
                            <th>Comisión (%)</th>
                            <th>Límite Productos</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($planes as $plan)
                        <tr>
                            <td class="font-weight-bold">{{ $plan->nombre }}</td>
                            <td>{{ $plan->descripcion ?? 'N/A' }}</td>
                            <td>${{ number_format($plan->precio_mensual, 2) }}</td>
                            <td>{{ $plan->porcentaje_comision }}%</td>
                            <td>{{ $plan->limite_productos == 0 ? 'Ilimitados' : $plan->limite_productos }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.planes.edit', $plan->id) }}" class="btn btn-sm btn-outline-primary px-3">Editar</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection