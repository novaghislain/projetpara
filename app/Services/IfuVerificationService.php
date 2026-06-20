<?php

namespace App\Services;

/**
 * Service de validation IFU (Identifiant Fiscal Unique) — Bénin
 *
 * L'IFU est un numéro à 9 chiffres dont les 2 derniers
 * constituent la clé de contrôle modulo 97 (norme ISO 7064).
 *
 * Algorithme :
 *   ( IFU_9_chiffres ) mod 97 === 0  → valide
 *   Sinon → invalide
 */
class IfuVerificationService
{
    /**
     * Valider un IFU Bénin complet (9 chiffres).
     */
    public function valider(string $ifu): array
    {
        $ifu = trim($ifu);

        // Format : 9 chiffres exactement
        if (!preg_match('/^\d{9}$/', $ifu)) {
            return [
                'valide'  => false,
                'erreur'  => 'L\'IFU doit contenir exactement 9 chiffres.',
                'ifu'     => $ifu,
            ];
        }

        $nombreComplet = (int) $ifu;

        // Clé ISO 7064 mod 97-10
        $reste = $nombreComplet % 97;
        $valide = $reste === 0;

        $base = (int) substr($ifu, 0, 7);
        $cle = (int) substr($ifu, 7, 2);
        $cleCalculee = (97 - ($base % 97)) % 97;

        return [
            'valide'       => $valide,
            'ifu'          => $ifu,
            'base'         => $base,
            'cle_attendue' => $cle,
            'cle_calculee' => $cleCalculee,
            'reste'        => $reste,
            'formate'      => chunk_split($ifu, 3, ' '),    // "123 456 789"
        ];
    }

    /**
     * Valide rapidement (booléen).
     */
    public function estValide(string $ifu): bool
    {
        return $this->valider($ifu)['valide'];
    }

    /**
     * Générer un IFU valide à partir d'une base de 7 chiffres.
     */
    public function generer(string $base): ?string
    {
        if (!preg_match('/^\d{7}$/', $base)) {
            return null;
        }

        $baseNum = (int) $base;
        $cle = (97 - ($baseNum % 97)) % 97;

        return $base . str_pad((string) $cle, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Extraire les informations depuis un IFU.
     */
    public function analyser(string $ifu): array
    {
        $resultat = $this->valider($ifu);
        if (!$resultat['valide']) {
            return $resultat;
        }

        // La base 7 chiffres peut contenir :
        //   - Registre de commerce (RCCM) simplifié
        //   - Identifiant Cotonou / hors Cotonou
        $prefixe = substr($ifu, 0, 1);

        return array_merge($resultat, [
            'prefixe'       => $prefixe,
            'est_cotonou'   => in_array($prefixe, ['1', '2', '3']),
            'description'   => $this->description($prefixe),
        ]);
    }

    /**
     * Description sommaire du préfixe IFU.
     */
    private function description(string $prefixe): string
    {
        return match ($prefixe) {
            '1' => 'Cotonou — Personne morale',
            '2' => 'Cotonou — Personne physique',
            '3' => 'Cotonou — Micro-entreprise',
            '4' => 'Intérieur — Personne morale',
            '5' => 'Intérieur — Personne physique',
            '6' => 'Intérieur — Micro-entreprise',
            default => 'Autre',
        };
    }
}
