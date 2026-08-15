<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EntrepriseInvitation extends Model
{
    protected $table = 'entreprise_invitations';

    protected $fillable = [
        'entreprise_id',
        'invited_by_user_id',
        'email',
        'role_invite',
        'token',
        'statut',
        'expire_at',
        'acceptee_at',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
        'acceptee_at' => 'datetime',
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = Str::random(40);
            }
            if (empty($model->expire_at)) {
                $model->expire_at = now()->addDays(7);
            }
        });
    }

    public function estExpiree(): bool
    {
        return $this->expire_at && $this->expire_at->isPast();
    }
}
