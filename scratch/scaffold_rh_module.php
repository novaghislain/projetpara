<?php

// 1. Generate Models and Migrations
$models = [
    'RhEmployee' => [
        'client_id' => 'uuid',
        'first_name' => 'string',
        'last_name' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'department' => 'string',
        'position' => 'string',
        'hire_date' => 'date',
        'status' => 'string', // actif, inactif
    ],
    'RhContract' => [
        'client_id' => 'uuid',
        'rh_employee_id' => 'uuid',
        'type' => 'string', // CDI, CDD
        'start_date' => 'date',
        'end_date' => 'date',
        'salary' => 'decimal',
    ],
    'RhLeaveRequest' => [
        'client_id' => 'uuid',
        'rh_employee_id' => 'uuid',
        'type' => 'string', // annuel, maladie
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'string', // en_attente, approuve, rejete
    ],
    'RhPayslip' => [
        'client_id' => 'uuid',
        'rh_employee_id' => 'uuid',
        'month' => 'string',
        'year' => 'integer',
        'gross_salary' => 'decimal',
        'net_salary' => 'decimal',
        'its_amount' => 'decimal',
        'cnss_amount' => 'decimal',
        'status' => 'string', // brouillon, valide
    ]
];

$modelsDir = __DIR__ . '/../app/Models/Rh/';
if (!is_dir($modelsDir)) {
    mkdir($modelsDir, 0777, true);
}

foreach ($models as $modelName => $fields) {
    // Model
    $modelContent = <<<PHP
<?php
namespace App\Models\Rh;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class {$modelName} extends Model
{
    use HasFactory, HasUuids;

    protected \$guarded = ['id'];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\TenantScope);
    }
}
PHP;
    file_put_contents($modelsDir . $modelName . '.php', $modelContent);

    // Migration
    $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName)) . 's';
    
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
            if ($fieldName === 'client_id' || $fieldName === 'rh_employee_id') {
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
        sleep(1);
    }
}


// 2. Refactor Controllers
$ctrlDir = __DIR__ . '/../app/Http/Controllers/GelRh/';

$dashboardCtrl = <<<PHP
<?php
namespace App\Http\Controllers\GelRh;
use App\Http\Controllers\Controller;
use App\Models\Rh\RhEmployee;
use App\Models\Rh\RhLeaveRequest;
use App\Models\Rh\RhPayslip;

class DashboardController extends Controller
{
    public function index()
    {
        \$stats = [
            'employes_actifs' => RhEmployee::where('status', 'actif')->count(),
            'conges_en_attente' => RhLeaveRequest::where('status', 'en_attente')->count(),
            'paies_a_valider' => RhPayslip::where('status', 'brouillon')->count(),
        ];

        return view('app', [
            'page' => 'Modules/Rh/Dashboard',
            'props' => ['stats' => \$stats]
        ]);
    }
}
PHP;
file_put_contents($ctrlDir . 'DashboardController.php', $dashboardCtrl);

$leaveCtrl = <<<PHP
<?php
namespace App\Http\Controllers\GelRh;
use App\Http\Controllers\Controller;
use App\Models\Rh\RhLeaveRequest;

class LeaveRequestController extends Controller
{
    public function index()
    {
        \$leaves = RhLeaveRequest::latest('created_at')->paginate(15);
        return view('app', [
            'page' => 'Modules/Rh/Leaves/Index',
            'props' => ['leaves' => \$leaves]
        ]);
    }
}
PHP;
file_put_contents($ctrlDir . 'LeaveRequestController.php', $leaveCtrl);

$payrollCtrl = <<<PHP
<?php
namespace App\Http\Controllers\GelRh;
use App\Http\Controllers\Controller;
use App\Models\Rh\RhPayslip;

class PayrollController extends Controller
{
    public function index()
    {
        \$payslips = RhPayslip::latest('created_at')->paginate(15);
        return view('app', [
            'page' => 'Modules/Rh/Payroll/Index',
            'props' => ['payslips' => \$payslips]
        ]);
    }
}
PHP;
file_put_contents($ctrlDir . 'PayrollController.php', $payrollCtrl);

// 3. Scaffold Vue Components
$vueDir = __DIR__ . '/../resources/js/Pages/Modules/Rh';
if (!is_dir($vueDir)) { mkdir($vueDir, 0777, true); }
if (!is_dir($vueDir . '/Leaves')) { mkdir($vueDir . '/Leaves', 0777, true); }
if (!is_dir($vueDir . '/Payroll')) { mkdir($vueDir . '/Payroll', 0777, true); }

$dashboardVue = <<<VUE
<template>
  <div>
    <div class="sec-page-header">
      <h1 class="sec-page-title">Tableau de bord RH</h1>
    </div>
    <div class="sec-kpi-grid">
      <div class="sec-kpi-card">
        <div class="sec-kpi-label">Employés Actifs</div>
        <div class="sec-kpi-value">{{ stats.employes_actifs }}</div>
      </div>
      <div class="sec-kpi-card">
        <div class="sec-kpi-label">Congés en attente</div>
        <div class="sec-kpi-value">{{ stats.conges_en_attente }}</div>
      </div>
      <div class="sec-kpi-card">
        <div class="sec-kpi-label">Paies à valider</div>
        <div class="sec-kpi-value">{{ stats.paies_a_valider }}</div>
      </div>
    </div>
  </div>
</template>
<script setup>
defineProps({ stats: Object });
</script>
VUE;
file_put_contents($vueDir . '/Dashboard.vue', $dashboardVue);

$leavesVue = <<<VUE
<template>
  <div>
    <div class="sec-page-header">
      <h1 class="sec-page-title">Congés</h1>
    </div>
    <div class="sec-card">
      <div class="sec-card-body p-0">
        <table class="sec-table">
          <thead>
            <tr>
              <th>Type</th>
              <th>Début</th>
              <th>Fin</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="leave in leaves.data" :key="leave.id">
              <td>{{ leave.type }}</td>
              <td>{{ leave.start_date }}</td>
              <td>{{ leave.end_date }}</td>
              <td><span class="sec-badge">{{ leave.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script setup>
defineProps({ leaves: Object });
</script>
VUE;
file_put_contents($vueDir . '/Leaves/Index.vue', $leavesVue);

$payrollVue = <<<VUE
<template>
  <div>
    <div class="sec-page-header">
      <h1 class="sec-page-title">Paie</h1>
    </div>
    <div class="sec-card">
      <div class="sec-card-body p-0">
        <table class="sec-table">
          <thead>
            <tr>
              <th>Mois/Année</th>
              <th>Salaire Brut</th>
              <th>ITS</th>
              <th>Net à payer</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="payslip in payslips.data" :key="payslip.id">
              <td>{{ payslip.month }}/{{ payslip.year }}</td>
              <td>{{ payslip.gross_salary }}</td>
              <td>{{ payslip.its_amount }}</td>
              <td>{{ payslip.net_salary }}</td>
              <td><span class="sec-badge">{{ payslip.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script setup>
defineProps({ payslips: Object });
</script>
VUE;
file_put_contents($vueDir . '/Payroll/Index.vue', $payrollVue);

echo "RH Module Scaffolded successfully!\n";
