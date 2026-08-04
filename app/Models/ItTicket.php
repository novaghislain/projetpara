<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle ItTicket (Ticket de support IT).
 *
 * Gère les tickets de support technique avec leur cycle de vie complet :
 * assignation, priorité, suivi SLA, résolution, et clôture.
 * Les tickets peuvent être facturables avec suivi des heures.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $ticket_number Numéro unique du ticket
 * @property string $title Titre du ticket
 * @property string $description Description détaillée
 * @property string $type Type (incident, demande, problème, changement)
 * @property string $priority Priorité (basse, moyenne, haute, critique)
 * @property string $status Statut (ouvert, en_cours, resolu, ferme)
 * @property string|null $category Catégorie
 * @property int|null $assigned_to ID du technicien assigné
 * @property int $requested_by ID du demandeur
 * @property int|null $sla_id ID de la politique SLA appliquée
 * @property \Carbon\Carbon|null $sla_due_at Échéance SLA
 * @property bool $sla_breached SLA non respecté
 * @property string|null $resolution Résolution apportée
 * @property \Carbon\Carbon|null $first_response_at Première réponse
 * @property \Carbon\Carbon|null $resolved_at Date de résolution
 * @property \Carbon\Carbon|null $closed_at Date de clôture
 * @property bool $billable Facturable
 * @property float|null $billed_hours Heures facturées
 *
 * @property-read \App\Models\Client $client Client associé
 * @property-read \App\Models\User|null $assignedTo Technicien assigné
 * @property-read \App\Models\User $requestedBy Demandeur
 * @property-read \App\Models\ItSlaPolicy|null $sla Politique SLA
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ItTicketComment[] $comments Commentaires
 */
class ItTicket extends Model
{
    protected $fillable = [
        'client_id', 'ticket_number', 'title', 'description',
        'type', 'priority', 'status', 'category',
        'assigned_to', 'requested_by', 'sla_id', 'sla_due_at',
        'sla_breached', 'resolution', 'first_response_at',
        'resolved_at', 'closed_at', 'billable', 'billed_hours',
    ];

    protected function casts(): array
    {
        return [
            'sla_breached' => 'boolean',
            'billable' => 'boolean',
            'sla_due_at' => 'datetime',
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'billed_hours' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function requestedBy(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function sla(): BelongsTo { return $this->belongsTo(ItSlaPolicy::class, 'sla_id'); }
    public function comments(): HasMany { return $this->hasMany(ItTicketComment::class, 'ticket_id'); }
}
