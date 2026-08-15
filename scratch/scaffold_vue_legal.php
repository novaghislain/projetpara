<?php

// 1. Refactor ContractController
$contractCtrl = <<<PHP
<?php

namespace App\Http\Controllers\GelLegal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Legal\LegalContract;

class ContractController extends Controller
{
    public function index()
    {
        \$contracts = LegalContract::latest('created_at')->paginate(15);
        return view('app', [
            'page' => 'Modules/Legal/Contrats/Index',
            'props' => [
                'contracts' => \$contracts
            ]
        ]);
    }

    public function create()
    {
        return view('app', [
            'page' => 'Modules/Legal/Contrats/Form'
        ]);
    }

    public function store(Request \$request)
    {
        \$request->validate([
            'titre' => 'required|string|max:255',
            'type' => 'required|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
        ]);

        LegalContract::create([
            'titre' => \$request->titre,
            'type' => \$request->type,
            'date_debut' => \$request->date_debut,
            'date_fin' => \$request->date_fin,
            'statut' => 'actif',
        ]);

        return redirect()->route('gel-legal.contracts.index')->with('success', 'Contrat ajouté avec succès.');
    }
}
PHP;
file_put_contents(__DIR__ . '/../app/Http/Controllers/GelLegal/ContractController.php', $contractCtrl);

// 2. Refactor AssemblyController
$assemblyCtrl = <<<PHP
<?php

namespace App\Http\Controllers\GelLegal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Legal\LegalAssembly;

class AssemblyController extends Controller
{
    public function index()
    {
        \$assemblies = LegalAssembly::latest('created_at')->paginate(15);
        return view('app', [
            'page' => 'Modules/Legal/Assemblees/Index',
            'props' => [
                'assemblies' => \$assemblies
            ]
        ]);
    }
}
PHP;
file_put_contents(__DIR__ . '/../app/Http/Controllers/GelLegal/AssemblyController.php', $assemblyCtrl);

// 3. Create Vue Components
$vueDir = __DIR__ . '/../resources/js/Pages/Modules/Legal';
if (!is_dir($vueDir)) { mkdir($vueDir, 0777, true); }
if (!is_dir($vueDir . '/Contrats')) { mkdir($vueDir . '/Contrats', 0777, true); }
if (!is_dir($vueDir . '/Assemblees')) { mkdir($vueDir . '/Assemblees', 0777, true); }

$dashboardVue = <<<VUE
<template>
  <div class="legal-dashboard">
    <div class="sec-page-header">
      <h1 class="sec-page-title">Tableau de bord Juridique</h1>
      <div class="sec-page-subtitle">Vue d'ensemble de vos activités légales</div>
    </div>
    
    <div class="sec-kpi-grid">
      <div class="sec-kpi-card">
        <div class="sec-kpi-label">Dossiers Actifs</div>
        <div class="sec-kpi-value">{{ stats.dossiers_actifs }}</div>
      </div>
      <div class="sec-kpi-card">
        <div class="sec-kpi-label">Contrats Actifs</div>
        <div class="sec-kpi-value">{{ stats.contrats_actifs }}</div>
      </div>
      <div class="sec-kpi-card">
        <div class="sec-kpi-label">Assemblées Planifiées</div>
        <div class="sec-kpi-value">{{ stats.assemblees_planifiees }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stats: Object,
  recent_contracts: Array
});
</script>
VUE;
file_put_contents($vueDir . '/Dashboard.vue', $dashboardVue);

$contratsIndexVue = <<<VUE
<template>
  <div>
    <div class="sec-page-header">
      <h1 class="sec-page-title">Contrats</h1>
    </div>
    <div class="sec-card">
      <div class="sec-card-body p-0">
        <table class="sec-table">
          <thead>
            <tr>
              <th>Titre</th>
              <th>Type</th>
              <th>Date Début</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="contract in contracts.data" :key="contract.id">
              <td>{{ contract.titre }}</td>
              <td>{{ contract.type }}</td>
              <td>{{ contract.date_debut }}</td>
              <td><span class="sec-badge sec-badge-success">{{ contract.statut }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  contracts: Object
});
</script>
VUE;
file_put_contents($vueDir . '/Contrats/Index.vue', $contratsIndexVue);

$assembleesIndexVue = <<<VUE
<template>
  <div>
    <div class="sec-page-header">
      <h1 class="sec-page-title">Assemblées</h1>
    </div>
    <div class="sec-card">
      <div class="sec-card-body p-0">
        <table class="sec-table">
          <thead>
            <tr>
              <th>Type</th>
              <th>Date</th>
              <th>Lieu</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="assembly in assemblies.data" :key="assembly.id">
              <td>{{ assembly.type }}</td>
              <td>{{ assembly.date_assemblee }}</td>
              <td>{{ assembly.lieu }}</td>
              <td><span class="sec-badge sec-badge-warning">{{ assembly.statut }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  assemblies: Object
});
</script>
VUE;
file_put_contents($vueDir . '/Assemblees/Index.vue', $assembleesIndexVue);

echo "Vue files generated successfully!";
