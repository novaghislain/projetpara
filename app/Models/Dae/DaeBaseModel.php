<?php

namespace App\Models\Dae;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class DaeBaseModel extends Model
{
    /**
     * Initialise le trait d'audit DAE au boot du modèle.
     */
    protected static function booted(): void
    {
        static::bootDaeAudit();
    }

    /**
     * Enregistre une action dans le journal d'audit DAE.
     */
    protected static function bootDaeAudit(): void
    {
        $module = (new static)->getDaeModuleName();

        static::created(function ($model) use ($module) {
            $model->logAudit($module, 'create', null, $model->toArray());
        });

        static::updated(function ($model) use ($module) {
            if (!$model->isDirty()) return;
            $changes = $model->getChanges();
            $original = array_intersect_key($model->getOriginal(), $changes);
            $model->logAudit($module, 'update', $original, $changes);
        });

        static::deleted(function ($model) use ($module) {
            $model->logAudit($module, 'delete', $model->toArray(), null);
        });
    }

    /**
     * Enregistre un log d'audit.
     */
    protected function logAudit(string $module, string $action, ?array $oldValues, ?array $newValues): void
    {
        DaeAuditLog::create([
            'dae_module'  => $module,
            'action'      => $action,
            'entity_type' => get_class($this),
            'entity_id'   => $this->id,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'user_id'     => Auth::id(),
            'ip_address'  => request()->ip(),
        ]);
    }

    /**
     * Nom du module DAE pour l'audit (surchargé par chaque modèle).
     */
    abstract protected function getDaeModuleName(): string;

    /**
     * Relation avec le client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope par client.
     */
    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
