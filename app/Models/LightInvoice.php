<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LightInvoice extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'type',
        'invoice_number',
        'partner_name',
        'date',
        'due_date',
        'amount_ht',
        'tva',
        'amount_ttc',
        'status',
        'attachment_path',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'amount_ht' => 'decimal:2',
        'tva' => 'decimal:2',
        'amount_ttc' => 'decimal:2',
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
