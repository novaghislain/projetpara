<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Configuration des widgets du dashboard (§8.15 CDC).
 * - widget_order   : ordre des widgets (drag & drop)
 * - hidden_widgets : widgets masqués
 */
class UserDashboardConfig extends Model
{
    protected $table = 'user_dashboard_config';

    protected $fillable = [
        'user_id',
        'widget_order',
        'hidden_widgets',
    ];

    protected $casts = [
        'widget_order'   => 'array',
        'hidden_widgets' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Récupère (ou crée) la config de widgets pour un utilisateur.
     */
    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId]);
    }
}
