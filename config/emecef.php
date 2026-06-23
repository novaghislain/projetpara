<?php
// =============================================================================
// FICHIER : config/emecef.php
// RÔLE    : Configuration de l'intégration e-MECeF / Sygmef (DGI Bénin)
// ÉQUIPE  : GEL Cabinet — Équipe Dev Backend
// =============================================================================
// Ce fichier centralise tous les paramètres de l'interface avec le système
// de facturation électronique certifiée de la Direction Générale des Impôts
// du Bénin (e-MECeF / Sygmef).
//
// Architecture des clés :
//   - api_token   → clé d'API DGI (stockée dans .env)
//   - nim         → Numéro d'Identification Machine (stocké dans .env)
//   - test_mode   → activé en dev, FORCÉMENT désactivé en production
//   - api_url     → point d'entrée de l'API DGI
//
// ⚠️  Ne JAMAIS commit les valeurs réelles (api_token, nim) dans ce fichier.
//     Elles doivent être dans le .env (ou .env.production).
// =============================================================================

return [
    /*
    |--------------------------------------------------------------------------
    | e-MECeF / Sygmef — API DGI Bénin
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'intégration avec le Système de Gestion des Machines
    | Électroniques Certifiées de Facturation (Sygmef) de la DGI Bénin.
    |
    | Nécessite d'être un SFE (Système de Facturation d'Entreprise) certifié DGI.
    |
    */

    // ─── Authentification API ──────────────────────────────────────────
    // Token d'API fourni par la DGI pour les appels REST.
    'api_token'   => env('EMECEF_API_TOKEN'),

    // NIM (Numéro d'Identification de la Machine) — identifiant unique
    // de l'équipement de facturation électronique.
    'nim'         => env('EMECEF_NIM'),

    // ─── Mode test / production ────────────────────────────────────────
    // En environnement 'production', test_mode est FORCÉMENT à false,
    // même si EMECEF_TEST_MODE=true dans le .env.
    // La double vérification (ici + dans EmecefService) garantit qu'aucun
    // appel de test n'atteint l'API réelle en production.
    'test_mode'   => env('EMECEF_TEST_MODE', true) && env('APP_ENV') !== 'production',

    // URL de l'API DGI — point d'entrée pour toutes les requêtes
    'api_url'     => env('EMECEF_API_URL', 'https://sygmef.impots.bj/emcf/api'),

    // ─── Types de facture ─────────────────────────────────────────────
    // Codes normalisés par la DGI pour chaque type d'opération.
    'invoice_types' => [
        'FV' => 'Facture Vente',
        'FA' => 'Facture d\'Avoir',
        'AV' => 'Avoir Vente',
        'EA' => 'Encaisse Achat',
    ],

    // ─── Groupes de taxe ──────────────────────────────────────────────
    // TaxGroups selon la réglementation béninoise en vigueur.
    'tax_groups' => [
        'A' => 'Exonéré',
        'B' => 'TVA 18% (standard)',
        'C' => 'TVA réduit',
    ],

    // ─── Modes de paiement ────────────────────────────────────────────
    // Codes acceptés par e-MECeF pour le renseignement du moyen de paiement.
    'payment_methods' => [
        'ESP' => 'Espèces',
        'MMO' => 'Mobile Money (MTN/Moov)',
        'VIR' => 'Virement bancaire',
        'CHQ' => 'Chèque',
        'CART' => 'Carte bancaire',
    ],
];
