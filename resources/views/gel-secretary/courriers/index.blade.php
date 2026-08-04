@extends('layouts.gel-secretary')
@section('title', 'Gestion des Courriers')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">
      <i class="fas fa-envelope-open-text" style="color:var(--sec-primary); margin-right:8px;"></i>Courriers
    </h1>
    <p class="sec-page-sub">Gestion des courriers entrants et sortants pour {{ $activeClient ? $activeClient->company_name : 'tous les clients' }}</p>
  </div>
  <button type="button" class="sec-btn sec-btn-primary" onclick="document.getElementById('modalAddCourrier').style.display='flex'">
    <i class="fas fa-plus"></i> Nouveau courrier
  </button>
</div>

@if(session('success'))
<div class="alert alert-success animate-fade delay-1" style="font-size:13px; font-weight:600; padding:10px 16px; margin-bottom: 20px;">
  <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@php
$workflowSteps = \App\Models\Dae\DaeCourrier::WORKFLOW_STEPS;
$stepKeys = array_keys($workflowSteps);
$tab = $tab ?? 'en_cours';

$blockedCourriers = $courriers->filter(function($c) {
    // Dans ce modèle, l'entrée dans l'étape 'visa' met le status 'en_cours'
    // Pour simuler la date d'entrée, on prend created_at ou visa_at
    $date = $c->visa_at ? \Carbon\Carbon::parse($c->visa_at) : $c->created_at;
    return $c->workflow_step === 'visa' && $date->diffInDays(now()) >= 3;
});
@endphp

@if($blockedCourriers->count() > 0 && $tab === 'en_cours')
  <div class="alert alert-warning animate-fade" style="font-size:13px; font-weight:600; padding:12px 16px; margin-bottom: 20px; border-left:4px solid var(--sec-warning); background:#FFFBEB; color:#92400E; display:flex; align-items:center; gap:12px; border-radius:6px;">
    <div style="width:36px; height:36px; border-radius:50%; background:white; color:var(--sec-primary); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:16px; box-shadow:0 2px 4px rgba(0,0,0,0.05);">
        <i class="fas fa-magic"></i>
    </div>
    <div>
      <div style="color:var(--sec-primary); font-size:11px; font-weight:800; text-transform:uppercase; margin-bottom:2px; letter-spacing:0.5px;">Alerte IA (Analyse des Workflows)</div>
      {{ $blockedCourriers->count() }} courrier(s) bloqué(s) à l'étape "Visa" depuis plus de 3 jours. Une relance est conseillée.
    </div>
  </div>
@endif

<div style="display:flex; gap:16px; margin-bottom: 20px; border-bottom: 1px solid var(--sec-border);">
    <a href="{{ route('gel-secretary.courriers.index', ['tab' => 'en_cours']) }}" 
       style="padding: 10px 16px; font-weight: 600; font-size: 14px; text-decoration: none; color: {{ $tab === 'en_cours' ? 'var(--sec-primary)' : 'var(--sec-text-muted)' }}; border-bottom: 2px solid {{ $tab === 'en_cours' ? 'var(--sec-primary)' : 'transparent' }};">
       <i class="fas fa-inbox"></i> En cours
    </a>
    <a href="{{ route('gel-secretary.courriers.index', ['tab' => 'archives']) }}" 
       style="padding: 10px 16px; font-weight: 600; font-size: 14px; text-decoration: none; color: {{ $tab === 'archives' ? 'var(--sec-primary)' : 'var(--sec-text-muted)' }}; border-bottom: 2px solid {{ $tab === 'archives' ? 'var(--sec-primary)' : 'transparent' }};">
       <i class="fas fa-archive"></i> Archives
    </a>
</div>

