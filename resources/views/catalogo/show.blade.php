<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $producto->nombre }} | {{ $emprendimiento->nombre }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

    <header class="bg-white border-bottom py-4 mb-4">
        <div class="container">
            <h1 class="h3">{{ $emprendimiento->nombre }}</h1>

            <a href="{{ route('catalog.index', $emprendimiento->slug) }}"
               class="btn btn-outline-primary btn-sm mt-2">
                Volver al catálogo
            </a>
        </div>
    </header>

    <main class="container pb-5">
        <div class="card shadow-sm">
            <div class="row g-0">

                <div class="col-md-6">
                    @if ($producto->imagen_url)
                        <img src="{{ asset('storage/' . $producto->imagen_url) }}"
                             alt="{{ $producto->nombre }}"
                             class="img-fluid rounded-start w-100"
                             style="max-height: 450px; object-fit: contain;">
                    @else
                        <div class="bg-secondary-subtle d-flex align-items-center justify-content-center"
                             style="min-height: 350px;">
                            <span class="text-muted">
                                Sin fotografía disponible
                            </span>
                        </div>
                    @endif
                </div>

                <div class="col-md-6">
                    <div class="card-body p-4">

                        <h2 class="card-title mb-3">
                            {{ $producto->nombre }}
                        </h2>

                        <p class="text-muted">
                            Categoría:
                            {{ $producto->categoria?->nombre ?? 'Sin categoría' }}
                        </p>

                        <h3 class="text-primary mb-4">
                            ${{ number_format($producto->precio, 2) }}
                        </h3>

                        <h5>Descripción</h5>

                        <p class="card-text">
                            {{ $producto->descripcion ?: 'Este producto no tiene descripción.' }}
                        </p>

                        <hr>

                        <p class="text-muted">
                            Ofrecido por:
                            <strong>{{ $emprendimiento->nombre }}</strong>
                        </p>

                        @if ($emprendimiento->telefono_contacto)
                            <p>
                                Teléfono de contacto:
                                {{ $emprendimiento->telefono_contacto }}
                            </p>
                        @endif

                        <a href="{{ route('catalog.index', $emprendimiento->slug) }}"
                           class="btn btn-primary mt-3">
                            Ver más productos
                        </a>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <footer class="bg-white border-top py-3 text-center">
        <small class="text-muted">
            {{ $emprendimiento->nombre }} — EmprendeLink
        </small>
    </footer>

</body>
</html>