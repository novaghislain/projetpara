<?php

$time = time();

// Migration 1: add_centre_impots_to_clients_table
$mig1 = date('Y_m_d_His', $time) . '_add_centre_impots_to_clients_table.php';
$content1 = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint \$table) {
            \$table->string('centre_impots_rattachement')->nullable();
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint \$table) {
            \$table->dropColumn('centre_impots_rattachement');
        });
    }
};
PHP;
file_put_contents(__DIR__ . '/../database/migrations/' . $mig1, $content1);

// Migration 2: create_regles_fiscales_table
$mig2 = date('Y_m_d_His', $time + 1) . '_create_regles_fiscales_table.php';
$content2 = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('regles_fiscales', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            \$table->string('code_pays');
            \$table->string('type_impot');
            \$table->decimal('taux', 15, 4);
            \$table->json('conditions')->nullable();
            \$table->date('date_debut_validite');
            \$table->date('date_fin_validite')->nullable();
            \$table->string('source_reglementaire')->nullable();
            \$table->integer('version')->default(1);
            \$table->string('statut')->default('active'); // active, inactive
            \$table->unsignedBigInteger('cree_par')->nullable();
            
            \$table->timestamps();
            \$table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('regles_fiscales');
    }
};
PHP;
file_put_contents(__DIR__ . '/../database/migrations/' . $mig2, $content2);

echo "Migrations created!\n";
