<?php

namespace App\Models\B2b;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'supplier_id',
        'client_id',
        'order_number',
        'total_amount',
        'status', // draft, sent, accepted, fulfilled, cancelled
        'expected_delivery_date'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
