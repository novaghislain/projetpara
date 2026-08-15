<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affectation extends Model
{
    use HasUuids;

    protected $table = 'affectations';

    protected $fillable = [
        'utilisateur_id',
        'entreprise_id',
        'role_id',
        'modele',
        'statut',
        'expire_le',
        'cree_le',
    ];

    const CREATED_AT = 'cree_le';
    const UPDATED_AT = null;

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'affectation_id');
    }
}
