<?php

return [
    'roles' => [
        'super_admin' => 'Super Administrateur',
        'entity_admin' => 'Admin Entité',
        'accountant' => 'Expert-Comptable',
        'bookkeeper' => 'Comptable',
        'manager' => 'Manager',
        'sales' => 'Commercial',
        'purchases' => 'Achats',
        'viewer' => 'Lecteur',
        'employee' => 'Employé',
        'project_manager' => 'Projet Manager',
        'report_only' => 'Rapports seulement',
        'time_tracker' => 'Saisie temps',
        'expense_only' => 'Dépenses seulement'
    ],
    
    'permissions_matrix' => [
        'entite' => [
            'super_admin' => ['CREATE', 'READ', 'UPDATE', 'DELETE'],
            'entity_admin' => ['CREATE', 'READ', 'UPDATE', 'DELETE'],
            'accountant' => ['READ'],
            'bookkeeper' => [],
            'manager' => [],
            'sales' => [],
            'purchases' => [],
            'viewer' => [],
            'employee' => []
        ],
        'plan_comptable' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['ALL'],
            'accountant' => ['ALL'],
            'bookkeeper' => ['CREATE', 'READ', 'UPDATE'],
            'manager' => ['READ'],
            'sales' => [],
            'purchases' => [],
            'viewer' => ['READ'],
            'employee' => []
        ],
        'ecritures_journal' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['ALL'],
            'accountant' => ['ALL'],
            'bookkeeper' => ['CREATE', 'READ', 'UPDATE', 'DELETE'],
            'manager' => [],
            'sales' => [],
            'purchases' => [],
            'viewer' => ['READ'],
            'employee' => []
        ],
        'lettrage' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['ALL'],
            'accountant' => ['ALL'],
            'bookkeeper' => ['CREATE', 'READ', 'UPDATE', 'DELETE'],
            'manager' => [],
            'sales' => [],
            'purchases' => [],
            'viewer' => [],
            'employee' => []
        ],
        'factures_clients' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['ALL'],
            'accountant' => ['ALL'],
            'bookkeeper' => ['READ'],
            'manager' => ['CREATE', 'READ', 'UPDATE'],
            'sales' => ['ALL'],
            'purchases' => [],
            'viewer' => ['READ'],
            'employee' => []
        ],
        'factures_fournisseurs' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['ALL'],
            'accountant' => ['ALL'],
            'bookkeeper' => ['CREATE', 'READ', 'UPDATE', 'DELETE'],
            'manager' => ['CREATE', 'READ', 'UPDATE'],
            'sales' => [],
            'purchases' => ['CREATE', 'READ', 'UPDATE', 'DELETE'],
            'viewer' => ['READ'],
            'employee' => []
        ],
        'notes_de_frais' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['ALL'],
            'accountant' => ['ALL'],
            'bookkeeper' => ['CREATE', 'READ', 'UPDATE', 'DELETE'],
            'manager' => ['CREATE', 'READ', 'UPDATE'],
            'sales' => ['CREATE', 'READ', 'UPDATE'],
            'purchases' => ['CREATE', 'READ', 'UPDATE'],
            'viewer' => [],
            'employee' => ['CREATE', 'READ']
        ],
        'rapprochement_banque' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['ALL'],
            'accountant' => ['ALL'],
            'bookkeeper' => ['CREATE', 'READ', 'UPDATE', 'DELETE'],
            'manager' => ['READ'],
            'sales' => [],
            'purchases' => [],
            'viewer' => ['READ'],
            'employee' => []
        ],
        'tva_declarations' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['ALL'],
            'accountant' => ['ALL'],
            'bookkeeper' => ['CREATE', 'READ', 'UPDATE'],
            'manager' => [],
            'sales' => [],
            'purchases' => [],
            'viewer' => ['READ'],
            'employee' => []
        ],
        'paie_rh' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['ALL'],
            'accountant' => ['ALL'],
            'bookkeeper' => [],
            'manager' => ['READ'],
            'sales' => [],
            'purchases' => [],
            'viewer' => ['READ'],
            'employee' => ['READ']
        ],
        'rapports' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['ALL'],
            'accountant' => ['ALL'],
            'bookkeeper' => ['READ'],
            'manager' => ['READ'],
            'sales' => ['READ'],
            'purchases' => ['READ'],
            'viewer' => ['READ'],
            'employee' => []
        ],
        'exports' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['ALL'],
            'accountant' => ['ALL'],
            'bookkeeper' => ['CREATE', 'READ', 'UPDATE', 'DELETE'],
            'manager' => ['READ'],
            'sales' => ['READ'],
            'purchases' => ['READ'],
            'viewer' => ['READ'],
            'employee' => []
        ],
        'gestion_utilisateurs' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['ALL'],
            'accountant' => [],
            'bookkeeper' => [],
            'manager' => [],
            'sales' => [],
            'purchases' => [],
            'viewer' => [],
            'employee' => []
        ],
        'audit_logs' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['READ'],
            'accountant' => ['READ'],
            'bookkeeper' => [],
            'manager' => [],
            'sales' => [],
            'purchases' => [],
            'viewer' => [],
            'employee' => []
        ],
        'api_integrations' => [
            'super_admin' => ['ALL'],
            'entity_admin' => ['ALL'],
            'accountant' => ['READ'],
            'bookkeeper' => [],
            'manager' => [],
            'sales' => [],
            'purchases' => [],
            'viewer' => [],
            'employee' => []
        ]
    ]
];
