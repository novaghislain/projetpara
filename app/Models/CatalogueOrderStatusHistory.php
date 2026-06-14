<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogueOrderStatusHistory extends Model
{
    //
    protected $guarded = [];

    public function commande()
    {
        return $this->belongsTo(CatalogueOrder::class, 'commande_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
