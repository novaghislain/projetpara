@extends('layouts.gel-secretary')
@section('title', 'Gestion des Procès-Verbaux')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">
      <i class="fas fa-file-signature" style="color:var(--sec-primary); margin-right:8px;"></i>Procès-Verbaux
    </h1>
    <p class="sec-page-sub">Comptes-rendus de réunions pour {{ $activeClient ? $activeClient->company_name : 'tous les clients' }}</p>
  </div>
  <button type="button" class="sec-btn sec-btn-primary" onclick="document.getElementById('modalAddPV').style.display='flex'">
    <i class="fas fa-plus"></i> Nouveau PV
  </button>
</div>

@if(session('success'))
<div class="alert alert-success animate-fade delay-1" style="font-size:13px; font-weight:600; padding:10px 16px; margin-bottom: 20px;">
  <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="sec-card animate-fade delay-2" style="border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.04);">
  <div class="sec-card-header">
    <div class="sec-card-title"><i class="fas fa-list" style="color:var(--sec-primary);margin-right:6px;"></i> Liste des Procès-Verbaux</div>
  </div>
  <div style="overflow-x:auto;">
    <table class="sec-table">
      <thead>
        <tr style="background:#f9fafb;">
          <th style="padding:14px 18px;">Titre / Entreprise</th>
          <th style="padding:14px 18px;">Date de Réunion</th>
          <th style="padding:14px 18px;">Participants</th>
          <th style="padding:14px 18px;">Statut</th>
          <th style="padding:14px 18px; text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($pvs as $pv)
          <tr style="border-bottom:1px solid #f3f4f6;">
            <td style="padding:14px 18px;">
              <div style="font-weight:700;color:#1e293b;">{{ $pv->titre }}</div>
              <div style="font-size:11px;color:#475569;">{{ $pv->client->company_name ?? '—' }}</div>
            </td>
            <td style="padding:14px 18px;">
              <div style="font-weight:600;color:#334155;">{{ \Carbon\Carbon::parse($pv->date_reunion)->format('d/m/Y') }}</div>
              <div style="font-size:11px;color:#64748b;">
                @if($pv->heure_debut)
                  {{ \Carbon\Carbon::parse($pv->heure_debut)->format('H:i') }} - {{ $pv->heure_fin ? \Carbon\Carbon::parse($pv->heure_fin)->format('H:i') : '?' }}
                @else
                  —
                @endif
              </div>
            </td>
            <td style="padding:14px 18px;">
              @if(is_array($pv->participants))
                <span style="background:#F3F4F6;color:#4B5563;padding:4px 8px;border-radius:12px;font-size:12px;">{{ count($pv->participants) }} participant(s)</span>
              @else
                —
              @endif
            </td>
            <td style="padding:14px 18px;">
              @if($pv->statut == 'brouillon')
                <span style="background:#F3F4F6;color:#4B5563;padding:4px 8px;border-radius:12px;font-size:12px;">Brouillon</span>
              @elseif($pv->statut == 'en_attente_signature')
                <span style="background:#FFFBEB;color:#D97706;padding:4px 8px;border-radius:12px;font-size:12px;">En attente de signature</span>
              @elseif($pv->statut == 'signe_archive')
                <span style="background:#DCFCE7;color:#16A34A;padding:4px 8px;border-radius:12px;font-size:12px;"><i class="fas fa-lock"></i> Signé / Archivé</span>
              @endif
            </td>
            <td style="padding:14px 18px; text-align:right;">
              <div style="display:flex; gap:8px; justify-content:flex-end; align-items:center;">
                @if($pv->statut === 'signe_archive')
                  @php
                    $decisionCount = is_array($pv->decisions) ? count(array_filter($pv->decisions)) : 0;
                  @endphp
                  @if($decisionCount > 0)
                    <span style="font-size:11px; background:#EFF6FF; color:#2563EB; padding:3px 8px; border-radius:8px;">
                        <i class="fas fa-tasks"></i> {{ $decisionCount }} tâche(s)
                    </span>
                  @endif
                  <span style="color:#94a3b8; font-size:12px;"><i class="fas fa-lock"></i> Verrouillé</span>
                @else
                  <button class="sec-btn" style="background:#f1f5f9; color:#475569; padding:6px 12px; font-size:12px;" onclick="updateStatus({{ $pv->id }}, '{{ $pv->statut }}')">
                    <i class="fas fa-edit"></i> État
                  </button>
                @endif
              </div>
            </td>

          </tr>
        @empty
          <tr>
            <td colspan="5" style="padding:40px; text-align:center; color:#94a3b8; font-weight:600;">
              <i class="fas fa-file-signature" style="font-size:32px; display:block; margin-bottom:8px; color:#cbd5e1;"></i>
              Aucun procès-verbal trouvé pour cette entreprise.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
