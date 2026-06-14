<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalAccount extends Model
{
    protected $table = 'internal_accounts';

    protected $fillable = [
        'type',
        'name',
        'email_connexion',
        'email_imap',
        'modules_json',
        'actif',
        'created_by_super_admin',
    ];

    protected $casts = [
        'modules_json' => 'array',
    ];

    /**
     * Get the transmissions sent by this account.
     */
    public function transmissionsSent(): HasMany
    {
        return $this->hasMany(TransmissionInterne::class, 'transmis_par_id');
    }

    /**
     * Get the transmissions received by this account.
     */
    public function transmissionsReceived(): HasMany
    {
        return $this->hasMany(TransmissionInterne::class, 'transmis_a_id');
    }
}
