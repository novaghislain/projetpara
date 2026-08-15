<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

echo "--- MODEL GENERATOR ---\n";

$tablesData = Schema::getTables();
$tables = array_map(fn($t) => $t['name'], $tablesData);
$namespace = 'App\\Models';
$modelsDir = __DIR__.'/app/Models';

if (!is_dir($modelsDir)) mkdir($modelsDir, 0755, true);

$relationsToBuild = []; 

foreach ($tables as $table) {
    if (in_array($table, ['migrations', 'password_reset_tokens', 'sessions', 'personal_access_tokens'])) continue;
    
    $relationsToBuild[$table] = ['hasMany' => [], 'belongsTo' => []];
    
    $foreignKeys = Schema::getForeignKeys($table);
    
    foreach ($foreignKeys as $fk) {
        $localColumn = $fk['columns'][0];
        $foreignTable = $fk['foreign_table'];
        $foreignColumn = $fk['foreign_columns'][0];
        
        $relationsToBuild[$table]['belongsTo'][] = [
            'method' => Str::camel(str_replace('_id', '', $localColumn)),
            'relatedTable' => $foreignTable,
            'foreignKey' => $localColumn,
            'ownerKey' => $foreignColumn
        ];
        
        if (!isset($relationsToBuild[$foreignTable])) {
            $relationsToBuild[$foreignTable] = ['hasMany' => [], 'belongsTo' => []];
        }
        $relationsToBuild[$foreignTable]['hasMany'][] = [
            'method' => Str::camel($table),
            'relatedTable' => $table,
            'foreignKey' => $localColumn,
            'localKey' => $foreignColumn
        ];
    }
}

// Step 2: Generate Models
$count = 0;
foreach ($tables as $table) {
    if (in_array($table, ['migrations', 'password_reset_tokens', 'sessions', 'personal_access_tokens'])) continue;
    
    $modelName = Str::studly(Str::singular($table));
    $filePath = $modelsDir . '/' . $modelName . '.php';
    
    // Check if it already exists to avoid overwriting complex logic
    if (file_exists($filePath)) {
        continue;
    }
    
    $columns = Schema::getColumnListing($table);
    $fillable = array_filter($columns, fn($col) => !in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at']));
    $fillableStr = implode("',\n        '", $fillable);
    
    $hasSoftDeletes = in_array('deleted_at', $columns);
    $softDeletesImport = $hasSoftDeletes ? "use Illuminate\Database\Eloquent\SoftDeletes;\n" : "";
    $softDeletesTrait = $hasSoftDeletes ? "    use SoftDeletes;\n\n" : "";
    
    $classBody = "<?php\n\nnamespace {$namespace};\n\nuse Illuminate\Database\Eloquent\Factories\HasFactory;\nuse Illuminate\Database\Eloquent\Model;\n{$softDeletesImport}\nclass {$modelName} extends Model\n{\n    use HasFactory;\n{$softDeletesTrait}";
    
    $classBody .= "    protected \$table = '{$table}';\n\n";
    
    if (count($fillable) > 0) {
        $classBody .= "    protected \$fillable = [\n        '{$fillableStr}'\n    ];\n\n";
    }
    
    if (isset($relationsToBuild[$table])) {
        foreach ($relationsToBuild[$table]['belongsTo'] as $rel) {
            $relatedModel = Str::studly(Str::singular($rel['relatedTable']));
            $method = $rel['method'];
            $fk = $rel['foreignKey'];
            $ok = $rel['ownerKey'];
            $classBody .= "    public function {$method}()\n    {\n        return \$this->belongsTo({$relatedModel}::class, '{$fk}', '{$ok}');\n    }\n\n";
        }
        foreach ($relationsToBuild[$table]['hasMany'] as $rel) {
            $relatedModel = Str::studly(Str::singular($rel['relatedTable']));
            $method = $rel['method'];
            $fk = $rel['foreignKey'];
            $lk = $rel['localKey'];
            $classBody .= "    public function {$method}()\n    {\n        return \$this->hasMany({$relatedModel}::class, '{$fk}', '{$lk}');\n    }\n\n";
        }
    }
    
    $classBody .= "}\n";
    
    file_put_contents($filePath, $classBody);
    $count++;
}

echo "Created {$count} missing models with relations.\n";