<div class="sec-card animate-fade delay-2" style="border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.04);">
  <div class="sec-card-header">
    <div class="sec-card-title"><i class="fas fa-list" style="color:var(--sec-primary);margin-right:6px;"></i> Liste des courriers</div>
  </div>
  <div style="overflow-x:auto;">
    <table class="sec-table">
      <thead>
        <tr style="background:#f9fafb;">
          <th style="padding:14px 18px;">Réf. / Type</th>
          <th style="padding:14px 18px;">Objet</th>
          <th style="padding:14px 18px;">Expéditeur / Destinataire</th>
          <th style="padding:14px 18px;">Date</th>
          <th style="padding:14px 18px; min-width:280px;">🔄 Workflow</th>
          <th style="padding:14px 18px; text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($courriers as $courrier)
          @php
            $currentStep = $courrier->workflow_step ?? 'creation';
            $currentIdx  = array_search($currentStep, $stepKeys);
          @endphp
          <tr style="border-bottom:1px solid #f3f4f6;">
            <td style="padding:14px 18px;">
              <div style="font-weight:700;color:#1e293b; font-size:12px; font-family:monospace;">{{ $courrier->reference }}</div>
              @if($courrier->type == 'entrant')
                <span style="background:#FEF2F2;color:#DC2626;padding:2px 6px;border-radius:12px;font-size:11px;">Entrant</span>
              @else
                <span style="background:#F0FDF4;color:#16A34A;padding:2px 6px;border-radius:12px;font-size:11px;">Sortant</span>
              @endif
              @if(in_array($courrier->urgence, ['urgent', 'tres_urgent']))
                <span style="background:#FEF2F2;color:#DC2626;padding:2px 6px;border-radius:12px;font-size:11px;margin-left:4px;"><i class="fas fa-exclamation"></i> Urgent</span>
              @endif
            </td>
            <td style="padding:14px 18px;">
              <div style="font-weight:600;color:#334155;">{{ $courrier->objet }}</div>
              <div style="font-size:11px;color:#64748b;">{{ Str::limit($courrier->contenu, 50) }}</div>
            </td>
            <td style="padding:14px 18px;">
              @if($courrier->type == 'entrant')
                <div style="font-size:12px;"><span style="color:#94a3b8;">De :</span> {{ $courrier->expediteur }}</div>
                <div style="font-size:12px;"><span style="color:#94a3b8;">À :</span> {{ $courrier->client->company_name ?? '—' }}</div>
              @else
                <div style="font-size:12px;"><span style="color:#94a3b8;">De :</span> {{ $courrier->client->company_name ?? '—' }}</div>
                <div style="font-size:12px;"><span style="color:#94a3b8;">À :</span> {{ $courrier->destinataire }}</div>
              @endif
            </td>
            <td style="padding:14px 18px; font-size:12px; color:#64748b;">
              {{ $courrier->date_courrier ? \Carbon\Carbon::parse($courrier->date_courrier)->format('d/m/Y') : '—' }}
            </td>

            {{-- ─── WORKFLOW PROGRESS BAR ─────────────────────────────── --}}
            <td style="padding:10px 18px;">
              <div style="display:flex; align-items:center; gap:0;">
                @foreach($workflowSteps as $stepKey => $step)
                  @php
                    $stepIdx   = array_search($stepKey, $stepKeys);
                    $isDone    = $stepIdx < $currentIdx;
                    $isCurrent = $stepKey === $currentStep;
                    $color     = $isDone || $isCurrent ? $step['color'] : '#e2e8f0';
                    $txtColor  = $isDone || $isCurrent ? '#fff' : '#94a3b8';
                  @endphp
                  <div style="display:flex; align-items:center; flex:1; min-width:0;">
                    <div title="{{ $step['label'] }}"
                         style="width:28px; height:28px; border-radius:50%; background:{{ $color }}; display:flex; align-items:center; justify-content:center; flex-shrink:0; color:{{ $txtColor }}; transition:all 0.3s; {{ $isCurrent ? 'box-shadow:0 0 0 3px '.$step['color'].'44;' : '' }}">
                      <i class="fas {{ $step['icon'] }}" style="font-size:9px;"></i>
                    </div>
                    @if(!$loop->last)
                      <div style="flex:1; height:2px; background:{{ $isDone ? $step['color'] : '#e2e8f0' }}; min-width:4px;"></div>
                    @endif
                  </div>
                @endforeach
              </div>
              <div style="font-size:10px; color:{{ $workflowSteps[$currentStep]['color'] ?? '#64748b' }}; font-weight:700; margin-top:5px; text-align:center;">
                {{ $workflowSteps[$currentStep]['label'] ?? 'Création' }}
              </div>
            </td>

            <td style="padding:14px 18px; text-align:right;">
              @if($currentStep !== 'archive')
                @php
                  $nextStepIdx = $currentIdx + 1;
                  $nextStep    = $nextStepIdx < count($stepKeys) ? $stepKeys[$nextStepIdx] : null;
                @endphp
                @if($nextStep)
                  <button class="sec-btn sec-btn-primary" style="padding:6px 12px; font-size:11px; white-space:nowrap;"
                          onclick="advanceWorkflow({{ $courrier->id }}, '{{ $nextStep }}', '{{ $workflowSteps[$nextStep]['label'] }}')">
                    <i class="fas {{ $workflowSteps[$nextStep]['icon'] }}"></i>
                    → {{ $workflowSteps[$nextStep]['label'] }}
                  </button>
                @endif
              @else
                <span style="color:#10B981; font-size:11px; font-weight:700;"><i class="fas fa-check-double"></i> Terminé</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="padding:40px; text-align:center; color:#94a3b8; font-weight:600;">
              <i class="fas fa-envelope-open-text" style="font-size:32px; display:block; margin-bottom:8px; color:#cbd5e1;"></i>
              Aucun courrier trouvé pour cette entreprise.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ─── MODALE CRÉATION COURRIER ──────────────────────────────────────── --}}
