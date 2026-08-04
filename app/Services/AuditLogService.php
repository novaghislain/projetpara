<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Gel\AuditLog as GelAuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    /**
     * Log an action in the audit_logs table and gel_audit_logs table.
     */
    public static function log(string $action, $entity, array $before = null, array $after = null)
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        $clientId = $entity->client_id ?? session('selected_client_id') ?? null;

        // La table audit_logs et gel_audit_logs pointent vers `gel_clients`.
        // Si le client_id vient d'un modèle non-GEL (ex: Document), il pointe vers `clients`.
        // On évite la violation de clé étrangère (SQLSTATE 1452) en le forçant à null.
        if ($clientId && !str_starts_with(get_class($entity), 'App\Models\Gel')) {
            $clientId = null;
        }

        try {
            // Log to old audit_logs table
        AuditLog::create([
            'cabinet_id'  => $user->cabinet_id,
            'actor_id'    => $user->id,
            'actor_email' => $user->email,
            'actor_role'  => $user->isAccountant() ? 'accountant' : 'client',
            'client_id'   => $clientId,
            'action'      => $action,
            'entity_type' => get_class($entity),
            'entity_id'   => $entity->id ?? null,
            'before'      => $before,
            'after'       => $after,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

        // Also log to new gel_audit_logs table
        GelAuditLog::create([
            'cabinet_id' => $user->cabinet_id,
            'user_id' => $user->id,
            'actor_name' => $user->name,
            'actor_email' => $user->email,
            'actor_role' => $user->account_type ?? ($user->isAccountant() ? 'accountant' : 'client'),
            'client_id' => $clientId,
            'event' => $action,
            'auditable_type' => get_class($entity),
            'auditable_id' => $entity->id ?? null,
            'description' => "Action: {$action} sur " . class_basename($entity),
            'old_values' => $before,
            'new_values' => $after,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
        ]);
        } catch (\Exception $e) {
            // Ne pas bloquer l'application si le log échoue (ex: erreur de contrainte)
            \Illuminate\Support\Facades\Log::error('AuditLogService failed to log: ' . $e->getMessage());
        }
    }
}

