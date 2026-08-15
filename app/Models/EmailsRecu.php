<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailsRecu extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'emails_recus';

    protected $fillable = [
        'user_id',
        'client_id',
        'from_email',
        'subject',
        'body',
        'est_lu',
        'received_at'
    ];

}
