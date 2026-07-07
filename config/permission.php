<?php

return [
    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    'table_names' => [
        'roles' => 'gel_roles',
        'permissions' => 'gel_permissions',
        'model_has_permissions' => 'gel_model_has_permissions',
        'model_has_roles' => 'gel_model_has_roles',
        'role_has_permissions' => 'gel_role_has_permissions',
    ],

    'column_names' => [
        'role_pivot_key' => null,
        'permission_pivot_key' => null,
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'cabinet_id',
    ],

    'register_permission_check_method' => true,

    'teams' => true,

    'team_resolver' => \App\Resolvers\CabinetTeamResolver::class,

    'team_foreign_key' => 'cabinet_id',

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => null,
    ],

    'messages' => [
        'unauthorized' => 'Vous n\'avez pas les droits nécessaires pour effectuer cette action.',
        'cross_tenant' => 'Accès refusé : vous ne pouvez pas accéder aux données d\'un autre cabinet.',
        'permissions_updated' => 'Permissions mises à jour avec succès.',
        'role_not_found' => 'Le rôle demandé n\'existe pas.',
        'user_not_found' => 'L\'utilisateur demandé n\'existe pas.',
        'tenant_mismatch' => 'Cette ressource n\'appartient pas à votre cabinet.',
        'account_inactive' => 'Votre compte a été désactivé. Contactez l\'administrateur.',
        'session_expired' => 'Votre session a expiré. Veuillez vous reconnecter.',
    ],
];
