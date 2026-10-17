<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\RedirectResponse;

class InvalidOrderTransitionException extends Exception
{
    public function render(): RedirectResponse
    {
        return back()->withErrors(['estado' => $this->getMessage()]);
    }
}
