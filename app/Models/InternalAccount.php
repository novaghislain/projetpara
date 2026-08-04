<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle InternalAccount (Compte interne).
 *
 * Représente un compte de transmission interne pour la communication
 * entre les différents modules de la plateforme.
 * Permet d'envoyer et recevoir des transmissions internes.
 * Table associée : `internal_accounts`.
 *
 * @property int $id
 * @property string $type Type de compte interne
 * @property string $name Nom du compte
 * @property string|null $email_connexion Email de connexion IMAP
 * @property string|null $email_imap Email IMAP pour réception
 * @property array|null $modules_json Modules associés (JSON)
 * @property bool $actif Si le compte est actif
 * @property bool $created_by_super_admin Créé par un super admin
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TransmissionInterne[] $transmissionsSent Transmissions envoyées
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TransmissionInterne[] $transmissionsReceived Transmissions reçues
 */
class InternalAccount extends Model
{
    protected $table = 'internal_accounts';

    protected $fillable = [
        'type',
        'name',
        'email_connexion',
        'email_imap',
        'modules_json',
        'actif',
        'created_by_super_admin',
    ];

    protected $casts = [
        'modules_json' => 'array',
    ];

    /**
     * Get the transmissions sent by this account.
     */
    public function transmissionsSent(): HasMany
    {
        return $this->hasMany(TransmissionInterne::class, 'transmis_par_id');
    }

    /**
     * Get the transmissions received by this account.
     */
    public function transmissionsReceived(): HasMany
    {
        return $this->hasMany(TransmissionInterne::class, 'transmis_a_id');
    }
}
