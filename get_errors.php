<?php
$log = file(__DIR__.'/storage/logs/laravel.log');
$errors = [];
$capture = false;
foreach ($log as $line) {
    if (str_contains($line, 'local.ERROR')) {
        $capture = true;
        $errors[] = "\n--- ERROR ---\n";
    }
    if ($capture) {
        $errors[] = $line;
        if (str_contains($line, 'Stack trace:')) {
            $capture = false;
        }
    }
}
$recent = array_slice($errors, -100);
echo implode("", $recent);
