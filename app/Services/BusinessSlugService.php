<?php

namespace App\Services;

use App\Models\Emprendimiento;
use Illuminate\Support\Str;

class BusinessSlugService
{
    private const MAX_LENGTH = 100;

    public function generate(string $name, int $stableId, ?int $ignoreBusinessId = null): string
    {
        $base = Str::slug($name) ?: 'emprendimiento';
        $attempt = 1;

        do {
            $suffix = '-'.$stableId.($attempt > 1 ? '-'.$attempt : '');
            $slug = rtrim(substr($base, 0, self::MAX_LENGTH - strlen($suffix)), '-').$suffix;
            $attempt++;
        } while ($this->exists($slug, $ignoreBusinessId));

        return $slug;
    }

    private function exists(string $slug, ?int $ignoreBusinessId): bool
    {
        return Emprendimiento::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreBusinessId, fn ($query) => $query->whereKeyNot($ignoreBusinessId))
            ->exists();
    }
}
