<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::first();
if ($user) {
    Auth::login($user);
}

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create(
        '/api/poles',
        'GET',
        [],
        [],
        [],
        ['HTTP_ACCEPT' => 'application/json']
    )
);

echo "STATUS: " . $response->getStatusCode() . "\n";
echo "CONTENT: " . $response->getContent() . "\n";
