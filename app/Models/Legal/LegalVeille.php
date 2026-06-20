<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LegalVeille extends LegalBaseModel
{
    use HasFactory;

    protected $table = 'legal_veille';

    protected $fillable = [
        'client_id', 'titre', 'description', 'source',
        'categorie', 'date_publication', 'url',
        'impact', 'tags', 'est_lu', 'created_by',
    ];

    protected $casts = [
        'tags' => 'json',
        'date_publication' => 'date',
        'est_lu' => 'boolean',
    ];
}
