<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant un module activé pour un client.
 *
 * Table pivot entre les clients et les modules fonctionnels.
 * Permet d'activer ou désactiver des modules spécifiques
 * pour chaque entreprise cliente.
 *
 * @property int $client_id Identifiant du client
 * @property string $module Code du module
 * @property bool $is_active Module actif
 * @property string|null $activated_at Date d'activation
 * @property int|null $activated_by Identifiant de l'utilisateur ayant activé
 *
 * @property-read Client $client Client associé
 * @property-read User|null $activator Utilisateur ayant activé
 *
 * @table client_modules
 */
class ClientModule extends Model
{
    protected $table = 'client_modules';

    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'module',
        'is_active',
        'activated_at',
        'activated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'activated_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function activator()
    {
        return $this->belongsTo(User::class, 'activated_by');
    }
}
