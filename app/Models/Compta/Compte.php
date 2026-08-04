<?php

namespace App\Models\Compta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;

/**
 * Modèle représentant un compte du plan comptable SYSCOHADA.
 *
 * Table associée : `comptes` (via convention Laravel, préfixe Compta)
 *
 * Relations :
 * - Un compte appartient à un client (Client)
 * - Un compte peut avoir un compte parent (Compte hiérarchique)
 * - Un compte peut avoir plusieurs sous-comptes enfants (Compte)
 */
class Compte extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'numero', 'intitule', 'classe', 'type', 'sous_type', 
        'parent_id', 'solde_debiteur', 'solde_crediteur', 'is_actif', 'is_verrouille'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function parent()
    {
        return $this->belongsTo(Compte::class, 'parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(Compte::class, 'parent_id');
    }
}
