<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class CashFlowForecast extends Model
{
    protected $fillable = [
        'client_id',
        'type', // in (encaissement), out (décaissement)
        'amount',
        'expected_date',
        'description',
        'status' // pending, realized, cancelled
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
}
