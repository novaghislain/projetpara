<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant le journal d'audit du module DAE.
 *
 * Table associée : `dae_audit_logs`
 *
 * Enregistre chaque action (création, modification, suppression)
 * effectuée sur les entités du module DAE.
 */
class DaeAuditLog extends Model
{
    protected $table = 'dae_audit_logs';

    protected $fillable = [
        'dae_module', 'action', 'entity_type', 'entity_id',
        'old_values', 'new_values', 'user_id', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('dae_module', $module);
    }

    public function scopeRecents($query, int $limit = 50)
    {
        return $query->latest()->limit($limit);
    }
}
