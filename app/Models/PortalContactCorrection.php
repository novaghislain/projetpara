<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalContactCorrection extends Model
{
    protected $fillable = [
        'portal_contact_id',
        'client_id',
        'field_name',
        'old_value',
        'new_value',
        'status',
        'proposed_by_user_id',
    ];

    public function contact()
    {
        return $this->belongsTo(PortalContact::class, 'portal_contact_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function proposedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'proposed_by_user_id');
    }