<div id="modalAddCourrier" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:999999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:14px; width:680px; max-width:95%; max-height:90vh; overflow-y:auto; border:1px solid var(--sec-border); box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div style="padding:16px 20px; background:#f9fafb; border-bottom:1px solid var(--sec-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:700; color:var(--sec-text); margin:0;">Enregistrer un courrier</h3>
            <button type="button" onclick="document.getElementById('modalAddCourrier').style.display='none'" style="background:none; border:none; font-size:20px; color:var(--sec-text-muted); cursor:pointer;">&times;</button>
        </div>
        <form action="{{ route('gel-secretary.courriers.store') }}" method="POST" style="padding:20px;">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="sec-form-group">
                    <label>Entreprise *</label>
                    <select name="client_id" class="sec-form-control" required>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ ($activeClient && $activeClient->id == $c->id) ? 'selected' : '' }}>{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sec-form-group">
                    <label>Type de courrier *</label>
                    <select name="type" class="sec-form-control" required>
                        <option value="entrant">📥 Entrant (Reçu)</option>
                        <option value="sortant">📤 Sortant (À envoyer)</option>
                    </select>
                </div>
                <div class="sec-form-group">
                    <label>Expéditeur *</label>
                    <input type="text" name="expediteur" class="sec-form-control" required placeholder="Nom ou organisation">
                </div>
                <div class="sec-form-group">
                    <label>Destinataire *</label>
                    <input type="text" name="destinataire" class="sec-form-control" required placeholder="Nom ou organisation">
                </div>
                <div class="sec-form-group" style="grid-column:1/-1;">
                    <label>Objet du courrier *</label>
                    <input type="text" name="objet" class="sec-form-control" required>
                </div>
                <div class="sec-form-group">
                    <label>Date du courrier</label>
                    <input type="date" name="date_courrier" class="sec-form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="sec-form-group">
                    <label>Urgence</label>
                    <select name="urgence" class="sec-form-control">
                        <option value="normale">🟢 Normale</option>
                        <option value="urgent">🟠 Urgent</option>
                        <option value="tres_urgent">🔴 Très urgent</option>
                    </select>
                </div>
                <div class="sec-form-group" style="grid-column:1/-1;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                        <label>Contenu / Résumé</label>
                        <button type="button" class="sec-btn sec-btn-sm" style="background:#F0FDFA; color:#0D9488; border:1px solid #CCFBF1; margin-bottom:5px;" onclick="generateDraftIA()">
                            <i class="fas fa-magic"></i> Rédiger avec l'IA
                        </button>
                    </div>
                    <textarea name="contenu" id="courrierContenu" class="sec-form-control" rows="5" style="resize:vertical;"></textarea>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px; padding-top:16px; border-top:1px solid var(--sec-border);">
                <button type="button" class="sec-btn" onclick="document.getElementById('modalAddCourrier').style.display='none'" style="background:#f1f5f9; color:#475569;">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary"><i class="fas fa-paper-plane me-1"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── MODALE AVANCEMENT WORKFLOW ──────────────────────────────────────── --}}
<div id="modalWorkflow" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.5); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:14px; width:440px; max-width:90%; border:1px solid var(--sec-border); box-shadow:var(--sec-shadow); overflow:hidden;">
        <div style="padding:16px 20px; background:#f9fafb; border-bottom:1px solid var(--sec-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 id="workflowModalTitle" style="font-size:15px; font-weight:700; color:var(--sec-text); margin:0;">Avancer le workflow</h3>
            <button type="button" onclick="document.getElementById('modalWorkflow').style.display='none'" style="background:none; border:none; font-size:18px; color:var(--sec-text-muted); cursor:pointer;">&times;</button>
        </div>
        <form id="workflowForm" method="POST" style="padding:20px;">
            @csrf
            @method('PUT')
            <input type="hidden" name="workflow_step" id="workflowStepInput">
            <div id="workflowStepInfo" style="font-size:13px; color:#475569; margin-bottom:16px; padding:12px; background:#F8FAFC; border-radius:8px; border:1px solid #e2e8f0;"></div>
            <div class="sec-form-group">
                <label style="font-size:12px; font-weight:700; color:var(--sec-text-muted);">Note (optionnelle)</label>
                <textarea name="workflow_notes" class="sec-form-control" rows="2" style="resize:none;" placeholder="Commentaire sur cette étape..."></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:16px;">
                <button type="button" class="sec-btn" onclick="document.getElementById('modalWorkflow').style.display='none'" style="background:#f1f5f9; color:#475569;">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary" id="workflowSubmitBtn">Confirmer</button>
            </div>
        </form>
    </div>
