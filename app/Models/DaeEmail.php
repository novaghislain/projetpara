<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeEmail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_emails';

    protected $fillable = [
        'client_id',
        'user_id',
        'from_email',
        'to_email',
        'subject',
        'body',
        'direction',
        'est_lu',
        'received_at'
    ];

}
