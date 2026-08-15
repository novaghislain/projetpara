<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bulletin extends Model
{
    use SoftDeletes;
    
    protected $table = 'gel_bulletins';

    protected $fillable = [
        'salarie_id',
        'client_id',
        'mois',
        'annee',
        'salaire_brut',
        'retenue_cnss',
        'retenue_its',
        'salaire_net',
        'statut',
    ];

    protected $casts = [
        'salaire_brut' => 'decimal:2',
        'retenue_cnss' => 'decimal:2',
        'retenue_its' => 'decimal:2',
        'salaire_net' => 'decimal:2',
    ];

    public function salarie()
    {
        return $this->belongsTo(Salarie::class);
    }
    
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
