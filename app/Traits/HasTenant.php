<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;

trait HasTenant
{
    /**
     * Boot du trait : applique le global scope tenant
     */
    protected static function bootHasTenant(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    /**
     * Scope pour ignorer le filtre tenant (Super Admin uniquement)
     */
    public function scopeAllTenants(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }

    /**
     * Scope pour filtrer manuellement par tenant
     */
    public function scopeForTenant(Builder $query, int $tenantId, string $field = 'client_id'): Builder
    {
        return $query->where($field, $tenantId);
    }
}
