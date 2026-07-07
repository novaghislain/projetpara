<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class EncryptedCast implements CastsAttributes
{
    /**
     * Déchiffre automatiquement à la lecture.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Chiffre automatiquement à l'écriture.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }
        return encrypt($value);
    }
}
