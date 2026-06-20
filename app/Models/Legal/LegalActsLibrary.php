<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalActsLibrary extends LegalBaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'legal_acts_library';

    protected $fillable = [
        'client_id', 'titre', 'categorie', 'type_societe',
        'contenu', 'variables', 'droit_applicable',
        'version', 'is_public', 'is_validated',
        'validated_by', 'tags', 'created_by',
    ];

    protected $casts = [
        'variables' => 'json',
        'tags' => 'json',
        'is_public' => 'boolean',
        'is_validated' => 'boolean',
    ];
}
