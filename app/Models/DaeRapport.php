<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeRapport extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_rapports';

    protected $fillable = [
        'client_id',
        'created_by',
        'titre',
        'type',
        'contenu',
        'fichier_path',
        'statut'
    ];

}
