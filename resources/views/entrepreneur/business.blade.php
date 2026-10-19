@extends('layouts.adminlte')

@section('title', 'Mi negocio')

@section('content_header')
    <h1 class="fw-bold">Mi negocio</h1>
@endsection

@section('content')
    @include('components.flash')

    <div class="row g-4">
        <div class="col-lg-8">
            <form class="card" method="POST" enctype="multipart/form-data" action="{{ route('entrepreneur.business.update', $emprendimiento) }}">
                <div class="card-body p-4">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input class="form-control" id="nombre" value="{{ $emprendimiento->nombre }}" readonly aria-readonly="true">
                        <div class="form-text">El nombre comercial es administrado por EmprendeLink.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="descripcion">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4">{{ old('descripcion', $emprendimiento->descripcion) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="telefono_contacto">Teléfono</label>
                        <input class="form-control" id="telefono_contacto" name="telefono_contacto" value="{{ old('telefono_contacto', $emprendimiento->telefono_contacto) }}">
                    </div>

                    <div class="mb-4">
                        <span class="form-label d-block">Logo</span>
                        <input
                            class="visually-hidden"
                            id="logo"
                            name="logo"
                            type="file"
                            accept="image/png,image/jpeg,image/webp"
                            data-image-preview-target="#business-logo-preview"
                            data-file-name-target="#business-logo-file-name"
                        >
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <label class="btn btn-outline-primary mb-0" for="logo">Seleccionar imagen</label>
                            <span id="business-logo-file-name" class="text-body-secondary" aria-live="polite">Ningún archivo seleccionado</span>
                        </div>
                        <div class="form-text">JPG, PNG o WebP. Máximo 2 MB.</div>
                        <img
                            id="business-logo-preview"
                            class="image-upload-preview mt-3"
                            src="{{ $emprendimiento->logo_url ? asset('storage/'.$emprendimiento->logo_url) : '' }}"
                            alt="Vista previa del logo de {{ $emprendimiento->nombre }}"
                            @if(! $emprendimiento->logo_url) hidden @endif
                        >
                    </div>

                    <button class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>

        <aside class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 fw-bold">Plan actual</h2>
                    <p class="price">{{ $emprendimiento->suscripcionActiva?->plan?->nombre ?? 'Sin plan' }}</p>
                    <p class="text-body-secondary">Límite: {{ $emprendimiento->suscripcionActiva?->plan?->limite_productos ?? 0 }} productos activos.</p>
                    <a href="{{ route('catalog.business', ['emprendimiento' => $emprendimiento->slug]) }}">Ver mi catálogo público</a>
                </div>
            </div>
        </aside>
    </div>
@endsection
