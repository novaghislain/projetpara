<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modèle représentant un cabinet comptable.
 *
 * Un cabinet est une entité qui héberge plusieurs clients (entreprises)
 * et utilisateurs. Il possède sa propre configuration, ses limites
 * d'utilisation et ses modules activés. Le cabinet a un identifiant
 * fiscal (IFU) et un RCCM pour l'identification légale.
 *
 * @property int $id
 * @property string $nom Nom du cabinet
 * @property string $slug Slug pour l'URL
 * @property string|null $email Email de contact
 * @property string|null $telephone Téléphone
 * @property string|null $adresse Adresse
 * @property string|null $ville Ville
 * @property string|null $pays Pays
 * @property string|null $logo URL du logo
 * @property string|null $site_web Site web
 * @property string|null $description Description
 * @property string|null $ifu IFU (Identifiant Fiscal Unique)
 * @property string|null $rccm RCCM
 * @property bool $is_active Cabinet actif
 * @property array $config Configuration (JSON)
 * @property array $limits Limites d'utilisation (JSON)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $users Utilisateurs du cabinet
 * @property-read \Illuminate\Database\Eloquent\Collection|ModuleCabinet[] $modules Modules du cabinet
 * @property-read \Illuminate\Database\Eloquent\Collection|ModuleCabinet[] $activeModules Modules actifs
 * @property-read \Illuminate\Database\Eloquent\Collection|ChatConversation[] $chatConversations Conversations
 *
 * @table cabinets
 */
class Cabinet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom', 'slug', 'email', 'telephone', 'adresse', 'ville', 'pays',
        'logo', 'site_web', 'description', 'ifu', 'rccm',
        'is_active', 'config', 'limits',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config' => 'array',
            'limits' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(ModuleCabinet::class);
    }

    public function activeModules(): HasMany
    {
        return $this->hasMany(ModuleCabinet::class)->where('is_active', true);
    }

    public function chatConversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class);
    }
}
