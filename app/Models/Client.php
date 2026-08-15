<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Client (Dossier du cabinet)
 * 
 * LEXIQUE TECHNIQUE : FRONTIÈRE D'ISOLATION
 * - Entreprise : Entité racine (SaaS), facturation de la plateforme GEL.
 * - Cabinet : Le cabinet d'expertise comptable (Tenant principal).
 * - Client : Le "Dossier" géré par le cabinet. Sert de base au cloisonnement 
 *            (multi-tenant métier via `client_id`) pour toutes les tables 
 *            transactionnelles (écritures, factures, workflow).
 * - Partner : Le tiers (fournisseur/client final) avec qui ce Client fait affaire.
 */
class Client extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'clients';

    protected $fillable = [
        'entreprise_id',
        'nom_entreprise',
        'sigle',
        'email',
        'telephone',
        'adresse',
        'ville',
        'nif',
        'rc',
        'secteur',
        'statut',
        'centre_impots_rattachement'
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }
}
