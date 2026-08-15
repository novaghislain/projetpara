<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class DataExport extends Model
{
    protected $fillable = [
        'client_id',
        'requested_by', // L'utilisateur qui demande l'export
        'module', // all, accounting, rh, crm
        'format', // csv, json, sql
        'file_path',
        'status', // pending, processing, completed, failed
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime'
    ];
}
