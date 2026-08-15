<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LigneFacture extends Model
{
    use HasUuids;

    protected $table = 'lignes_facture';

    protected $fillable = [
        'facture_id',
        'designation',
        'quantite',
        'prix_unitaire',
        'taux_tva',
        'total_ht'
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }
}
