<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelBulletin extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_bulletins';

    protected $fillable = [
        'client_id',
        'salarie_id',
        'periode',
        'salaire_base',
        'net_a_payer',
        'statut',
        'fichier_path'
    ];

}
