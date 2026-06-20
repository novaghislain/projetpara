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
        return $query->where('client_id', $clientId);
    }
}
