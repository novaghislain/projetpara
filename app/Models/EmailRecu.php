<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle représentant un email reçu (importé depuis un compte IMAP).
 *
 * Table associée : `emails_recus`
 *
 * Stocke les emails entrants avec leur contenu HTML et texte,
 * et les pièces jointes. Peut être transmis en interne.
 *
 * Relations :
 * - Un email reçu peut avoir plusieurs transmissions internes (TransmissionInterne)
 */
class EmailRecu extends Model
{
    protected $table = 'emails_recus';

    protected $fillable = [
        'message_id_imap',
        'expediteur',
        'expediteur_email',
        'objet',
        'corps_html',
        'corps_texte',
        'date_reception',
        'statut',
        'pieces_jointes_json',
    ];

    protected $casts = [
        'pieces_jointes_json' => 'array',
        'date_reception' => 'datetime',
    ];

    public function transmissions(): HasMany
    {
        return $this->hasMany(TransmissionInterne::class, 'email_recu_id');
    }
}
