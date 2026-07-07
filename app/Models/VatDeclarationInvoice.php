<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VatDeclarationInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'declaration_id', 'invoice_id',
        'type', 'base_amount', 'vat_amount',
    ];

    public function declaration(): BelongsTo
    {
        return $this->belongsTo(VatDeclaration::class, 'declaration_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
