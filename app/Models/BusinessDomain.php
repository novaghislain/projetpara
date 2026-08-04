<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle représentant un domaine d'activité d'une entreprise cliente.
 *
 * Chaque entreprise cliente a un domaine d'activité qui détermine les
 * fonctionnalités comptables disponibles (modules obligatoires + optionnels).
 * Les modules sont stockés sous forme de tableaux JSON.
 *
 * @property int $id
 * @property string $code Code unique du domaine
 * @property string $label Libellé du domaine
 * @property string|null $description Description
 * @property string|null $icon Icône du domaine
 * @property array $modules_comptables Modules obligatoires pour ce domaine
 * @property array $modules_optionnels Modules optionnels activables
 * @property bool $is_active Domaine actif
 * @property int $sort_order Ordre d'affichage
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|Client[] $clients Entreprises ayant ce domaine
 *
 * @table business_domains
 */
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
