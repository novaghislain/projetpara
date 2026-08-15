<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Signet/favori personnalisable de la sidebar (§8.14 CDC).
 */
class UserBookmark extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'url',
        'icon',
        'sort_order',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
