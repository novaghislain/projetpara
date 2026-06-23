<?php
// =============================================================================
// FICHIER : BusinessDomain.php
// RÔLE    : Modèle — Domaine d'activité d'une entreprise cliente
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Chaque entreprise cliente a un domaine d'activité qui détermine les
// fonctionnalités comptables disponibles (modules obligatoires + optionnels).
//
// Relations :
//   - clients() : HasMany → les entreprises ayant ce domaine
//
// Casts :
//   - modules_comptables → array (modules obligatoires pour ce domaine)
//   - modules_optionnels → array (modules activables en option)
//
// Voir aussi : ClientAccountingModule, TenantDomainService
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessDomain extends Model
{
    protected $fillable = [
        'code',
        'label',
        'description',
        'icon',
        'modules_comptables',
        'modules_optionnels',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'modules_comptables' => 'array',
            'modules_optionnels' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'domain_id');
    }

    /**
     * Raccourci : tous les modules (obligatoires + optionnels).
     */
    public function getAllModules(): array
    {
        return array_merge($this->modules_comptables ?? [], $this->modules_optionnels ?? []);
    }

    /**
     * Scope : domaines actifs triés.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
