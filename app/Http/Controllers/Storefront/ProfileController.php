<?php
namespace App\Http\Controllers\Storefront;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class ProfileController extends Controller
{
    public function edit(): View { return view('storefront.profile',['user'=>request()->user()]); }
    public function update(UpdateProfileRequest $request): RedirectResponse { $request->user()->update($request->validated()); return back()->with('success','Perfil actualizado.'); }
}
