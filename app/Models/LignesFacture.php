<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LignesFacture extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        return $this->belongsTo(Facture::class, 'facture_id', 'id');
    }

}
