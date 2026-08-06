<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ClientFolder;
use Illuminate\Support\Facades\DB;

$duplicates = ClientFolder::select('name', 'client_id', DB::raw('MIN(id) as keep_id'), DB::raw('GROUP_CONCAT(id) as all_ids'))
    ->groupBy('name', 'client_id')
    ->havingRaw('COUNT(*) > 1')
    ->get();

$count = 0;
foreach ($duplicates as $dup) {
    $ids = explode(',', $dup->all_ids);
    $ids = array_diff($ids, [$dup->keep_id]);
    if (!empty($ids)) {
        DB::table('documents')->whereIn('folder_id', $ids)->update(['folder_id' => $dup->keep_id]);
        ClientFolder::whereIn('id', $ids)->delete();
        $count += count($ids);
    }
}
echo "Nettoyage effectue : $count dossiers fusionnes.\n";
