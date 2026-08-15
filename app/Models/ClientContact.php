<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Modèle représentant un contact d'une entreprise cliente.
 *
 * @table client_contacts
 */
class ClientContact extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'client_id',
        'user_id',
        'name',
        'position',
        'phone',
        'email',
        'is_primary',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

