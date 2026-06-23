<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

abstract class LegalBaseModel extends Model
{
    /**
     * Scope pour filtrer par client.
     */
    public function scopeByClient($query, int $clientId)
    {
        // Les super admins voient toutes les données
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            return $query;
        }
        return $query->where('client_id', $clientId);
    }
}
