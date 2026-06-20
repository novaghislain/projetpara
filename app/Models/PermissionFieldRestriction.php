<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionFieldRestriction extends Model
{
    protected $table = 'permission_field_restrictions';

    protected $fillable = [
        'module',
        'action',
        'role_slug',
        'hidden_fields',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'hidden_fields' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
