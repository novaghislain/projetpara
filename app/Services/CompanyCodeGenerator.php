<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\Company;

class CompanyCodeGenerator
{
    /**
     * Generate a unique, human‑readable code like ENT‑A1B2C3.
     */
    public static function generate(): string
    {
        do {
            $code = 'ENT-' . Str::upper(Str::random(6));
        } while (Company::where('client_code', $code)->exists());

        return $code;
    }
}
?>
