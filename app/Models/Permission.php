<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    use HasUuids;

    protected $table = 'permissions';
    public $timestamps = false;

    protected $fillable = [
        'affectation_id',
        'ressource',
        'action',
        'autorise',
    ];

    public function affectation(): BelongsTo
    {
        return $this->belongsTo(Affectation::class, 'affectation_id');
    }
}
