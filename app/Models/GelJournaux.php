<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelJournaux extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_journaux';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'code',
        'libelle',
        'type',
        'compte_contrepartie',
        'actif'
    ];

}
