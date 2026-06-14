<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogueCategory extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'actif' => 'boolean',
        'ordre' => 'integer',
    ];

    public function services()
    {
        return $this->hasMany(CatalogueService::class, 'category_id');
    }
}
