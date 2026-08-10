<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyPayment extends Model
{
    protected $fillable = [
        'invoice_id',
        'date',
        'amount',
        'method',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CompanyInvoice::class, 'invoice_id');
    }
}
