
@extends('layouts.adminlte')

@section('title', 'Mi emprendimiento')

@section('content_header')
    <h1>Mi emprendimiento</h1>
    <p class="text-muted">Consulta y actualiza la información de tu negocio.</p>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            Revisa los campos del formulario antes de continuar.
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Información del emprendimiento</h2>
        </div>

        <div class="card-body">
            <form action="{{ route('entrepreneur.business.update', $emprendimiento) }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre del emprendimiento</label>
                    <input type="text"
                           id="nombre"
                           name="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $emprendimiento->nombre) }}"
                           maxlength="150"
                           required>

                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea id="descripcion"
                              name="descripcion"
                              rows="4"
                              class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $emprendimiento->descripcion) }}</textarea>

                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="telefono_contacto" class="form-label">Teléfono de contacto</label>
                    <input type="text"
                           id="telefono_contacto"
                           name="telefono_contacto"
                           class="form-control @error('telefono_contacto') is-invalid @enderror"
                           value="{{ old('telefono_contacto', $emprendimiento->telefono_contacto) }}"
                           maxlength="20">

                    @error('telefono_contacto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="logo" class="form-label">Logo del emprendimiento</label>

                    @if ($emprendimiento->logo_url)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $emprendimiento->logo_url) }}"
                                 alt="Logo de {{ $emprendimiento->nombre }}"
                                 style="max-width: 150px; max-height: 150px; object-fit: contain;">
                        </div>
                    @endif

                    <input type="file"
                           id="logo"
                           name="logo"
                           accept=".jpg,.jpeg,.png,.webp"
                           class="form-control @error('logo') is-invalid @enderror">

                    <div class="form-text">
                        Formatos permitidos: JPG, PNG y WEBP. Tamaño máximo: 2 MB.
                    </div>

                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    Guardar cambios
                </button>
            </form>
        </div>
    </div>
@endsection
