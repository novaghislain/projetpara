<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelFacture extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_factures';

    protected $fillable = [
        'client_id',
        'numero',
        'date_facture',
        'date_echeance',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
        'statut'
    ];

    public function gelFactureLignes()
    {
        return $this->hasMany(GelFactureLigne::class, 'facture_id', 'id');
    }

}
