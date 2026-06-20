<?php

namespace App\Traits;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Request;

/**
 * Trait Auditable — Traçabilité automatique des actions sur les modèles.
 *
 * Appliquer ce trait sur les modèles sensibles (Invoice, JournalEntry, User, etc.)
 * pour enregistrer automatiquement toutes les créations, modifications et suppressions
 * dans la table audit_logs.
 *
 * Usage : class Invoice extends Model { use \App\Traits\Auditable; }
 */
trait Auditable
{
    /**
     * Boot le trait — enregistre les événements Eloquent.
     */
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::logAudit('create', $model, [], $model->getAttributes());
        });

        static::updated(function ($model) {
            static::logAudit('update', $model, $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function ($model) {
            static::logAudit('delete', $model, $model->getAttributes(), []);
        });
    }

    /**
     * Enregistrer une entrée dans le journal d'audit.
     */
    private static function logAudit(string $action, $model, array $old, array $new): void
    {
        // Champs à exclure du log (données sensibles)
        $except = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

        // Ne pas logger les modèles sans client_id
        $clientId = $model->client_id ?? (auth()->user()->client_id ?? null);

        AuditTrail::create([
            'client_id'      => $clientId ?? 0,
            'user_id'        => auth()->id(),
            'event'          => $action,
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->getKey(),
            'old_values'     => !empty($old) ? array_diff_key($old, array_flip($except)) : null,
            'new_values'     => !empty($new) ? array_diff_key($new, array_flip($except)) : null,
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
            'description'    => ucfirst($action) . ' : ' . class_basename($model) . ' #' . $model->getKey(),
        ]);
    }
}
