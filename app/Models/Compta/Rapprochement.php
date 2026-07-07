<?php

namespace App\Models\Compta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\User;

class Rapprochement extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'compte_id', 'date_debut', 'date_fin', 'solde_depart', 
        'solde_fin', 'is_valide', 'validated_by', 'validated_at', 
        'lignes_rapprochees', 'rapport_pdf_path'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'validated_at' => 'datetime',
        'is_valide' => 'boolean',
        'lignes_rapprochees' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
