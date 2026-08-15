<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelDepense extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_depenses';

    protected $fillable = [
        'client_id',
        'user_id',
        'categorie',
        'montant',
        'date_depense',
        'description',
        'statut',
        'justificatif_path'
    ];

}
