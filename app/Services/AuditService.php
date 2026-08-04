<?php

namespace App\Services;

use App\Models\Gel\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Enregistre une action utilisateur dans la table gel_audit_logs.
     */
    public static function log(string $event, Model $entity, ?array $oldValues = null, ?array $newValues = null, ?string $description = null): ?AuditLog
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $clientId = session('selected_client_id');
        if (!$clientId && isset($entity->client_id)) {
            $clientId = $entity->client_id;
        }

        if ($clientId && !str_starts_with(get_class($entity), 'App\Models\Gel')) {
            $clientId = null;
        }

        try {
            return AuditLog::create([
            'cabinet_id' => $user->cabinet_id,
            'user_id' => $user->id,
            'actor_name' => $user->name,
            'actor_email' => $user->email,
            'actor_role' => $user->account_type, // 'comptable', 'admin', 'client', etc.
            'client_id' => $clientId,
            'event' => $event,
            'auditable_type' => get_class($entity),
            'auditable_id' => $entity->getKey(),
            'description' => $description ?? self::generateDescription($event, $entity),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'session_id' => session()->getId(),
        ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AuditService failed to log: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Génère une description textuelle automatique par défaut si absente.
     */
    private static function generateDescription(string $event, Model $entity): string
    {
        $className = class_basename($entity);
        $id = $entity->getKey();
        $nameOrNum = $entity->numero ?? $entity->name ?? $entity->libelle ?? '';

        $info = $nameOrNum ? " ({$nameOrNum})" : '';

        switch ($event) {
            case 'created':
                return "Création de {$className} #{$id}{$info}";
            case 'updated':
                return "Modification de {$className} #{$id}{$info}";
            case 'deleted':
                return "Suppression de {$className} #{$id}{$info}";
            case 'validated':
                return "Validation de {$className} #{$id}{$info}";
            case 'consulted':
                return "Consultation de {$className} #{$id}{$info}";
            default:
                return "Événement {$event} sur {$className} #{$id}";
        }
    }
}
