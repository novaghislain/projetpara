<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelFactureLigne extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_facture_lignes';

    protected $fillable = [
        'facture_id',
        'designation',
        'quantite',
        'prix_unitaire',
        'tva',
        'montant_ht'
    ];

    public function facture()
    {
        return $this->belongsTo(GelFacture::class, 'facture_id', 'id');
    }

}
