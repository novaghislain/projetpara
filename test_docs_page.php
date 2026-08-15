<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::create('/gel-secretary/documents', 'GET');
// Simulate a user
$user = \App\Models\User::first();
if (!$user) { echo "No users\n"; exit; }

\Illuminate\Support\Facades\Auth::login($user);

try {
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    echo "Status: $status\n";
    if ($status >= 400) {
        echo substr($response->getContent(), 0, 1000) . "\n";
    } else {
        echo "Page OK!\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . get_class($e) . "\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
