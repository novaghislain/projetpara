<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salarie extends Model
{
    use SoftDeletes;
    
    protected $table = 'gel_salaries';

    protected $fillable = [
        'client_id',
        'nom',
        'prenom',
        'date_embauche',
        'salaire_base',
        'statut',
        'numero_cnss',
        'situation_matrimoniale',
        'nombre_enfants',
    ];

    protected $casts = [
        'date_embauche' => 'date',
        'salaire_base' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    public function bulletins()
    {
        return $this->hasMany(Bulletin::class);
    }
}
