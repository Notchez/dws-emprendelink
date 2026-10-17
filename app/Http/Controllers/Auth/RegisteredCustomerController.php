<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCustomerRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredCustomerController extends Controller
{
    public function create(): View { return view('auth.register'); }

    public function store(RegisterCustomerRequest $request): RedirectResponse
    {
        $user = User::create($request->safe()->merge(['rol' => UserRole::CLIENTE, 'estado' => true])->all());
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('catalog.index')->with('success', 'Tu cuenta fue creada correctamente.');
    }
}
