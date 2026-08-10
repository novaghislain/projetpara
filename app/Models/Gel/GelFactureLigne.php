<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class GelFactureLigne extends Model
{
    protected $table = 'gel_facture_lignes';

    protected $fillable = [
        'facture_id',
        'designation',
        'description',
        'quantite',
        'prix_unitaire',
        'taux_tva',
        'total_ht',
        'total_ttc',
        'compte_produit_id',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    public function facture()
    {
        return $this->belongsTo(GelFacture::class, 'facture_id');
    }

    public function compteProduit()
    {
        return $this->belongsTo(PlanComptableSyscohada::class, 'compte_produit_id');
    }
}
