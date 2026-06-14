<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogueService extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'inclus_json' => 'array',
        'documents_requis_json' => 'array',
        'champs_formulaire_json' => 'array',
        'tarif_fcfa' => 'decimal:2',
        'actif' => 'boolean',
        'ordre_affichage' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(CatalogueCategory::class, 'category_id');
    }
}
