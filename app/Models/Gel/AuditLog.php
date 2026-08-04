<?php

namespace App\Models\Gel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle AuditLog (Journal d'audit).
 *
 * Enregistre toutes les actions importantes effectuées dans le système
 * (création, modification, suppression d'entités) pour la traçabilité.
 * Table associée : `gel_audit_logs`.
 * Relation polymorphe via `auditable` pour lier à n'importe quel modèle.
 *
 * @property int $id
 * @property int|null $cabinet_id ID du cabinet
 * @property int|null $user_id ID de l'utilisateur à l'origine de l'action
 * @property string|null $actor_name Nom de l'acteur
 * @property string|null $actor_email Email de l'acteur
 * @property string|null $actor_role Rôle de l'acteur
 * @property int|null $client_id ID du client
 * @property string $event Type d'événement (created, updated, deleted, etc.)
 * @property string $auditable_type Type du modèle audité (polymorphe)
 * @property int $auditable_id ID du modèle audité (polymorphe)
 * @property string|null $description Description textuelle de l'action
 * @property array|null $old_values Anciennes valeurs (format JSON)
 * @property array|null $new_values Nouvelles valeurs (format JSON)
 * @property string|null $ip_address Adresse IP de l'utilisateur
 * @property string|null $user_agent User-Agent du navigateur
 * @property string|null $session_id ID de session
 *
 * @property-read \App\Models\Gel\Cabinet|null $cabinet Cabinet associé
 * @property-read \App\Models\Gel\Client|null $client Client associé
 * @property-read \App\Models\User|null $user Utilisateur à l'origine
 * @property-read \Illuminate\Database\Eloquent\Model|null $auditable Entité auditée (polymorphe)
 */
class AuditLog extends Model
{
    protected $table = 'gel_audit_logs';

    protected $fillable = [
        'cabinet_id',
        'user_id',
        'actor_name',
        'actor_email',
        'actor_role',
        'client_id',
        'event',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'session_id',
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Dictionnaire des traductions des événements.
     */
    public static function getEventMap(): array
    {
        return [
            'client.switch_context' => 'Changement de dossier',
            'client.select'         => 'Sélection de dossier',
            'document.upload'       => 'Dépôt de document',
            'document.download'     => 'Téléchargement de document',
            'document.read'         => 'Lecture de document',
            'document.delete'       => 'Suppression de document',
            'ecriture.create'       => 'Saisie comptable',
            'ecriture.update'       => 'Modification comptable',
            'ecriture.delete'       => 'Suppression comptable',
            'ecriture.valider'      => 'Validation comptable',
            'ecriture.show'         => 'Consultation comptable',
            'created'               => 'Création',
            'updated'               => 'Mise à jour',
            'deleted'               => 'Suppression',
            'validated'             => 'Validation',
            'consulted'             => 'Consultation',
        ];
    }

    /**
     * Retourne une version "lisible par un humain" (non informaticien) du nom de l'événement.
     */
    public function getReadableEventAttribute()
    {
        $map = self::getEventMap();
        return $map[$this->event] ?? ucfirst(str_replace(['.', '_'], ' ', $this->event));
    }

    /**
     * Retourne une description clarifiée et lisible pour l'audit.
     */
    public function getReadableDescriptionAttribute()
    {
        // Si la description contient 'Action: xxx sur Yyy' (format technique)
        if (str_starts_with($this->description, 'Action:')) {
            $parts = explode(' sur ', $this->description);
            $entityName = isset($parts[1]) ? $parts[1] : '';
            
            $translatedEvent = $this->readable_event;

            if ($entityName) {
                return "{$translatedEvent} ({$entityName})";
            }
            return $translatedEvent;
        }

        // Sinon, on retourne la description d'origine (qui est peut-être déjà en français clair)
        return $this->description;
    }
}
