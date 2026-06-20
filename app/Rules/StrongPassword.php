<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Strong Password Rule — Exigences de sécurité minimales.
 *
 * - Minimum 10 caractères
 * - Au moins 1 lettre majuscule
 * - Au moins 1 chiffre
 * - Au moins 1 caractère spécial
 */
class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) < 10) {
            $fail('Le mot de passe doit faire au moins 10 caractères.');
            return;
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $fail('Le mot de passe doit contenir au moins une lettre majuscule.');
            return;
        }

        if (!preg_match('/[0-9]/', $value)) {
            $fail('Le mot de passe doit contenir au moins un chiffre.');
            return;
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $value)) {
            $fail('Le mot de passe doit contenir au moins un caractère spécial.');
            return;
        }
    }
}
