<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Client;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'name',
        'purchase_date',
        'purchase_price',
        'salvage_value',
        'useful_life_years',
        'depreciation_method', // linear, declining
        'asset_account_id',
        'depreciation_account_id',
        'expense_account_id',
        'status', // active, disposed
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function depreciations()
    {
        return $this->hasMany(AssetDepreciation::class);
    }
}
