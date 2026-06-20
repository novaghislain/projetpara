<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientModule extends Model
{
    protected $table = 'client_modules';

    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'module',
        'is_active',
        'activated_at',
        'activated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'activated_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function activator()
    {
        return $this->belongsTo(User::class, 'activated_by');
    }
}
