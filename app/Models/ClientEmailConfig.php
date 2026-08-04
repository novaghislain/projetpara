<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientEmailConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_username',
        'imap_password',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
    ];

    protected $casts = [
        'imap_password' => 'encrypted',
        'smtp_password' => 'encrypted',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
