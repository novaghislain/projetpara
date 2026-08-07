<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItEquipmentOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'informaticien_id',
        'order_number',
        'items',
        'status',
        'invoice_url',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }

    public function informaticien()
    {
        return $this->belongsTo(\App\Models\User::class, 'informaticien_id');
    }
}
