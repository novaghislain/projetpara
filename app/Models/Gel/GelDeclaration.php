<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelDeclaration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cabinet_id', 'client_id', 'type', 'periode', 'date_echeance', 
        'date_soumission', 'statut', 'montant_du', 'administration', 
        'notes', 'created_by'
    ];
}
