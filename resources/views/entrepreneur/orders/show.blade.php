@extends('layouts.adminlte')

@section('title', 'Pedido #'.$pedido->id)

@section('content_header')
    <h1 class="fw-bold">Pedido #{{ $pedido->id }}</h1>
@endsection

@section('content')
    <div class="orders-surface">
        @include('components.flash')

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between gap-3">
                            <div>
                                <h2 class="h5">Cliente</h2>
                                <p>{{ $pedido->nombre_cliente }}<br>{{ $pedido->telefono_cliente }}<br>{{ $pedido->correo_cliente }}</p>
                            </div>
                            <span class="badge status-badge align-self-start">{{ $pedido->estado_actual->value }}</span>
                        </div>
                        <hr>
                        <h2 class="h5">Productos</h2>
                        @foreach($pedido->detallePedidos as $detalle)
                            <div class="d-flex justify-content-between py-2">
                                <span>{{ $detalle->cantidad }}× {{ $detalle->producto->nombre }}</span>
                                <strong>${{ number_format($detalle->subtotal, 2) }}</strong>
                            </div>
                        @endforeach
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total</strong>
                            <strong class="price">${{ number_format($pedido->subtotal, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        @if($pedido->estado_actual === \App\Enums\OrderStatus::ENTREGADO)
                            <h2 class="h5">Pedido completado</h2>
                            <p class="text-body-secondary mb-0">El pedido ha sido completado.</p>
                        @elseif($pedido->estado_actual === \App\Enums\OrderStatus::CANCELADO)
                            <h2 class="h5">Pedido cancelado</h2>
                            <p class="text-body-secondary mb-0">El pedido ha sido cancelado.</p>
                        @else
                            <h2 class="h5">Actualizar estado</h2>
                            <form method="POST" action="{{ route('entrepreneur.orders.status', $pedido) }}">
                                @csrf
                                @method('PATCH')
                                <select class="form-select mb-3" name="estado">
                                    @foreach($transiciones as $estado)
                                        <option value="{{ $estado->value }}">{{ $estado->value }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary w-100">Guardar estado</button>
                            </form>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
