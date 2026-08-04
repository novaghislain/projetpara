<?php

namespace App\Models\GelAdmin;

use Illuminate\Database\Eloquent\Model;
use App\Models\Gel\Cabinet;

class CabinetSubscriptionInvoice extends Model
{
    protected $table = 'cabinet_subscription_invoices';

    protected $fillable = [
        'cabinet_id',
        'invoice_number',
        'amount',
        'status',
        'plan_name',
        'paid_at',
        'pdf_path',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }
}
