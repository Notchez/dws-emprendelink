<?php

namespace App\Services\Emprendedor;

use App\Models\Emprendimiento;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BusinessProfileService
{
    public function actualizar(
        Emprendimiento $emprendimiento,
        array $datos,
        ?UploadedFile $logo = null
    ): Emprendimiento {
        $rutaAnterior = $emprendimiento->logo_url;
        $rutaNueva = null;

        if ($logo !== null) {
            $rutaNueva = $logo->store('emprendimientos/logos', 'public');
            $datos['logo_url'] = $rutaNueva;
        }

        try {
            $emprendimiento->update($datos);
        } catch (\Throwable $error) {
            if ($rutaNueva !== null) {
                Storage::disk('public')->delete($rutaNueva);
            }

            throw $error;
        }

        if ($rutaNueva !== null && $rutaAnterior !== null) {
            Storage::disk('public')->delete($rutaAnterior);
        }

        return $emprendimiento->refresh();
    }
}
