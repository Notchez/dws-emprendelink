@extends('layouts.adminlte')

@section('title', 'Inicio')

@section('content_header')
    <h1 class="fw-bold mb-2">Bienvenido a EmprendeLink</h1>
    <p class="text-body-secondary mb-0">
        Tu negocio, catálogo y pedidos en un solo lugar.
    </p>
@endsection

@section('content')
    <div class="alert surface-soft border mb-4">
        <h2 class="h3 fw-semibold">Vista previa de la plataforma</h2>
        <p class="mb-0">
            Los módulos están en preparación. Esta pantalla muestra la base visual
            y todavía no permite realizar operaciones.
        </p>
    </div>

    <div class="row g-3">
        @foreach ([
            ['shop', 'Mi negocio', 'Presenta tu emprendimiento y sus datos de contacto.'],
            ['box-seam', 'Catálogo', 'Organiza tus categorías y productos.'],
            ['receipt', 'Pedidos', 'Consulta pedidos y da seguimiento a sus estados.'],
            ['percent', 'Comisiones', 'Consulta las comisiones asociadas a tus pedidos.'],
        ] as [$icon, $heading, $description])
            <div class="col-12 col-md-6 col-xl-3">
                <section class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="surface-soft text-primary rounded p-3 d-inline-flex mb-3">
                            <i class="bi bi-{{ $icon }} fs-4" aria-hidden="true"></i>
                        </div>

                        <h2 class="h3 fw-semibold">{{ $heading }}</h2>
                        <p class="text-body-secondary mb-0">{{ $description }}</p>
                    </div>

                    <div class="card-footer bg-transparent">
                        <span class="small text-body-secondary">En preparación</span>
                    </div>
                </section>
            </div>
        @endforeach
    </div>
@endsection