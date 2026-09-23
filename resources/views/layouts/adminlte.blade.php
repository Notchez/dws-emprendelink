<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inicio') | EmprendeLink</title>

    @vite(['resources/css/adminlte.css', 'resources/js/adminlte.js'])

    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg">
    <a href="#main-content"
       class="visually-hidden-focusable position-absolute top-0 start-0 z-3 p-3 bg-white">
        Saltar al contenido
    </a>

    <div class="app-wrapper">
        @include('partials.adminlte-header')
        @include('partials.adminlte-sidebar')

        <main id="main-content" class="app-main" tabindex="-1">
            <div class="app-content-header">
                <div class="container-fluid">
                    @yield('content_header')
                </div>
            </div>

            <div class="app-content pb-4">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </main>

        @include('partials.adminlte-footer')
    </div>

    @stack('scripts')
</body>
</html>