<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelDeclarationsFiscale extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_declarations_fiscales';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'type',
        'periode',
        'base_imposable',
        'montant_impot',
        'montant_paye',
        'statut',
        'date_soumission',
        'date_echeance',
        'notes'
    ];

}
