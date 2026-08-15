<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactureLigne extends Model
{
    protected $table = 'gel_facture_lignes';

    protected $fillable = [
        'facture_id',
        'produit_id',
        'description',
        'quantite',
        'prix_unitaire',
        'taux_tva',
        'total_ht',
        'total_ttc',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class, 'facture_id');
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(GelProduit::class, 'produit_id');
    }
}
