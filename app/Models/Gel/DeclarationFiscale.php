<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeclarationFiscale extends Model
{
    use SoftDeletes;
    
    protected $table = 'gel_declarations_fiscales';

    protected $fillable = [
        'client_id',
        'type_impot',
        'periode_mois',
        'periode_annee',
        'montant_base',
        'montant_impot',
        'statut',
        'details',
    ];

    protected $casts = [
        'montant_base' => 'decimal:2',
        'montant_impot' => 'decimal:2',
        'details' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
