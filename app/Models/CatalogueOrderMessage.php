<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogueOrderMessage extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'lu' => 'boolean',
        'date_lecture' => 'datetime',
    ];

    public function commande()
    {
        return $this->belongsTo(CatalogueOrder::class, 'commande_id');
    }

    public function expediteur()
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }
}
