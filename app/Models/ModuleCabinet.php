<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle ModuleCabinet (Module de cabinet).
 *
 * Gère l'activation des modules additionnels pour un cabinet.
 * Chaque module a sa propre configuration, ses limites d'utilisateurs
 * et de stockage, ainsi que sa période de validité.
 * Table associée : `module_cabinets`.
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet
 * @property string $module Nom technique du module
 * @property string $label Libellé affiché du module
 * @property bool $is_active Si le module est actif
 * @property array|null $config Configuration spécifique (JSON)
 * @property int|null $max_users Nombre max d'utilisateurs
 * @property int|null $max_storage_mb Stockage max (Mo)
 * @property \Carbon\Carbon|null $date_activation Date d'activation
 * @property \Carbon\Carbon|null $date_expiration Date d'expiration
 *
 * @property-read \App\Models\Cabinet $cabinet Cabinet associé
 */
class ModuleCabinet extends Model
{
    protected $table = 'module_cabinets';

    protected $fillable = [
        'cabinet_id', 'module', 'label', 'is_active',
        'config', 'max_users', 'max_storage_mb',
        'date_activation', 'date_expiration',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config' => 'array',
            'date_activation' => 'date',
            'date_expiration' => 'date',
        ];
    }

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }
}
