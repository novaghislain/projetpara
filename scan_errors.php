<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);
session_start();
$_SESSION['active_client_id'] = \DB::table('clients')->value('id');

$pages = [
    '/gel-secretary/tasks',
    '/gel-secretary/documents',
    '/gel-secretary/services/business-trips',
    '/gel-secretary/courriers',
    '/gel-secretary/relances',
    '/gel-secretary/calls',
    '/gel-secretary/coordination',
    '/gel-secretary/historique',
    '/gel-secretary/notifications',
    '/gel-secretary/settings',
];

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

foreach ($pages as $path) {
    $request = \Illuminate\Http\Request::create($path, 'GET');
    $request->setLaravelSession($app->make('session')->driver());
    
    // Disable exception handling so we can catch it
    $app->instance(\Illuminate\Contracts\Debug\ExceptionHandler::class, new class extends \Illuminate\Foundation\Exceptions\Handler {
        public function __construct(){}
        public function report(\Throwable $e){}
        public function render($request, \Throwable $e) { throw $e; }
    });
    
    try {
        $response = $kernel->handle($request);
        echo "OK: $path\n";
    } catch (\Throwable $e) {
        $class = get_class($e);
        echo "ERROR on $path: [$class] " . $e->getMessage() . "\n";
    }
}
echo "\nDone.\n";
