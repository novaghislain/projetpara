<?php
$files = glob('resources/views/gel-secretary/courriers/*.blade.php');
foreach($files as $f) {
    if (basename($f) == 'index.blade.php') continue; // Already manually fixed
    $c = file_get_contents($f);
    $c = str_replace(
        ['pro-header', 'pro-title', 'pro-subtitle', 'pro-btn', 'pro-panel', 'pro-table'],
        ['sec-page-header', 'sec-page-title', 'sec-page-sub', 'sec-btn', 'sec-card', 'sec-table'],
        $c
    );
    file_put_contents($f, $c);
}
echo "REPLACED\n";
