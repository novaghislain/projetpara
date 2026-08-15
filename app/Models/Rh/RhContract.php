<?php
namespace App\Models\Rh;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RhContract extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\TenantScope);
    }
}