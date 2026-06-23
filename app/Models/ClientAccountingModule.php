<?php
// =============================================================================
// FICHIER : ClientAccountingModule.php
// RÔLE    : Modèle — Activation d'un module comptable pour un client
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Table pivot entre les clients et les modules comptables.
// Chaque entrée représente l'activation d'un module spécifique (ex: "stock",
// "gestion_chambres", "quittances_loyer") pour une entreprise cliente donnée.
//
// Relations :
//   - client()    : BelongsTo → Client
//   - activator() : BelongsTo → User (qui a activé le module)
//
// Scopes :
//   - active()    : modules activés uniquement
//   - byClient()  : modules d'un client spécifique
//
// Voir aussi : BusinessDomain, TenantDomainService
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAccountingModule extends Model
{
    protected $table = 'client_accounting_modules';

    protected $fillable = [
        'client_id',
        'module',
        'is_active',
        'config',
        'activated_at',
        'activated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config' => 'array',
            'activated_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function activator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    /**
     * Scope : modules actifs uniquement.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope : par client.
     */
    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
