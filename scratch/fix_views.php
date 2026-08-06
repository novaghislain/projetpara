<?php
$files = [
    'c:/xampp/htdocs/Para/resources/views/gel-super-admin/dashboard/index.blade.php',
    'c:/xampp/htdocs/Para/resources/views/gel-super-admin/tenants/index.blade.php',
    'c:/xampp/htdocs/Para/resources/views/gel-super-admin/tenants/show.blade.php',
    'c:/xampp/htdocs/Para/resources/views/gel-super-admin/plans/index.blade.php',
    'c:/xampp/htdocs/Para/resources/views/gel-super-admin/security/index.blade.php',
    'c:/xampp/htdocs/Para/resources/views/gel-super-admin/platform/audit.blade.php'
];

$replacements = [
    'super-card' => 'card shadow-sm border-0 h-100 p-4',
    'super-page-title' => 'page-title',
    'super-page-sub' => 'page-subtitle',
    'super-table' => 'table table-hover align-middle',
    'btn-super-primary' => 'btn btn-primary',
    'btn-outline-secondary text-white' => 'btn-outline-secondary',
    'border-secondary' => 'border-light',
    'text-white' => 'text-dark',
    'text-white-50' => 'text-muted',
    'bg-dark' => 'bg-white',
    'border-bottom-color: rgba(255,255,255,0.05);' => 'border-bottom-color: var(--gel-border);',
    'background-color: rgba(255,255,255,0.05)' => 'background-color: var(--gel-bg)',
    'rgba(255,255,255,0.05)' => 'var(--gel-border)',
    'rgba(255,255,255,0.1)' => 'var(--gel-border)',
    'style="color: var(--super-text);"' => '',
    'style="color: var(--super-text-muted);"' => 'class="text-muted"',
    'style="background-color: var(--super-bg); border: 1px solid rgba(255,255,255,0.1);"' => 'style="background-color: var(--gel-bg); border: 1px solid var(--gel-border);"',
    'style="background-color: rgba(255, 255, 255, 0.05);"' => 'style="background-color: var(--gel-bg);"'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
