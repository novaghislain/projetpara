<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LegalRegistre extends LegalBaseModel
{
    use HasFactory;

    protected $table = 'legal_registres';

    protected $fillable = [
        'client_id', 'type', 'annee',
        'entrees', 'is_closed', 'closed_at',
    ];

    protected $casts = [
        'entrees' => 'json',
        'is_closed' => 'boolean',
        'closed_at' => 'date',
    ];
}
