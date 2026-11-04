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
                        <textarea class="form-control" 
                                  id="descripcion" 
                                  name="descripcion" 
                                  rows="4" 
                                  maxlength="255" 
                                  oninput="updateCharCount(this)"
                                  style="resize: none;">{{ old('descripcion', $emprendimiento->descripcion) }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <div class="form-text">Breve descripción de tu tienda (máximo 255 caracteres).</div>
                            <small class="text-muted" id="char-count">0 / 255</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="telefono_contacto">Teléfono de contacto</label>
                        <div class="input-group">
                            <select class="form-select" id="codigo_area" name="codigo_area" style="max-width: 130px;">
                                <option value="+503" @selected(old('codigo_area', $emprendimiento->codigo_area ?? '+503') == '+503')>🇸🇻 +503</option>
                            </select>
                            <input type="text" 
                                   class="form-control" 
                                   id="telefono_contacto" 
                                   name="telefono_contacto" 
                                   value="{{ old('telefono_contacto', $emprendimiento->telefono_contacto) }}" 
                                   placeholder="22911163" 
                                   maxlength="8" 
                                   oninput="formatPhone(this)">
                        </div>
                        <div class="form-text">Ingresa los 8 dígitos del número (ej. 22911163).</div>
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
                    <a href="{{ route('catalog.business', ['emprendimiento' => $emprendimiento->slug]) }}" class="d-block mb-3">Ver mi catálogo público</a>
                    
                    <hr>

                    <div class="mt-3">
                        <label for="store-url" class="form-label fw-bold text-sm">Compartir tienda</label>
                        <div class="input-group">
                            <input type="text" 
                                   id="store-url" 
                                   readonly 
                                   value="{{ route('catalog.business', ['emprendimiento' => $emprendimiento->slug]) }}" 
                                   class="form-control form-control-sm text-muted bg-light">
                            <button class="btn btn-outline-secondary btn-sm" type="button" id="copy-btn" onclick="copyStoreUrl()" title="Copiar enlace">
                                <i class="fas fa-copy"></i> <span id="copy-text">Copiar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
    <script>
    function copyStoreUrl() {
        const input = document.getElementById('store-url');
        const copyBtn = document.getElementById('copy-btn');
        const copyText = document.getElementById('copy-text');

        input.select();
        input.setSelectionRange(0, 99999); //

        navigator.clipboard.writeText(input.value).then(() => {
            copyBtn.classList.remove('btn-outline-secondary');
            copyBtn.classList.add('btn-success');
            copyText.textContent = '¡Copiado!';
            setTimeout(() => {
                copyBtn.classList.remove('btn-success');
                copyBtn.classList.add('btn-outline-secondary');
                copyText.textContent = 'Copiar';
            }, 2000);
        }).catch(err => {
            console.error('Error al copiar: ', err);
        });
    }
    </script>
@endsection