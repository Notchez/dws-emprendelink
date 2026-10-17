<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::ADMIN) === true;
    }

    public function rules(): array
    {
        return ['estado' => ['required', 'boolean']];
    }
}