</div>

<script>
function advanceWorkflow(id, step, stepLabel) {
    document.getElementById('workflowForm').action = '/gel-secretary/courriers/' + id;
    document.getElementById('workflowStepInput').value = step;
    document.getElementById('workflowModalTitle').textContent = 'Passer à : ' + stepLabel;
    document.getElementById('workflowStepInfo').innerHTML =
        '<i class="fas fa-arrow-right me-2" style="color:var(--sec-primary);"></i>Le courrier sera marqué comme <strong>' + stepLabel + '</strong>.';
    document.getElementById('workflowSubmitBtn').textContent = '✔ Confirmer : ' + stepLabel;
    document.getElementById('modalWorkflow').style.display = 'flex';
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.querySelector('select[name="type"]');
    const clientSelect = document.querySelector('select[name="client_id"]');
    const expInput = document.querySelector('input[name="expediteur"]');
    const destInput = document.querySelector('input[name="destinataire"]');

    function updateFields() {
        if (!typeSelect || !clientSelect || !expInput || !destInput) return;
        
        const type = typeSelect.value;
        const clientName = clientSelect.options[clientSelect.selectedIndex].text;

        if (type === 'entrant') {
            destInput.value = clientName;
            destInput.setAttribute('readonly', 'true');
            destInput.style.backgroundColor = '#f1f5f9';
            destInput.style.cursor = 'not-allowed';
            
            expInput.removeAttribute('readonly');
            expInput.style.backgroundColor = '#fff';
            expInput.style.cursor = 'text';
            if (expInput.value === clientName) expInput.value = '';
        } else {
            expInput.value = clientName;
            expInput.setAttribute('readonly', 'true');
            expInput.style.backgroundColor = '#f1f5f9';
            expInput.style.cursor = 'not-allowed';
            
            destInput.removeAttribute('readonly');
            destInput.style.backgroundColor = '#fff';
            destInput.style.cursor = 'text';
            if (destInput.value === clientName) destInput.value = '';
        }
    }

    if (typeSelect && clientSelect) {
        typeSelect.addEventListener('change', updateFields);
        clientSelect.addEventListener('change', updateFields);
        // Initial call
        setTimeout(updateFields, 100);
    }
});

function generateDraftIA() {
    const objet = document.querySelector('input[name="objet"]').value;
    const exp = document.querySelector('input[name="expediteur"]').value;
    const dest = document.querySelector('input[name="destinataire"]').value;
    
    if (!objet) {
        secToast("Veuillez d'abord saisir l'objet du courrier.", "warn");
        return;
    }
    
    const btn = document.querySelector('button[onclick="generateDraftIA()"]');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rédaction...';
    btn.disabled = true;
    
    fetch('{{ route("gel-secretary.courriers.draft-ia") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ 
            objet: objet,
            contexte: `Expéditeur: ${exp}. Destinataire: ${dest}.`
        })
    })
    .then(r => r.json())
    .then(data => {
        if(data.draft) {
            document.getElementById('courrierContenu').value = data.draft;
            secToast("Brouillon généré par l'IA.", "success");
        }
    })
    .catch(err => {
        secToast("Erreur lors de la génération.", "err");
    })
    .finally(() => {
        btn.innerHTML = '<i class="fas fa-magic"></i> Rédiger avec l\'IA';
        btn.disabled = false;
    });
}
</script>
@endsection
