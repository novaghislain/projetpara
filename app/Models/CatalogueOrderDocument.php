<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogueOrderDocument extends Model
{
    //
    protected $guarded = [];

    public function commande()
    {
        return $this->belongsTo(CatalogueOrder::class, 'commande_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
