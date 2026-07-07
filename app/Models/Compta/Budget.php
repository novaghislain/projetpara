<?php

namespace App\Models\Compta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'exercice_id', 'libelle', 'departement', 'lignes_budget', 'is_actif'
    ];

    protected $casts = [
        'lignes_budget' => 'array',
        'is_actif' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
    }
}
