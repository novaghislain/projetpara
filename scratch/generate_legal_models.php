<?php

$models = [
    'LegalCompanyInfo' => [
        'client_id' => 'uuid',
        'rccm' => 'string',
        'ifu' => 'string',
        'capital' => 'decimal',
        'siege_social' => 'text',
        'forme_juridique' => 'string',
    ],
    'LegalAssembly' => [
        'client_id' => 'uuid',
        'type' => 'string', // ordinaire, extraordinaire
        'date_assemblee' => 'date',
        'lieu' => 'string',
        'statut' => 'string',
        'resolutions' => 'text',
    ],
    'LegalContract' => [
        'client_id' => 'uuid',
        'titre' => 'string',
        'parties' => 'json',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'statut' => 'string',
        'montant' => 'decimal',
    ],
    'LegalContractSignature' => [
        'client_id' => 'uuid',
        'contract_id' => 'uuid',
        'signataire_name' => 'string',
        'signataire_email' => 'string',
        'signed_at' => 'timestamp',
        'ip_address' => 'string',
    ],
    'LegalLitigation' => [
        'client_id' => 'uuid',
        'reference' => 'string',
        'titre' => 'string',
        'type' => 'string',
        'nature' => 'string',
        'tribunal' => 'string',
        'montant_litige' => 'decimal',
        'prochaine_audience' => 'date',
    ],
    'LegalCompliance' => [
        'client_id' => 'uuid',
        'intitule' => 'string',
        'organisme' => 'string',
        'type' => 'string',
        'date_echeance' => 'date',
        'statut' => 'string',
    ],
    'LegalActsLibrary' => [
        'client_id' => 'uuid',
        'titre' => 'string',
        'categorie' => 'string',
        'contenu' => 'longText',
        'version' => 'integer',
    ],
    'LegalRegistre' => [
        'client_id' => 'uuid',
        'type' => 'string',
        'contenu' => 'text',
        'periode' => 'string',
    ],
    'LegalDossier' => [
        'client_id' => 'uuid',
        'titre' => 'string',
        'description' => 'text',
        'statut' => 'string',
    ],
    'LegalVeille' => [
        'client_id' => 'uuid',
        'titre' => 'string',
        'actualites' => 'text',
        'textes_loi' => 'text',
        'jurisprudence' => 'text',
    ],
    'LegalAuditLog' => [
        'client_id' => 'uuid',
        'action' => 'string',
        'user_id' => 'uuid',
        'details' => 'json',
    ]
];

$modelsDir = __DIR__ . '/../app/Models/Legal/';
if (!is_dir($modelsDir)) {
    mkdir($modelsDir, 0777, true);
}

foreach ($models as $modelName => $fields) {
    // Generate Model
    $modelContent = <<<PHP
<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class {$modelName} extends LegalBaseModel
{
    use HasFactory;

    protected \$guarded = ['id'];
}
PHP;
    file_put_contents($modelsDir . $modelName . '.php', $modelContent);

    // Generate Migration
    $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName));
    if (!str_ends_with($tableName, 's') && !str_ends_with($tableName, 'info') && !str_ends_with($tableName, 'log')) {
        $tableName .= 's';
    }
    // French plurals hacks
    if ($tableName === 'legal_assemblies') $tableName = 'legal_assemblies'; // it's okay
    if ($tableName === 'legal_litigations') $tableName = 'legal_litigations';
    
    // Check if migration exists
    $files = glob(__DIR__ . '/../database/migrations/*_create_' . $tableName . '_table.php');
    if (empty($files)) {
        $date = date('Y_m_d_His');
        $migrationPath = __DIR__ . "/../database/migrations/{$date}_create_{$tableName}_table.php";
        
        $migrationContent = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            
PHP;
        foreach ($fields as $fieldName => $type) {
            if ($fieldName === 'client_id' || $fieldName === 'user_id' || $fieldName === 'contract_id') {
                $migrationContent .= "            \$table->foreignUuid('{$fieldName}')->nullable();\n";
            } elseif ($type === 'decimal') {
                $migrationContent .= "            \$table->decimal('{$fieldName}', 15, 2)->nullable();\n";
            } else {
                $migrationContent .= "            \$table->{$type}('{$fieldName}')->nullable();\n";
            }
        }

        $migrationContent .= <<<PHP
            \$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('{$tableName}');
    }
};
PHP;
        file_put_contents($migrationPath, $migrationContent);
        echo "Created $modelName and migration for $tableName\n";
        sleep(1); // avoid duplicate timestamps
    } else {
        echo "Migration already exists for $tableName\n";
    }
}
