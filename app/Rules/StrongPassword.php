<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Strong Password Rule — Exigences de sécurité renforcées.
 *
 * - Minimum 12 caractères
 * - Au moins 1 lettre majuscule
 * - Au moins 1 lettre minuscule
 * - Au moins 1 chiffre
 * - Au moins 1 caractère spécial
 * - Pas de répétitions (aaa, 111, etc.)
 * - Pas de mots de passe communs
 */
class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $errors = [];

        if (strlen($value) < 12) {
            $errors[] = '12 caractères minimum';
        }
        if (!preg_match('/[A-Z]/', $value)) {
            $errors[] = 'une majuscule';
        }
        if (!preg_match('/[a-z]/', $value)) {
            $errors[] = 'une minuscule';
        }
        if (!preg_match('/[0-9]/', $value)) {
            $errors[] = 'un chiffre';
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $value)) {
            $errors[] = 'un caractère spécial (@, #, $, %, etc.)';
        }
        if (preg_match('/(.)\1{2,}/', $value)) {
            $errors[] = 'pas de répétitions (aaa, 111, etc.)';
        }
        if (preg_match('/^(1234|password|motdepasse|admin|qwerty)/i', $value)) {
            $errors[] = 'pas de mot de passe commun';
        }

        if (!empty($errors)) {
            $fail('Le mot de passe doit contenir : ' . implode(', ', $errors));
        }
    }
}
