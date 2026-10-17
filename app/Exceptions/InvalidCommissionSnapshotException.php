<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\RedirectResponse;

class InvalidCommissionSnapshotException extends Exception
{
    public function render(): RedirectResponse
    {
        return back()->withErrors(['estado' => $this->getMessage()]);
    }
}
