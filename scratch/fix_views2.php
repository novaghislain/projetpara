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
    'var(--super-bg)' => 'var(--gel-bg)',
    'var(--super-text)' => 'var(--gel-primary)',
    'var(--super-text-muted)' => 'var(--gel-text-muted)',
    'var(--super-primary)' => 'var(--gel-primary)',
    'var(--super-accent)' => 'var(--gel-accent)',
    'bg-white text-dark border-light' => 'border-light',
    'bg-white text-dark' => 'bg-white',
    'btn-outline-light' => 'btn-outline-secondary',
    'btn-close-white' => '',
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
