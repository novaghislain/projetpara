<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeModelesCourrier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_modeles_courriers';

    protected $fillable = [
        'user_id',
        'nom',
        'type',
        'contenu',
        'variables',
        'actif'
    ];

}
