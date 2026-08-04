<?php

namespace App\Models\Traits;

use App\Models\Scopes\ClientScope;
use App\Models\Scopes\TenantScope;

trait ClientScopedModel
{
    /**
     * Boot the trait to add the scopes.
     */
    protected static function bootClientScopedModel()
    {
        // Add Tenant Scope (Cabinet)
        static::addGlobalScope(new TenantScope);
        
        // Add Client Scope (Entreprise)
        static::addGlobalScope(new ClientScope);
    }
}
