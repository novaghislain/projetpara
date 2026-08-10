<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Journal d'exécution du job `folders:calendar`.
 * Une ligne par passe + périmètre : ok | created | failure.
 */
class FolderRolloverLog extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'scope_label',
        'year',
        'month',
        'status',
        'note',
        'run_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}