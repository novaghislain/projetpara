<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class GelFacture extends Model
{
    protected $table = 'gel_factures';

    protected $fillable = [
        'entreprise_id',
        'client_id',
        'type',
        'numero',
        'client_nom',
        'montant',
        'total_ht',
        'total_tva',
        'total_ttc',
        'date_facture',
        'statut',
        'ecriture_comptable_id',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'total_tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'date_facture' => 'date',
    ];

    public function lignes()
    {
        return $this->hasMany(GelFactureLigne::class, 'facture_id');
    }

    public function ecritureComptable()
    {
        return $this->belongsTo(\App\Models\Gel\EcritureComptable::class, 'ecriture_comptable_id');
    }
}
