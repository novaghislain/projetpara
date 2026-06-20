<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComptableClient extends Model
{
    protected $table = 'comptable_clients';

    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = false;

    protected $fillable = [
        'comptable_id',
        'client_id',
        'assigned_at',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }

    public function comptable()
    {
        return $this->belongsTo(User::class, 'comptable_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
