<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class AssetDepreciation extends Model
{
    protected $fillable = [
        'fixed_asset_id',
        'year',
        'depreciation_amount', // Montant de l'amortissement pour l'année
        'accumulated_depreciation', // Amortissement cumulé
        'net_book_value' // VNC (Valeur Nette Comptable)
    ];

    public function asset()
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }
}
