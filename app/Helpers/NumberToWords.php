<?php
// =============================================================================
// FICHIER : NumberToWords.php
// RÔLE    : Helper — Convertit un nombre en lettres (français)
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Utilisé pour générer les montants en toutes lettres sur les factures,
// quittances, contrats et autres documents officiels.
//
// Supporte :
//   - Nombres de -PHP_INT_MAX à PHP_INT_MAX
//   - Nombres décimaux (partie fractionnaire en lettres)
//   - Règles d'orthographe française (pluriel de cent/million, et/eu, etc.)
// =============================================================================

namespace App\Helpers;

class NumberToWords
{
    public static function toWords($number)
    {
        $hyphen      = '-';
        $conjunction = ' et ';
        $separator   = ' ';
        $negative    = 'moins ';
        $decimal     = ' virgule ';
        $dictionary  = array(
            0                   => 'zéro',
            1                   => 'un',
            2                   => 'deux',
            3                   => 'trois',
            4                   => 'quatre',
            5                   => 'cinq',
            6                   => 'six',
            7                   => 'sept',
            8                   => 'huit',
            9                   => 'neuf',
            10                  => 'dix',
            11                  => 'onze',
            12                  => 'douze',
            13                  => 'treize',
            14                  => 'quatorze',
            15                  => 'quinze',
            16                  => 'seize',
            17                  => 'dix-sept',
            18                  => 'dix-huit',
            19                  => 'dix-neuf',
            20                  => 'vingt',
            30                  => 'trente',
            40                  => 'quarante',
            50                  => 'cinquante',
            60                  => 'soixante',
            70                  => 'soixante-dix',
            71                  => 'soixante et onze',
            72                  => 'soixante-douze',
            73                  => 'soixante-treize',
            74                  => 'soixante-quatorze',
            75                  => 'soixante-quinze',
            76                  => 'soixante-seize',
            77                  => 'soixante-dix-sept',
            78                  => 'soixante-dix-huit',
            79                  => 'soixante-dix-neuf',
            80                  => 'quatre-vingts',
            90                  => 'quatre-vingt-dix',
            91                  => 'quatre-vingt-onze',
            92                  => 'quatre-vingt-douze',
            93                  => 'quatre-vingt-treize',
            94                  => 'quatre-vingt-quatorze',
            95                  => 'quatre-vingt-quinze',
            96                  => 'quatre-vingt-seize',
            97                  => 'quatre-vingt-dix-sept',
            98                  => 'quatre-vingt-dix-huit',
            99                  => 'quatre-vingt-dix-neuf',
            100                 => 'cent',
            1000                => 'mille',
            1000000             => 'million',
            1000000000          => 'milliard',
            1000000000000       => 'billion',
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            trigger_error(
                'toWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . self::toWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                
                if (isset($dictionary[$number])) {
                    $string = $dictionary[$number];
                } else {
                    $string = $dictionary[$tens];
                    if ($units) {
                        $string .= ($units == 1 ? $conjunction : $hyphen) . $dictionary[$units];
                    }
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $hundredsInt = (int)$hundreds;
                $string = ($hundredsInt == 1 ? '' : $dictionary[$hundredsInt] . ' ') . $dictionary[100];
                if ($remainder) {
                    $string .= $separator . self::toWords($remainder);
                } elseif ($hundredsInt > 1) {
                    $string .= 's'; // quatre cents
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                
                if ($baseUnit == 1000 && $numBaseUnits == 1) {
                    $string = $dictionary[$baseUnit];
                } else {
                    $string = self::toWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                    if ($baseUnit > 1000 && $numBaseUnits > 1) {
                        $string .= 's';
                    }
                }
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= self::toWords($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }
}
