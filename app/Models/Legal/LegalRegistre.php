<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle LegalRegistre (Registre légal).
 *
 * Représente un registre obligatoire (ex: registre des assemblées,
 * registre des actions, registre des décisions) avec ses entrées
 * au format JSON et son état d'ouverture/clôture.
 * Table associée : `legal_registres`.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $type Type de registre
 * @property int $annee Année du registre
 * @property array $entrees Entrées du registre (JSON)
 * @property bool $is_closed Si le registre est clôturé
 * @property \Carbon\Carbon|null $closed_at Date de clôture
 */
class LegalRegistre extends LegalBaseModel
{
    use HasFactory;

    protected $table = 'legal_registres';

    protected $fillable = [
        'client_id', 'type', 'annee',
        'entrees', 'is_closed', 'closed_at',
    ];

    protected $casts = [
        'entrees' => 'json',
        'is_closed' => 'boolean',
        'closed_at' => 'date',
    ];
}
