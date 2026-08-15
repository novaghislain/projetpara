<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelRevenueRecognition extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_revenue_recognition';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'libelle',
        'montant_total',
        'date_debut',
        'date_fin',
        'statut',
        'montant_reconnu',
        'source_id'
    ];

}
