<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceSequence extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'prefix', 'year', 'next_number'];

    public static function getNextNumber(int $clientId, string $prefix, ?int $year = null): string
    {
        $year = $year ?? now()->year;

        $sequence = static::firstOrCreate(
            ['client_id' => $clientId, 'prefix' => $prefix, 'year' => $year],
            ['next_number' => 1]
        );

        $number = $sequence->next_number;

        $sequence->increment('next_number');

        return $prefix . '-' . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
