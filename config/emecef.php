<?php

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

    'api_token'   => env('EMECEF_API_TOKEN'),
    'nim'         => env('EMECEF_NIM'),       // Numéro d'Identification de la Machine
    'test_mode'   => env('EMECEF_TEST_MODE', true),
    'api_url'     => env('EMECEF_API_URL', 'https://sygmef.impots.bj/emcf/api'),

    /**
     * Types de facture autorisés par la DGI.
     */
    'invoice_types' => [
        'FV' => 'Facture Vente',
        'FA' => 'Facture d\'Avoir',
        'AV' => 'Avoir Vente',
        'EA' => 'Encaisse Achat',
    ],

    /**
     * Groupes de taxe (taxGroup) selon la réglementation béninoise.
     */
    'tax_groups' => [
        'A' => 'Exonéré',
        'B' => 'TVA 18% (standard)',
        'C' => 'TVA réduit',
    ],

    /**
     * Modes de paiement acceptés par e-MECeF.
     */
    'payment_methods' => [
        'ESP' => 'Espèces',
        'MMO' => 'Mobile Money (MTN/Moov)',
        'VIR' => 'Virement bancaire',
        'CHQ' => 'Chèque',
        'CART' => 'Carte bancaire',
    ],
];
