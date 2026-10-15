@extends('layouts.adminlte')

@section('content')
<div class="container-fluid pt-3">
    <div class="row mb-3">
        <div class="col-sm-12">
            <h1 class="m-0 text-dark font-weight-bold">Gestión de Usuarios</h1>
            <p class="text-muted">Administra los usuarios y cuentas registradas en la plataforma.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <!-- Buscador -->
            <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o correo electrónico..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Buscar</button>
                    </div>
                </div>
            </form>

            <!-- Tabla de Usuarios -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted">
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo Electrónico</th>
                            <th>Fecha de Registro</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td class="font-weight-bold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-outline-primary px-3">Ver Detalle</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No se encontraron usuarios registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-end mt-3">
                {{ $usuarios->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection