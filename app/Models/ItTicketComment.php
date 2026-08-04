<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle ItTicketComment (Commentaire de ticket IT).
 *
 * Représente un commentaire ou une note ajoutée à un ticket de support.
 * Les commentaires peuvent être internes (visibles seulement par le staff)
 * ou publics. Supporte les pièces jointes.
 *
 * @property int $id
 * @property int $ticket_id ID du ticket parent
 * @property int $user_id ID de l'utilisateur auteur
 * @property string $body Contenu du commentaire
 * @property bool $is_internal Commentaire interne (staff only)
 * @property array|null $attachments Pièces jointes (JSON)
 *
 * @property-read \App\Models\ItTicket $ticket Ticket parent
 * @property-read \App\Models\User $user Auteur du commentaire
 */
class ItTicketComment extends Model
{
    protected $fillable = [
        'ticket_id', 'user_id', 'body', 'is_internal', 'attachments',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'attachments' => 'array',
        ];
    }

    public function ticket(): BelongsTo { return $this->belongsTo(ItTicket::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
