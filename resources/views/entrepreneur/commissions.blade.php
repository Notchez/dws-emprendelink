@extends('layouts.adminlte')

@section('title', 'Cuenta mensual')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="fw-bold">Cuenta mensual</h1>
            <p class="text-body-secondary mb-0">
                Resumen de los cargos generados por pedidos entregados.
            </p>
        </div>

        <form method="GET" class="d-flex align-items-end gap-2">
            <div>
                <label class="form-label" for="mes">Período</label>
                <input
                    class="form-control @error('mes') is-invalid @enderror"
                    id="mes"
                    name="mes"
                    type="month"
                    value="{{ $mes }}"
                    required
                >
                @error('mes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button class="btn btn-primary">Consultar</button>
        </form>
    </div>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card orders-surface">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                        <div>
                            <p class="text-body-secondary mb-1">Período</p>
                            <h2 class="h4 mb-0">{{ $periodStart->format('m/Y') }}</h2>
                        </div>
                        <div class="text-md-end">
                            <p class="text-body-secondary mb-1">Estado</p>
                            @if(! $periodClosed)
                                <span class="badge text-bg-info">Mes en curso</span>
                            @elseif($statement?->estado_pago === \App\Enums\AccountStatementStatus::PAGADO)
                                <span class="badge text-bg-success">Pagado</span>
                            @elseif($commissionCount > 0)
                                <span class="badge text-bg-warning">Pendiente de pago</span>
                            @else
                                <span class="badge text-bg-secondary">Sin cargos</span>
                            @endif
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <p class="text-body-secondary mb-1">Total de cargos</p>
                                <strong class="price">
                                    ${{ number_format($statement?->total_adeudado ?? 0, 2) }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <p class="text-body-secondary mb-1">Pedidos entregados</p>
                                <strong class="price">{{ $commissionCount }}</strong>
                            </div>
                        </div>
                    </div>

                    @if($statement?->fecha_pago)
                        <hr>
                        <p class="mb-0">
                            <span class="text-body-secondary">Fecha de pago:</span>
                            {{ $statement->fecha_pago->format('d/m/Y H:i') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
