<?php

namespace App\Models\Invoicing;

use Illuminate\Database\Eloquent\Model;

class RecurringInvoice extends Model
{
    protected $fillable = [
        'client_id',
        'partner_id',
        'frequency', // daily, weekly, monthly, yearly
        'amount',
        'currency',
        'description',
        'next_invoice_date',
        'status' // active, paused, cancelled
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
}
