<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class CatalogueOrder extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'date_commande' => 'datetime',
        'date_livraison' => 'datetime',
        'montant_estime_fcfa' => 'decimal:2',
        'form_data' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(CatalogueService::class, 'service_id');
    }

    public function category()
    {
        return $this->belongsTo(CatalogueCategory::class, 'categorie_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function documents()
    {
        return $this->hasMany(CatalogueOrderDocument::class, 'commande_id');
    }

    public function messages()
    {
        return $this->hasMany(CatalogueOrderMessage::class, 'commande_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(CatalogueOrderStatusHistory::class, 'commande_id');
    }
}
