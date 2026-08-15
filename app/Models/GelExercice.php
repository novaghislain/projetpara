<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelExercice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_exercices';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'annee',
        'date_debut',
        'date_fin',
        'statut',
        'clos_at',
        'clos_par'
    ];

}
