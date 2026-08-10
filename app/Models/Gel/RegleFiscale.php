<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegleFiscale extends Model
{
    use HasFactory;

    protected $table = 'regle_fiscale';

    protected $fillable = [
        'entreprise_id',
        'pays_code',
        'type_impot',
        'regime_fiscal',
        'date_debut_validite',
        'date_fin_validite',
        'parametres',
        'compte_comptable_id'
    ];

    protected $casts = [
        'parametres' => 'array',
        'date_debut_validite' => 'date',
        'date_fin_validite' => 'date',
    ];
}
