<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Listar todos los usuarios del sistema
    public function index(Request $request)
    {
        $query = User::query();

        // Opcional: filtrar por nombre o correo si se usa el buscador
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $usuarios = $query->latest()->paginate(10);

        return view('admin.users.index', compact('usuarios'));
    }

    // Ver detalles de un usuario específico
    public function show($id)
    {
        $usuario = User::with(['emprendimiento'])->findOrFail($id);

        return view('admin.users.show', compact('usuario'));
    }
}
