<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\RedirectResponse;

class ProductLimitReachedException extends Exception
{
    public function render(): RedirectResponse
    {
        return back()->withInput()->withErrors(['estado' => $this->getMessage()]);
    }
}
