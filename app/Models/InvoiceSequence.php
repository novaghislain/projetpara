<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle InvoiceSequence (Séquence de numérotation des factures).
 *
 * Gère la numérotation automatique des factures par client, préfixe
 * et année. Chaque combinaison client/préfixe/année maintient un compteur
 * incrémental pour générer des numéros uniques et séquentiels.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $prefix Préfixe (ex: FAC, AV, ND)
 * @property int $year Année fiscale
 * @property int $next_number Prochain numéro à attribuer
 */
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
