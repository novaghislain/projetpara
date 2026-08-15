<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RhTraining extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rh_trainings';

    protected $fillable = [
        'client_id',
        'intitule',
        'organisme',
        'date_debut',
        'date_fin',
        'statut'
    ];

}
