<?php

namespace App\Models\Rh;

use Illuminate\Database\Eloquent\Model;

abstract class RhBaseModel extends Model
{
    /**
     * Scope pour filtrer par client.
     */
    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
