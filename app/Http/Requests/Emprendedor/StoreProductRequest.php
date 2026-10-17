<?php
namespace App\Http\Requests\Emprendedor;
use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', Producto::class) === true; }
    public function rules(): array { $business=$this->user()->emprendimiento; return ['categoria_id'=>['nullable',Rule::exists('categorias','id')->where('emprendimiento_id',$business?->id)->where('estado',true)],'nombre'=>['required','string','max:150'],'descripcion'=>['nullable','string','max:2000'],'precio'=>['required','numeric','min:0.01','max:999999.99'],'imagen'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],'estado'=>['sometimes','boolean']]; }
}
