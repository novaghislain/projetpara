<?php

namespace App\Models\B2b;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'tax_number', // IFU (Identifiant Fiscal Unique)
        'rccm', // Registre de Commerce
        'status' // active, inactive
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
