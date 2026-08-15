<?php
/**
 * Extract fillable fields from models to understand table schemas.
 */
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$models = [
    'MeetingMinute' => \App\Models\MeetingMinute::class ?? null,
    'Courrier'      => null,
    'Relance'       => null,
    'CallLog'       => null,
    'Contrat'       => null,
];

// Find model files
$modelFiles = glob(base_path('app/Models/**/*.php'));
$modelFiles = array_merge($modelFiles, glob(base_path('app/Models/*.php')));

foreach ($modelFiles as $file) {
    $content = file_get_contents($file);
    $basename = basename($file, '.php');
    
    if (preg_match('/protected \$table\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $m)) {
        $table = $m[1];
    } else {
        // Guess table from class name (snake_case plural)
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $basename));
        if (!str_ends_with($table, 's')) $table .= 's';
    }
    
    $target = ['meeting_minutes', 'meeting_minute', 'courriers', 'courrier', 'relances', 'relance', 
               'call_logs', 'call_log', 'contrats', 'contrat', 'reunions', 'reunion', 'messagerie', 'messages'];
    
    if (in_array($table, $target) || in_array(strtolower($basename), ['meetingminute','courrier','relance','calllog','contrat','reunion','message','messagerie'])) {
        echo "=== $basename (table: $table) ===\n";
        // Extract fillable
        if (preg_match('/protected \$fillable\s*=\s*\[(.*?)\];/s', $content, $fm)) {
            $fields = preg_replace('/\s+/', ' ', $fm[1]);
            echo "  Fillable: $fields\n";
        }
        echo "  File: $file\n\n";
    }
}
