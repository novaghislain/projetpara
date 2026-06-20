<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'client_id',
        'pos_session_id',
        'business_user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'subtotal',
        'discount',
        'discount_type',
        'total_ht',
        'total_ttc',
        'tax_amount',
        'paid_amount',
        'change_amount',
        'status',
        'payment_method',
        'emecef_uid',
        'emecef_response',
        'qr_code',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_ht' => 'decimal:2',
            'total_ttc' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'emecef_response' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function posSession(): BelongsTo
    {
        return $this->belongsTo(PosSession::class);
    }

    public function businessUser(): BelongsTo
    {
        return $this->belongsTo(BusinessUser::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
