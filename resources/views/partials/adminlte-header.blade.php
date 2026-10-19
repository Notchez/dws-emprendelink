<nav class="app-header navbar navbar-expand border-bottom" aria-label="Barra superior">
    <div class="container-fluid">
        <button type="button" class="btn btn-link text-body" data-lte-toggle="sidebar" aria-controls="main-sidebar" aria-label="Mostrar u ocultar menú"><i class="bi bi-list fs-4"></i></button>
        <span class="fw-semibold ms-2">Emprende<span class="text-primary">Link</span></span>
        <span class="ms-auto text-body-secondary me-3 d-none d-sm-inline">{{ auth()->user()->name }}</span>
        <form action="{{ route('logout') }}" method="POST">@csrf<button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-box-arrow-right me-1"></i>Salir</button></form>
    </div>
</nav>
