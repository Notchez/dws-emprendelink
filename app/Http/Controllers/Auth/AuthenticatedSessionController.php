<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View { return view('auth.login'); }

    public function store(LoginRequest $request): RedirectResponse
    {
        $key = Str::lower($request->string('email')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Demasiados intentos. Espera '.RateLimiter::availableIn($key).' segundos.']);
        }

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'Las credenciales proporcionadas no son válidas.']);
        }

        if (! $request->user()->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'Tu cuenta está suspendida.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(match ($request->user()->rol) {
            UserRole::ADMIN => route('admin.dashboard'),
            UserRole::EMPRENDEDOR => route('entrepreneur.dashboard'),
            UserRole::CLIENTE => route('catalog.index'),
        });
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('catalog.index')->with('success', 'Sesión cerrada correctamente.');
    }
}
