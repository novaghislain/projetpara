<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle TransmissionInterne - Transmission interne d'un email reçu.
 *
 * Table associée : 'transmissions_internes'.
 * Gère la transmission d'un email reçu vers un autre compte interne,
 * avec note, priorité et suivi du traitement.
 * Relations :
 * - emailRecu() : appartient à un email reçu (EmailRecu).
 * - transmisPar() : appartient au compte interne expéditeur (InternalAccount).
 * - transmisA() : appartient au compte interne destinataire (InternalAccount).
 */
class TransmissionInterne extends Model
{
    protected $table = 'transmissions_internes';

    protected $fillable = [
        'email_recu_id',
        'transmis_par_id',
        'transmis_a_id',
        'note',
        'priorite',
        'statut',
        'date_transmission',
        'date_traitement',
    ];

    protected $casts = [
        'date_transmission' => 'datetime',
        'date_traitement' => 'datetime',
    ];

    public function emailRecu(): BelongsTo
    {
        return $this->belongsTo(EmailRecu::class, 'email_recu_id');
    }

    public function transmisPar(): BelongsTo
    {
        return $this->belongsTo(InternalAccount::class, 'transmis_par_id');
    }

    public function transmisA(): BelongsTo
    {
        return $this->belongsTo(InternalAccount::class, 'transmis_a_id');
    }
}
