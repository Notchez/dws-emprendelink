<?php
namespace App\Http\Requests\Emprendedor;
use Illuminate\Foundation\Http\FormRequest;
class UpdateProductStatusRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update',$this->route('producto')) === true; }
    public function rules(): array { return ['estado'=>['required','boolean']]; }
}
