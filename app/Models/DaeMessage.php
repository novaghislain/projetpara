<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeMessage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_messages';

    protected $fillable = [
        'client_id',
        'sender_id',
        'sender_type',
        'contenu',
        'est_lu'
    ];

}
