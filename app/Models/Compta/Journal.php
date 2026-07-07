<?php

namespace App\Models\Compta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'code', 'intitule', 'type', 'compte_defaut_id', 'is_actif'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function compteDefaut()
    {
        return $this->belongsTo(Compte::class, 'compte_defaut_id');
    }

    public function ecritures()
    {
        return $this->hasMany(Ecriture::class);
    }
}
