<?php

$baseDir = __DIR__.'/resources/js/Pages';
if (!is_dir($baseDir)) {
    mkdir($baseDir, 0777, true);
}

$portals = [
    'SuperAdmin' => ['Dashboard', 'Entreprises', 'Utilisateurs', 'Settings'],
    'GelAdmin' => ['Dashboard', 'PoleManagement', 'Staff', 'Reports', 'Settings'],
    'GelAccountant' => ['Dashboard', 'Clients', 'Folders', 'Coordination', 'Accounting', 'Settings'],
    'GelSecretary' => ['Dashboard', 'Tasks', 'Coordination', 'Calls', 'Settings'],
    'GelCollaborator' => ['Dashboard', 'DataEntry', 'Folders', 'Settings'],
    'CompanyAdmin' => ['Dashboard', 'Employees', 'Accounting', 'Hr', 'Settings'],
    'CompanyEmployee' => ['Dashboard', 'LeaveRequests', 'Expenses', 'Payslips', 'Settings'],
    'CpaClient' => ['Dashboard', 'MyDocuments', 'MyDeclarations', 'Settings']
];

$count = 0;

$vueTemplate = <<<VUE
<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="{TITLE}" />
    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-4">{TITLE}</h1>
                    <p>Bienvenue sur le composant {TITLE}. Ce module est en cours de développement.</p>
                </div>
            </div>
        </div>
    </div>
</template>
VUE;

foreach ($portals as $portal => $pages) {
    $portalDir = $baseDir . '/' . $portal;
    if (!is_dir($portalDir)) mkdir($portalDir, 0777, true);
    
    foreach ($pages as $page) {
        $pageDir = $portalDir . '/' . $page;
        if (!is_dir($pageDir)) mkdir($pageDir, 0777, true);
        
        $filePath = $pageDir . '/Index.vue';
        if (!file_exists($filePath)) {
            $title = $portal . ' - ' . $page;
            $content = str_replace('{TITLE}', $title, $vueTemplate);
            file_put_contents($filePath, $content);
            $count++;
        }
    }
}

echo "Created {$count} Vue page components.\n";
