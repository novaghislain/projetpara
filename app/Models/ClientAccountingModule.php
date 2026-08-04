<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant l'activation d'un module comptable pour un client.
 *
 * Table pivot entre les clients et les modules comptables.
 * Chaque entrée représente l'activation d'un module spécifique
 * (ex: "stock", "gestion_chambres", "quittances_loyer") pour
 * une entreprise cliente donnée, avec sa configuration.
 *
 * @property int $id
 * @property int $client_id Identifiant du client
 * @property string $module Code du module (ex: stock, hotel, location)
 * @property bool $is_active Module actif
 * @property array|null $config Configuration du module
 * @property string|null $activated_at Date d'activation
 * @property int|null $activated_by Identifiant de l'utilisateur ayant activé
 *
 * @property-read Client $client Client associé
 * @property-read User|null $activator Utilisateur ayant activé le module
 *
 * @table client_accounting_modules
 */
class ClientAccountingModule extends Model
{
    protected $table = 'client_accounting_modules';

    protected $fillable = [
        'client_id',
        'module',
        'is_active',
        'config',
        'activated_at',
        'activated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config' => 'array',
            'activated_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function activator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    /**
     * Scope : modules actifs uniquement.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope : par client.
     */
    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
