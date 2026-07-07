<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'alice@gel.cabinet')->first();
Auth::login($user);

$request = Illuminate\Http\Request::create('/api/clients', 'GET');
$response = app()->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . substr($response->getContent(), 0, 500) . "\n";