/* Custom styling for TomSelect to match your theme */
.ts-control { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; font-size: 13px; box-shadow: none; }
.ts-control.focus { border-color: var(--sec-primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
</style>

<!-- Modal Ajout -->
<div id="modalAddPV" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.4); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:14px; width:700px; max-width:95%; max-height:90vh; overflow-y:auto; border:1px solid var(--sec-border); box-shadow:var(--sec-shadow);">
        <div style="padding:16px 20px; background:#f9fafb; border-bottom:1px solid var(--sec-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:700; color:var(--sec-text); margin:0;">Nouveau Procès-Verbal</h3>
            <button type="button" onclick="document.getElementById('modalAddPV').style.display='none'" style="background:none; border:none; font-size:20px; color:var(--sec-text-muted); cursor:pointer;">&times;</button>
        </div>
        <form action="{{ route('gel-secretary.pv.store') }}" method="POST" style="padding:20px;">
            @csrf
            
            <div class="row g-3" style="display:flex; flex-wrap:wrap; margin:-10px;">
                
                <!-- Saisie Rapide IA -->
                <div class="col-md-12" style="padding:10px; width:100%;">
                    <div style="background:#F0FDFA; border:1px solid #99F6E4; padding:16px; border-radius:8px; margin-bottom:12px;">
                        <label style="display:flex; justify-content:space-between; align-items:center; font-size:13px; font-weight:700; color:#0D9488; margin-bottom:8px;">
                            <span><i class="fas fa-magic"></i> Saisie Rapide IA</span>
                            <button type="button" onclick="extractPvIA()" class="sec-btn" style="background:#0D9488; color:white; padding:4px 12px; font-size:11px;">
                                <i class="fas fa-bolt"></i> Extraire
                            </button>
                        </label>
                        <textarea id="aiPvInput" rows="3" style="width:100%; border:1px solid #CCFBF1; border-radius:6px; padding:8px 12px; font-size:13px;" placeholder="Collez ici les notes brutes de la réunion pour pré-remplir le formulaire avec l'IA..."></textarea>
                        <div id="aiPvLoading" style="display:none; color:#0D9488; font-size:12px; margin-top:8px;">
                            <i class="fas fa-spinner fa-spin"></i> L'IA analyse vos notes...
                        </div>
                    </div>
                </div>

                <div class="col-md-12" style="padding:10px; width:100%;">
                    <div class="sec-form-group">
                        <label>Titre de la réunion *</label>
                        <input type="text" name="titre" class="sec-form-control" required placeholder="Ex: Assemblée Générale Ordinaire">
                    </div>
                </div>
                <div class="col-md-6" style="padding:10px; width:50%;">
                    <div class="sec-form-group">
                        <label>Entreprise concernée *</label>
                        <select name="client_id" class="sec-form-control" required>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ ($activeClient && $activeClient->id == $c->id) ? 'selected' : '' }}>{{ $c->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6" style="padding:10px; width:50%;">
                    <div class="sec-form-group">
                        <label>Lieu</label>
                        <input type="text" name="lieu" class="sec-form-control">
                    </div>
                </div>
                
                <div class="col-md-4" style="padding:10px; width:33.33%;">
                    <div class="sec-form-group">
                        <label>Date *</label>
                        <input type="date" name="date_reunion" class="sec-form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-md-4" style="padding:10px; width:33.33%;">
                    <div class="sec-form-group">
                        <label>Heure début</label>
                        <input type="time" name="heure_debut" class="sec-form-control">
                    </div>
                </div>
                <div class="col-md-4" style="padding:10px; width:33.33%;">
                    <div class="sec-form-group">
                        <label>Heure fin</label>
                        <input type="time" name="heure_fin" class="sec-form-control">
                    </div>
                </div>
                
                <div class="col-md-12" style="padding:10px; width:100%;">
                    <div class="sec-form-group">
                        <label>Participants (1 par ligne)</label>
                        <textarea name="participants" class="sec-form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="col-md-12" style="padding:10px; width:100%;">
                    <div class="sec-form-group">
                        <label>Ordre du jour (1 par ligne)</label>
                        <textarea name="ordre_du_jour" class="sec-form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="col-md-12" style="padding:10px; width:100%;">
                    <div class="sec-form-group">
                        <label style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                            <span>📌 Décisions / Actions à suivre</span>
                            <button type="button" onclick="addDecision()" style="background:var(--sec-primary);color:white;border:none;padding:4px 10px;border-radius:6px;font-size:11px;cursor:pointer;">
                                <i class="fas fa-plus"></i> Ajouter
                            </button>
                        </label>
                        <div id="decisionsContainer" style="display:flex; flex-direction:column; gap:10px;">
                            {{-- Decisions added by JS --}}
                        </div>
                        <div style="font-size:11px;color:#3B82F6;margin-top:8px; padding:8px 12px; background:#EFF6FF; border-radius:6px; border:1px solid #BFDBFE;">
                            <i class="fas fa-magic me-1"></i>
                            <strong>Assistant de tâches :</strong> à la signature du PV, chaque décision génère automatiquement une tâche assignée au responsable désigné.
                        </div>
                    </div>
                    <input type="hidden" name="decisions" id="decisionsHidden">
                </div>

            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px; padding-top:16px; border-top:1px solid var(--sec-border);">
                <button type="button" class="sec-btn" onclick="document.getElementById('modalAddPV').style.display='none'" style="background:#f1f5f9; color:#475569;">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary">Créer le brouillon</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Update Status -->
<div id="modalUpdateStatus" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.4); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:14px; width:450px; max-width:90%; border:1px solid var(--sec-border); box-shadow:var(--sec-shadow);">
        <div style="padding:16px 20px; background:#f9fafb; border-bottom:1px solid var(--sec-border); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:14px; font-weight:700; color:var(--sec-text); margin:0;">Changer l'état du PV</h3>
            <button type="button" onclick="document.getElementById('modalUpdateStatus').style.display='none'" style="background:none; border:none; font-size:18px; color:var(--sec-text-muted); cursor:pointer;">&times;</button>
        </div>
        <form id="formUpdateStatus" method="POST" style="padding:20px;">
            @csrf
            @method('PUT')
            
            <div class="sec-form-group">
                <label>Nouvel état</label>
                <select name="statut" id="statut_input" class="sec-form-control" onchange="toggleSignature()" required>
                    <option value="brouillon">Brouillon</option>
                    <option value="en_attente_signature">En attente de signature</option>
                    <option value="signe_archive">Signé / Archivé (Définitif)</option>
                </select>
            </div>
            
            <div id="signature_section" style="display:none; margin-top:16px;">
                <label style="font-weight:700; color:var(--sec-text); font-size:13px; display:block; margin-bottom:8px;">Signature numérique *</label>
                <div style="border:1px dashed #cbd5e1; border-radius:8px; background:#fff; position:relative;">
                    <canvas id="signature_pad" style="width:100%; height:150px; display:block; border-radius:8px; touch-action:none;"></canvas>
                    <button type="button" onclick="signaturePad.clear()" style="position:absolute; top:8px; right:8px; background:#f1f5f9; color:#64748b; border:none; padding:4px 8px; border-radius:4px; font-size:11px; cursor:pointer;"><i class="fas fa-eraser"></i> Effacer</button>
                </div>
                <input type="hidden" name="signature_data" id="signature_data">
            </div>
            
            <div style="margin-top:12px; font-size:12px; color:#D97706; background:#FFFBEB; padding:10px; border-radius:6px; border:1px solid #FDE68A;">
                <i class="fas fa-info-circle"></i> Le passage en "Signé / Archivé" verrouillera le PV en écriture.
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
                <button type="button" class="sec-btn" onclick="document.getElementById('modalUpdateStatus').style.display='none'" style="background:#f1f5f9; color:#475569;">Annuler</button>
                <button type="button" class="sec-btn sec-btn-primary" onclick="submitStatusForm()">Confirmer</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
let signaturePad;

function toggleSignature() {
    const status = document.getElementById('statut_input').value;
    const sigSection = document.getElementById('signature_section');
    if (status === 'signe_archive') {
        sigSection.style.display = 'block';
        // Resize canvas correctly when it becomes visible
        setTimeout(() => {
            const canvas = document.getElementById('signature_pad');
            const ratio =  Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }, 100);
    } else {
        sigSection.style.display = 'none';
    }
}

function submitStatusForm() {
    const status = document.getElementById('statut_input').value;
    if (status === 'signe_archive') {
        if (signaturePad.isEmpty()) {
            secToast('Veuillez apposer votre signature numérique avant de valider.', 'warn');
            return;
        }
        document.getElementById('signature_data').value = signaturePad.toDataURL();
    }
    document.getElementById('formUpdateStatus').submit();
}

function updateStatus(id, currentStatus) {
    document.getElementById('formUpdateStatus').action = '/gel-secretary/pv/' + id;
    document.getElementById('statut_input').value = currentStatus;
    toggleSignature();
    document.getElementById('modalUpdateStatus').style.display = 'flex';
}

let decisionCount = 0;

function addDecision(actionText = '', responsableText = '', echeanceDate = '') {
    decisionCount++;
    const idx = decisionCount;
    const container = document.getElementById('decisionsContainer');
    const div = document.createElement('div');
    div.id = 'decision-' + idx;
    div.style.cssText = 'background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px; position:relative;';
    div.innerHTML = `
        <button type="button" onclick="document.getElementById('decision-${idx}').remove(); syncDecisions()"
            style="position:absolute;top:8px;right:8px;background:none;border:none;color:#94a3b8;cursor:pointer;font-size:16px;" title="Supprimer">&times;</button>
        <div style="display:flex; flex-direction:column; gap:8px;">
            <div>
                <label style="font-size:11px;font-weight:700;color:#475569;display:block;margin-bottom:3px;">Décision / Action *</label>
                <input type="text" placeholder="Ex: Préparer le rapport financier T2" value="${actionText.replace(/"/g, '&quot;')}"
                    style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;outline:none;box-sizing:border-box;"
                    oninput="syncDecisions()">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                <div>
                    <label style="font-size:11px;font-weight:700;color:#475569;display:block;margin-bottom:3px;"><i class="fas fa-user"></i> Responsable</label>
                    <input type="text" placeholder="Ex: M. Durand" value="${responsableText.replace(/"/g, '&quot;')}"
                        style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;outline:none;box-sizing:border-box;"
                        oninput="syncDecisions()">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#475569;display:block;margin-bottom:3px;"><i class="fas fa-calendar"></i> Échéance</label>
                    <input type="date" value="${echeanceDate}"
                        style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;outline:none;box-sizing:border-box;"
                        onchange="syncDecisions()">
                </div>
            </div>
        </div>
    `;
    container.appendChild(div);
    syncDecisions();
}

function syncDecisions() {
    const items = document.querySelectorAll('#decisionsContainer > div');
    const data = [];
    items.forEach(item => {
        const inputs = item.querySelectorAll('input');
        if (inputs[0] && inputs[0].value.trim()) {
            data.push({
                action: inputs[0].value.trim(),
                responsable: inputs[1] ? inputs[1].value.trim() : '',
                echeance: inputs[2] ? inputs[2].value : ''
            });
        }
    });
    document.getElementById('decisionsHidden').value = JSON.stringify(data);
}

function extractPvIA() {
    const text = document.getElementById('aiPvInput').value;
    if (!text.trim()) {
        secToast("Veuillez coller des notes avant d'extraire.", "warn");
        return;
    }
    
    document.getElementById('aiPvLoading').style.display = 'block';
    
    let formData = new FormData();
    formData.append('text', text);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch("{{ route('gel-secretary.pv.extract-ia') }}", {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('aiPvLoading').style.display = 'none';
        if (data.error) throw new Error(data.error);
        
        // Fill standard fields
        if (data.titre) document.querySelector('input[name="titre"]').value = data.titre;
        if (data.lieu) document.querySelector('input[name="lieu"]').value = data.lieu;
        if (data.date_reunion) document.querySelector('input[name="date_reunion"]').value = data.date_reunion;
        if (data.heure_debut) document.querySelector('input[name="heure_debut"]').value = data.heure_debut;
        if (data.heure_fin) document.querySelector('input[name="heure_fin"]').value = data.heure_fin;
        
        if (data.participants && Array.isArray(data.participants)) {
            document.querySelector('textarea[name="participants"]').value = data.participants.join('\n');
        }
        
        if (data.ordre_du_jour && Array.isArray(data.ordre_du_jour)) {
            document.querySelector('textarea[name="ordre_du_jour"]').value = data.ordre_du_jour.join('\n');
        }
        
        // Fill decisions
        if (data.decisions && Array.isArray(data.decisions)) {
            document.getElementById('decisionsContainer').innerHTML = ''; // Clear existing
            decisionCount = 0;
            data.decisions.forEach(dec => {
                addDecision(dec.action, dec.responsable, dec.echeance);
            });
        }
        
        secToast("Extraction IA terminée ! Veuillez vérifier les champs.", "success");
    })
    .catch(err => {
        console.error(err);
        document.getElementById('aiPvLoading').style.display = 'none';
        secToast("Erreur lors de l'extraction IA.", "error");
    });
}

// Auto-add one blank decision slot on modal open
document.addEventListener('DOMContentLoaded', function() {
    // Init TomSelect for the company dropdown
    const clientSelect = document.querySelector('select[name="client_id"]');
    if(clientSelect) {
        new TomSelect(clientSelect, {
            create: false,
            sortField: { field: "text", direction: "asc" }
        });
    }

    // Init Signature Pad
    const canvas = document.getElementById('signature_pad');
    if (canvas) {
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)',
            penColor: 'rgb(15, 23, 42)'
        });
    }

    document.querySelector('[onclick*="modalAddPV"]')?.addEventListener('click', function() {
        if (decisionCount === 0) addDecision();
    });
});
</script>
@endsection

