<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RhContract extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rh_contracts';

    protected $fillable = [
        'client_id',
        'salarie_id',
        'type',
        'date_debut',
        'date_fin',
        'salaire',
        'statut'
    ];

    public function salarie()
    {
        return $this->belongsTo(GelSalary::class, 'salarie_id', 'id');
    }

}
