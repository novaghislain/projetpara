<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
