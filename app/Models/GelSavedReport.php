<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelSavedReport extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_saved_reports';

    protected $fillable = [
        'client_id',
        'user_id',
        'titre',
        'type',
        'parametres',
        'fichier_path'
    ];

}
