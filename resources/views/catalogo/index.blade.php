<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emprendimiento->nombre }} | Catálogo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

    <header class="bg-white border-bottom py-4 mb-4">
        <div class="container text-center">

            @if ($emprendimiento->logo_url)
                <img src="{{ asset('storage/' . $emprendimiento->logo_url) }}"
                     alt="Logo de {{ $emprendimiento->nombre }}"
                     style="width: 100px; height: 100px; object-fit: contain;"
                     class="mb-3">
            @endif

            <h1>{{ $emprendimiento->nombre }}</h1>

            <p class="text-muted">
                {{ $emprendimiento->descripcion }}
            </p>
        </div>
    </header>

    <main class="container pb-5">

        <h2 class="mb-4">Nuestro catálogo</h2>

        <form action="{{ route('catalog.index', $emprendimiento->slug) }}"
              method="GET"
              class="row g-3 mb-4">

            <div class="col-md-6">
                <input type="search"
                       name="buscar"
                       class="form-control"
                       placeholder="Buscar productos..."
                       value="{{ request('buscar') }}">
            </div>

            <div class="col-md-4">
                <select name="categoria" class="form-select">
                    <option value="">Todas las categorías</option>

                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}"
                            @selected(request('categoria') == $categoria->id)>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit"
                        class="btn btn-primary w-100">
                    Buscar
                </button>
            </div>
        </form>

        <div class="row g-4">

            @forelse ($productos as $producto)
                <div class="col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm">

                        @if ($producto->imagen_url)
                            <img src="{{ asset('storage/' . $producto->imagen_url) }}"
                                 class="card-img-top"
                                 alt="{{ $producto->nombre }}"
                                 style="height: 200px; object-fit: cover;">
                        @else
                            <div class="bg-secondary-subtle d-flex align-items-center justify-content-center"
                                 style="height: 200px;">
                                <span class="text-muted">
                                    Sin fotografía
                                </span>
                            </div>
                        @endif

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                {{ $producto->nombre }}
                            </h5>

                            <p class="card-text text-muted">
                                {{ \Illuminate\Support\Str::limit($producto->descripcion, 90) }}
                            </p>

                            <p class="fw-bold fs-5 mt-auto">
                                ${{ number_format($producto->precio, 2) }}
                            </p>

                            <a href="{{ route('catalog.show', [$emprendimiento->slug, $producto]) }}"
                               class="btn btn-outline-primary">
                                Ver detalles
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        No se encontraron productos disponibles.
                    </div>
                </div>
            @endforelse

        </div>

        <div class="mt-4">
            {{ $productos->links() }}
        </div>

    </main>

    <footer class="bg-white border-top py-3 text-center">
        <small class="text-muted">
            Catálogo de {{ $emprendimiento->nombre }} — EmprendeLink
        </small>
    </footer>

</body>
</html>