<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelProfilProfessionnel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_profil_professionnels';

    protected $fillable = [
        'user_id',
        'specialite',
        'certifications',
        'bio'
    ];

}
