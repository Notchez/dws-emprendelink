<?php

namespace App\Services\Emprendedor;

use App\Models\Emprendimiento;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class BusinessProfileService
{
    public function update(Emprendimiento $business, array $data, ?UploadedFile $logo = null): Emprendimiento
    {
        $data = Arr::only($data, ['descripcion', 'telefono_contacto']);

        $previousLogo = $business->logo_url;
        $newLogo = $logo ? Storage::disk('public')->putFile('emprendimientos', $logo) : null;

        if ($logo && ! $newLogo) {
            throw new RuntimeException('No se pudo guardar el logo del emprendimiento.');
        }

        if ($newLogo) {
            $data['logo_url'] = $newLogo;
        }

        try {
            $business->update($data);
        } catch (Throwable $exception) {
            if ($newLogo) {
                Storage::disk('public')->delete($newLogo);
            }

            throw $exception;
        }

        if ($newLogo && $previousLogo && $previousLogo !== $newLogo) {
            Storage::disk('public')->delete($previousLogo);
        }

        return $business->refresh();
    }
}
