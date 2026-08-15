<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulate logged-in user
$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);
session_start();
$_SESSION['active_client_id'] = \DB::table('clients')->value('id');

$pages = [
    '/gel-secretary/dashboard',
    '/gel-secretary/clients',
    '/gel-secretary/conformite',
    '/gel-secretary/tasks',
    '/gel-secretary/documents',
    '/gel-secretary/contacts',
    '/gel-secretary/services/hr',
    '/gel-secretary/reunions',
    '/gel-secretary/pv',
    '/gel-secretary/services/reservations',
    '/gel-secretary/services/business-trips',
    '/gel-secretary/courriers',
    '/gel-secretary/messagerie',
    '/gel-secretary/relances',
    '/gel-secretary/calls',
    '/gel-secretary/contrats',
    '/gel-secretary/safebox',
    '/gel-secretary/coordination',
    '/gel-secretary/historique',
    '/gel-secretary/notifications',
    '/gel-secretary/settings',
];

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

foreach ($pages as $path) {
    $request = \Illuminate\Http\Request::create($path, 'GET');
    $request->setLaravelSession($app->make('session')->driver());
    
    try {
        ob_start();
        $response = $kernel->handle($request);
        ob_end_clean();
        $status = $response->getStatusCode();
        
        if ($status >= 500) {
            $content = $response->getContent();
            // Extract exception info from Laravel debug page
            preg_match('/<h1[^>]*>([^<]+)<\/h1>/', $content, $h1);
            preg_match('/class="exception-message[^"]*">([^<]+)</', $content, $msg);
            echo "ERROR $status: $path\n";
            if (!empty($h1[1])) echo "  Type: " . trim($h1[1]) . "\n";
        } else {
            echo "OK    $status: $path\n";
        }
    } catch (\Throwable $e) {
        echo "EXCEPTION: $path\n";
        echo "  " . get_class($e) . ": " . $e->getMessage() . "\n";
        echo "  at " . str_replace(base_path(), '', $e->getFile()) . ":" . $e->getLine() . "\n";
    }
}
echo "\nDone.\n";
