@extends('layouts.adminlte')

@section('content')
<div class="container-fluid pt-3">
    <div class="row mb-3">
        <div class="col-sm-12">
            <h1 class="m-0 text-dark font-weight-bold">Detalle del Pedido #{{ $pedido->id }}</h1>
            <p class="text-muted">Información completa de la transacción y productos solicitados.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="max-width: 900px; margin: 0 auto;">
        <div class="card-body p-4">
            <h5 class="font-weight-bold mb-3 text-primary">Información del Cliente y Envío</h5>
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Emprendimiento:</div>
                <div class="col-md-8 font-weight-bold">{{ $pedido->emprendimiento->nombre ?? 'N/A' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Nombre del Cliente:</div>
                <div class="col-md-8 font-weight-bold">{{ $pedido->nombre_cliente }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Teléfono:</div>
                <div class="col-md-8 font-weight-bold">{{ $pedido->telefono_cliente }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Modalidad de Entrega:</div>
                <div class="col-md-8 font-weight-bold">{{ ucfirst($pedido->modalidad_entrega) }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 text-muted">Dirección de Entrega:</div>
                <div class="col-md-8">{{ $pedido->direccion_entrega ?? 'N/A (Retiro en tienda)' }}</div>
            </div>
            <div class="row mb-4">
                <div class="col-md-4 text-muted">Estado Actual:</div>
                <div class="col-md-8 font-weight-bold">
                    <span class="badge bg-success px-3 py-1">{{ $pedido->estado_actual }}</span>
                </div>
            </div>

            <hr>

            <h5 class="font-weight-bold mb-3 text-primary">Productos Solicitados</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio Unitario</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedido->detallePedidos as $detalle)
                        <tr>
                            <td>{{ $detalle->producto->nombre ?? 'Producto no disponible' }}</td>
                            <td class="text-center">{{ $detalle->cantidad }}</td>
                            <td class="text-end">${{ number_format($detalle->precio_unitario, 2) }}</td>
                            <td class="text-end font-weight-bold">${{ number_format($detalle->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end font-weight-bold">Subtotal General:</td>
                            <td class="text-end font-weight-bold">${{ number_format($pedido->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end font-weight-bold text-primary">Comisión de Plataforma:</td>
                            <td class="text-end font-weight-bold text-primary">${{ number_format($pedido->comision_plataforma ?? 0, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('admin.pedidos.index') }}" class="btn btn-secondary px-4">Volver al Listado</a>
            </div>
        </div>
    </div>
</div>
@endsection