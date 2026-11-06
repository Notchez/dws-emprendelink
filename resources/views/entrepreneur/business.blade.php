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
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="5" maxlength="2000" data-character-counter="#business-description-count">{{ old('descripcion', $emprendimiento->descripcion) }}</textarea>
                        @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="d-flex justify-content-between mt-1">
                            <small class="form-text">Describe tu tienda y su propuesta de valor.</small>
                            <small class="text-body-secondary" id="business-description-count" aria-live="polite">0 / 2000</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="telefono_contacto">Teléfono de contacto</label>
                        <input type="tel" class="form-control @error('telefono_contacto') is-invalid @enderror" id="telefono_contacto" name="telefono_contacto" value="{{ old('telefono_contacto', $emprendimiento->telefono_contacto) }}" maxlength="20" autocomplete="tel" placeholder="+503 2291 1163">
                        @error('telefono_contacto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Incluye el código de país cuando corresponda. Máximo 20 caracteres.</div>
                    </div>

                    <div class="mb-4">
                        <span class="form-label d-block">Logo</span>
                        <input class="visually-hidden" id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" data-image-preview-target="#business-logo-preview" data-file-name-target="#business-logo-file-name">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <label class="btn btn-outline-primary mb-0" for="logo">Seleccionar imagen</label>
                            <span id="business-logo-file-name" class="text-body-secondary" aria-live="polite">Ningún archivo seleccionado</span>
                        </div>
                        @error('logo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <div class="form-text">JPG, PNG o WebP. Máximo 2 MB.</div>
                        <img id="business-logo-preview" class="image-upload-preview mt-3" src="{{ $emprendimiento->logo_url ? asset('storage/'.$emprendimiento->logo_url) : '' }}" alt="Vista previa del logo de {{ $emprendimiento->nombre }}" @if(! $emprendimiento->logo_url) hidden @endif>
                    </div>

                    <button class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>

        <aside class="col-lg-4">
            <div class="card"><div class="card-body">
                <h2 class="h5 fw-bold">Plan actual</h2>
                <p class="price">{{ $emprendimiento->suscripcionActiva?->plan?->nombre ?? 'Sin plan' }}</p>
                <p class="text-body-secondary">Límite: {{ $emprendimiento->suscripcionActiva?->plan?->limite_productos ?? 0 }} productos activos.</p>
                <a href="{{ route('catalog.business', ['emprendimiento' => $emprendimiento->slug]) }}" class="d-block mb-3">Ver mi catálogo público</a>
                <hr>
                <label for="store-url" class="form-label fw-bold">Compartir tienda</label>
                <div class="input-group">
                    <input type="text" id="store-url" readonly value="{{ route('catalog.business', ['emprendimiento' => $emprendimiento->slug]) }}" class="form-control form-control-sm bg-light">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-copy-target="#store-url" data-copy-label="#copy-text" title="Copiar enlace"><i class="bi bi-copy" aria-hidden="true"></i> <span id="copy-text">Copiar</span></button>
                </div>
            </div></div>
        </aside>
    </div>
@endsection
