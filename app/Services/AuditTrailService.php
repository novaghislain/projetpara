<?php

namespace App\Services;

use App\Models\AuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditTrailService
{
    /**
     * Log an event in the audit trail.
     */
    public static function log(
        Model $model,
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?int $clientId = null,
        ?int $userId = null,
    ): AuditTrail {
        // Detect client_id from model if available
        if (!$clientId && $model->getConnectionName() !== '') {
            $clientId = $model->client_id ?? null;
        }

        // Detect user from request or model
        if (!$userId) {
            $userId = auth()->id();
        }

        return AuditTrail::create([
            'client_id'      => $clientId ?? $model->client_id ?? 0,
            'user_id'        => $userId,
            'event'          => $event,
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->getKey(),
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
            'description'    => $description ?? "{$event}: " . class_basename($model) . " #{$model->getKey()}",
        ]);
    }

    /**
     * Boot audit trail events on a model.
     * Call this in your ServiceProvider or model's booted().
     */
    public static function bootAudit(Model $model): void
    {
        $model::created(function (Model $m) {
            self::log($m, 'created', null, $m->toArray());
        });

        $model::updated(function (Model $m) {
            $changed = [];
            foreach ($m->getDirty() as $key => $value) {
                $changed[$key] = [
                    'old' => $m->getOriginal($key),
                    'new' => $value,
                ];
            }
            if (!empty($changed)) {
                self::log($m, 'updated', $m->getOriginal(), $m->toArray(), json_encode($changed));
            }
        });

        $model::deleted(function (Model $m) {
            self::log($m, 'deleted', $m->toArray(), null);
        });
    }
}
