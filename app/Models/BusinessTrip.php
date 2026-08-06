<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessTrip extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'destination',
        'start_date',
        'end_date',
        'purpose',
        'status',
        'budget',
        'transport_details',
        'accommodation_details',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
