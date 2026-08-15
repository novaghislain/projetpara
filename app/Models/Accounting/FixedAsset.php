<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'description',
        'purchase_date',
        'purchase_price',
        'salvage_value', // Valeur résiduelle
        'useful_life_years', // Durée de vie utile
        'depreciation_method', // straight_line, declining_balance
        'status' // active, disposed, sold
    ];

    public function depreciations()
    {
        return $this->hasMany(AssetDepreciation::class);
    }
}
