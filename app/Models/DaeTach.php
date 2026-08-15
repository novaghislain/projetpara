<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeTach extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_taches';

    protected $fillable = [
        'client_id',
        'assigned_to',
        'created_by',
        'titre',
        'description',
        'statut',
        'priorite',
        'echeance',
        'termine_at'
    ];

}
