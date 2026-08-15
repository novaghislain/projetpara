<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

abstract class LegalBaseModel extends Model
{
    use HasUuids;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\TenantScope);
    }
}
