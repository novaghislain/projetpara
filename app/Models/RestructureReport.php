<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rapport de restructuration de l'espace documentaire (avant/après).
 * proposed → approved (validation humaine) → executed.
 */
class RestructureReport extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'status',
        'payload',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}