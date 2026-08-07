@extends('layouts.gel-secretary')
@section('title', 'Réunions — Secrétariat')

@section('content')
<style>
  @keyframes fadeUp { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
  .animate-fade { animation:fadeUp .3s ease-out; }
  .rn-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-top:20px; }
  .rn-tab { padding:10px 16px; border-radius:10px; text-decoration:none; font-weight:600; font-size:13px;
            display:inline-flex; align-items:center; gap:8px; background:#fff; color:var(--sec-text);
            border:1px solid var(--sec-border); cursor:pointer; transition:.2s; }
  .rn-tab:hover { background:#F8FAFC; }
  .rn-tab.active { background:var(--sec-primary); color:#fff; border-color:var(--sec-primary); }
  .rn-panel { display:none; margin-top:16px; }
  .rn-panel.active { display:block; animation:fadeUp .3s ease-out; }
  .panel-card { background:#fff; border:1px solid var(--sec-border); border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.02); }
  .list-row { display:flex; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid var(--sec-border); }
  .list-row:last-child { border-bottom:none; }
  .list-row:hover { background:#F8FAFC; }
  .empty-state { padding:40px; text-align:center; color:var(--sec-text-muted); font-size:13px; }
  .tab-section-header { padding:10px 16px; background:#F8FAFC; border-bottom:1px solid var(--sec-border); font-size:11px; font-weight:700; color:var(--sec-text-muted); text-transform:uppercase; }
  .b-en-cours { background:#FFF7ED; color:#D97706; border:1px solid #FDE68A; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:600; }
  .b-retard { background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:600; }
  .b-termine { background:#ECFDF5; color:#059669; border:1px solid #A7F3D0; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:600; }
  .b-attente { background:#F3F4F6; color:#4B5563; border:1px solid #E2E8F0; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:600; }
</style>

<div class="sec-page-header">
  <div>
    <div class="sec-page-title"><i class="fas fa-users" style="color:var(--sec-primary);margin-right:8px;"></i>Réunions</div>
    <div class="sec-page-sub">Ordres du jour, procès-verbaux, décisions et suivi des plans d'action</div>
  </div>
</div>

@if($activeClient)
<div style="margin-bottom:14px;">
  <span class="sec-badge" style="background:#EFF6FF;color:#2563EB;border:1px solid #BFDBFE;padding:6px 12px;">
    <i class="fas fa-building"></i> Contexte : {{ $activeClient->nom_entreprise }}
  </span>
</div>
@endif

{{-- ─── Onglets Réunions (S6) ─────────────────────────────────────── --}}
<div class="rn-tabs animate-fade">
  <button class="rn-tab active" data-panel="calendrier"><i class="fas fa-calendar-alt"></i> Calendrier</button>
  <button class="rn-tab" data-panel="odj"><i class="fas fa-list-check"></i> Ordres du jour <span class="rn-count">{{ $ordresDuJour->count() }}</span></button>
  <button class="rn-tab" data-panel="pv"><i class="fas fa-file-signature"></i> Procès-verbaux <span class="rn-count">{{ $pvs->count() }}</span></button>
  <button class="rn-tab" data-panel="decisions"><i class="fas fa-gavel"></i> Décisions <span class="rn-count">{{ $decisions->count() }}</span></button>
  <button class="rn-tab" data-panel="suivi"><i class="fas fa-tasks"></i> Plan d'action</button>
</div>

{{-- PANEL : CALENDRIER --}}
<div id="panel-calendrier" class="rn-panel active">
  <div class="panel-card">
    <div class="tab-section-header"><i class="fas fa-calendar-alt"></i> Réunions planifiées ({{ $reunions->count() }})</div>
    @forelse($reunions as $r)
      @php
        $past = \Carbon\Carbon::parse($r->start_at)->isPast();
      @endphp
      <div class="list-row" style="opacity:{{ $past ? '.55' : '1' }}">
        <div style="width:44px;text-align:center;flex-shrink:0;">
          <div style="font-size:17px;font-weight:700;color:var(--sec-primary);">{{ \Carbon\Carbon::parse($r->start_at)->format('d') }}</div>
          <div style="font-size:10px;color:var(--sec-text-muted);text-transform:uppercase;">{{ \Carbon\Carbon::parse($r->start_at)->format('M') }}</div>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $r->title }}</div>
          <div style="font-size:11px;color:var(--sec-text-muted);">
            <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($r->start_at)->format('H:i') }}
            @if($r->location) — <i class="fas fa-map-marker-alt"></i> {{ $r->location }} @endif
          </div>
        </div>
        <span class="sec-badge {{ $past ? '' : 'sec-badge-success' }}">{{ $past ? 'Passée' : 'Planifiée' }}</span>
      </div>
    @empty
      <div class="empty-state"><i class="fas fa-calendar-alt" style="font-size:32px;opacity:.3;display:block;margin-bottom:10px;"></i>Aucune réunion planifiée.</div>
    @endforelse
  </div>
</div>

{{-- PANEL : ORDRES DU JOUR --}}
<div id="panel-odj" class="rn-panel">
  <div class="panel-card" style="margin-bottom:16px;">
    <div class="tab-section-header"><i class="fas fa-plus-circle"></i> Préparer un ordre du jour (avant la réunion)</div>
    <form action="{{ route('gel-secretary.reunions.odj.store') }}" method="POST" style="padding:16px;">
      @csrf
      <div class="row g-3" style="display:flex;flex-wrap:wrap;gap:12px;">
        <div style="flex:2;min-width:220px;">
          <label style="font-size:11px;font-weight:700;color:var(--sec-text);display:block;margin-bottom:4px;">Entreprise *</label>
          <select name="client_id" class="sec-form-control" required>
            @foreach($clients as $c)
              <option value="{{ $c->id }}" {{ ($activeClient && $activeClient->id == $c->id) ? 'selected' : '' }}>{{ $c->nom_entreprise }}</option>
            @endforeach
          </select>
        </div>
        <div style="flex:2;min-width:220px;">
          <label style="font-size:11px;font-weight:700;color:var(--sec-text);display:block;margin-bottom:4px;">Titre de la réunion *</label>
          <input type="text" name="titre" class="sec-form-control" required placeholder="Ex: Conseil d'administration — T2">
        </div>
        <div style="flex:1;min-width:140px;">
          <label style="font-size:11px;font-weight:700;color:var(--sec-text);display:block;margin-bottom:4px;">Date *</label>
          <input type="date" name="date_reunion" class="sec-form-control" required value="{{ now()->addDays(3)->format('Y-m-d') }}">
        </div>
        <div style="flex:1;min-width:120px;">
          <label style="font-size:11px;font-weight:700;color:var(--sec-text);display:block;margin-bottom:4px;">Heure début</label>
          <input type="time" name="heure_debut" class="sec-form-control" value="09:00">
        </div>
        <div style="flex:1;min-width:120px;">
          <label style="font-size:11px;font-weight:700;color:var(--sec-text);display:block;margin-bottom:4px;">Lieu</label>
          <input type="text" name="lieu" class="sec-form-control" placeholder="Salle / visio">
        </div>
      </div>

      <div style="margin-top:12px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
          <label style="font-size:11px;font-weight:700;color:var(--sec-text);flex:1;">Points de l'ordre du jour * (1 par ligne)</label>
          <button type="button" class="sec-btn sec-btn-secondary sec-btn-sm" onclick="generateOdjIA()" style="background:#F0FDFA;color:#0D9488;border-color:#99F6E4;">
            <i class="fas fa-sparkles" style="color:#F59E0B;"></i> Générer par IA
          </button>
        </div>
        <div style="display:flex;gap:8px;">
          <input type="text" id="odjSujetInput" class="sec-form-control" placeholder="Sujet de la réunion (pour l'IA)..." style="flex:1;">
        </div>
        <textarea name="ordre_du_jour" id="odjTextarea" class="sec-form-control" rows="4" style="margin-top:8px;" placeholder="- Examen du bilan T1&#10;- Point sur les échéances fiscales&#10;- Suivi des décisions précédentes"></textarea>
      </div>

      <div style="margin-top:12px;">
        <label style="font-size:11px;font-weight:700;color:var(--sec-text);display:block;margin-bottom:4px;">Participants (1 par ligne)</label>
        <textarea name="participants" class="sec-form-control" rows="2" placeholder="Ex: Directeur Général&#10;Secrétaire Général"></textarea>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
        <button type="submit" class="sec-btn sec-btn-primary"><i class="fas fa-save"></i> Créer l'ordre du jour</button>
      </div>
    </form>
  </div>

  <div class="panel-card">
    <div class="tab-section-header"><i class="fas fa-list-check"></i> Ordres du jour préparés ({{ $ordresDuJour->count() }})</div>
    @forelse($ordresDuJour as $odj)
      <div class="list-row" style="align-items:flex-start;">
        <div style="width:44px;text-align:center;flex-shrink:0;">
          <div style="font-size:17px;font-weight:700;color:var(--sec-primary);">{{ \Carbon\Carbon::parse($odj->date_reunion)->format('d') }}</div>
          <div style="font-size:10px;color:var(--sec-text-muted);text-transform:uppercase;">{{ \Carbon\Carbon::parse($odj->date_reunion)->format('M') }}</div>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $odj->titre }}</div>
          <div style="font-size:11px;color:var(--sec-text-muted);">{{ $odj->client->nom_entreprise ?? '' }} — {{ $odj->heure_debut ?? '' }}</div>
          <div style="margin-top:8px;">
            @foreach((array)$odj->ordre_du_jour as $point)
              <div style="font-size:12px;color:var(--sec-text);padding:3px 0;display:flex;gap:6px;">
                <span style="color:var(--sec-primary);"><i class="fas fa-circle" style="font-size:5px;"></i></span> {{ $point }}
              </div>
            @endforeach
          </div>
        </div>
        <a href="{{ route('gel-secretary.pv.index') }}" class="sec-btn sec-btn-secondary sec-btn-sm">
          <i class="fas fa-file-signature"></i> Rédiger le PV
        </a>
      </div>
    @empty
      <div class="empty-state"><i class="fas fa-list-check" style="font-size:32px;opacity:.3;display:block;margin-bottom:10px;"></i>Aucun ordre du jour préparé.</div>
    @endforelse
  </div>
</div>

{{-- PANEL : PROCES-VERBAUX --}}
<div id="panel-pv" class="rn-panel">
  <div class="panel-card">
    <div class="tab-section-header" style="display:flex;align-items:center;justify-content:space-between;">
      <span><i class="fas fa-file-signature"></i> Procès-verbaux ({{ $pvs->count() }})</span>
      <a href="{{ route('gel-secretary.pv.index') }}" class="sec-btn sec-btn-secondary sec-btn-sm"><i class="fas fa-external-link-alt"></i> Gérer les PV</a>
    </div>
    @forelse($pvs as $pv)
      <div class="list-row">
        <div style="width:38px;height:38px;border-radius:10px;background:#EEF2FF;color:#6366F1;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="fas fa-file-signature"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $pv->titre }}</div>
          <div style="font-size:11px;color:var(--sec-text-muted);">
            {{ \Carbon\Carbon::parse($pv->date_reunion)->format('d/m/Y') }}
            @if(is_array($pv->participants) && count($pv->participants)) — {{ count($pv->participants) }} participant(s) @endif
          </div>
        </div>
        @if($pv->statut == 'brouillon') <span class="b-attente">Brouillon</span>
        @elseif($pv->statut == 'en_attente_signature') <span class="b-en-cours">En attente de signature</span>
        @else <span class="b-termine"><i class="fas fa-lock"></i> Signé</span>
        @endif
      </div>
    @empty
      <div class="empty-state"><i class="fas fa-file-signature" style="font-size:32px;opacity:.3;display:block;margin-bottom:10px;"></i>Aucun procès-verbal.</div>
    @endforelse
  </div>
</div>

{{-- PANEL : DECISIONS --}}
<div id="panel-decisions" class="rn-panel">
  <div class="panel-card">
    <div class="tab-section-header"><i class="fas fa-gavel"></i> Décisions extraites des PV ({{ $decisions->count() }})</div>
    @forelse($decisions as $d)
      <div class="list-row" style="align-items:flex-start;">
        <div style="width:34px;height:34px;border-radius:50%;background:#FEF2F2;color:#DC2626;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="fas fa-gavel" style="font-size:13px;"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $d->action }}</div>
          <div style="font-size:11px;color:var(--sec-text-muted);margin-top:3px;">
            <i class="fas fa-file-signature"></i> {{ $d->pv_titre }}
            @if($d->pv_date) — {{ \Carbon\Carbon::parse($d->pv_date)->format('d/m/Y') }} @endif
            @if($d->responsable) — <i class="fas fa-user"></i> {{ $d->responsable }} @endif
            @if($d->echeance) — <i class="far fa-calendar"></i> {{ \Carbon\Carbon::parse($d->echeance)->format('d/m/Y') }} @endif
          </div>
        </div>
      </div>
    @empty
      <div class="empty-state"><i class="fas fa-gavel" style="font-size:32px;opacity:.3;display:block;margin-bottom:10px;"></i>Aucune décision enregistrée.</div>
    @endforelse
  </div>
</div>

{{-- PANEL : PLAN D'ACTION / SUIVI --}}
<div id="panel-suivi" class="rn-panel">
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:16px;">
    <div style="text-align:center;background:#fff;border:1px solid var(--sec-border);border-radius:10px;padding:14px;">
      <div style="font-size:24px;font-weight:700;color:var(--sec-text);">{{ $suiviStats['a_faire'] }}</div>
      <div style="font-size:11px;color:var(--sec-text-muted);">À faire</div>
    </div>
    <div style="text-align:center;background:#fff;border:1px solid var(--sec-border);border-radius:10px;padding:14px;">
      <div style="font-size:24px;font-weight:700;color:#D97706;">{{ $suiviStats['en_cours'] }}</div>
      <div style="font-size:11px;color:var(--sec-text-muted);">En cours</div>
    </div>
    <div style="text-align:center;background:#fff;border:1px solid var(--sec-border);border-radius:10px;padding:14px;">
      <div style="font-size:24px;font-weight:700;color:#DC2626;">{{ $suiviStats['en_retard'] }}</div>
      <div style="font-size:11px;color:var(--sec-text-muted);">En retard</div>
    </div>
    <div style="text-align:center;background:#fff;border:1px solid var(--sec-border);border-radius:10px;padding:14px;">
      <div style="font-size:24px;font-weight:700;color:#059669;">{{ $suiviStats['terminees'] }}</div>
      <div style="font-size:11px;color:var(--sec-text-muted);">Terminées</div>
    </div>
  </div>

  <div class="panel-card">
    <div class="tab-section-header"><i class="fas fa-tasks"></i> Plan d'action issu des décisions ({{ $planActions->count() }})</div>
    @forelse($planActions as $t)
      @php
        $overdue = in_array($t->statut, ['a_faire', 'en_cours']) && $t->date_echeance && \Carbon\Carbon::parse($t->date_echeance)->isPast();
      @endphp
      <div class="list-row">
        <div style="width:4px;height:36px;background:{{ $t->priorite === 'critique' ? '#DC2626' : ($t->priorite === 'haute' ? '#D97706' : '#2563EB') }};border-radius:4px;flex-shrink:0;"></div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;color:var(--sec-text);{{ $t->statut === 'terminee' ? 'text-decoration:line-through;opacity:.6;' : '' }}">{{ $t->titre }}</div>
          <div style="font-size:11px;color:{{ $overdue ? '#DC2626' : 'var(--sec-text-muted)' }};">
            <i class="far fa-calendar-alt"></i> Échéance : {{ $t->date_echeance ? \Carbon\Carbon::parse($t->date_echeance)->format('d/m/Y') : 'N/A' }}
          </div>
        </div>
        @if($overdue) <span class="b-retard">En retard</span>
        @elseif($t->statut === 'terminee') <span class="b-termine">Terminée</span>
        @elseif($t->statut === 'en_cours') <span class="b-en-cours">En cours</span>
        @else <span class="b-attente">À faire</span>
        @endif
      </div>
    @empty
      <div class="empty-state"><i class="fas fa-tasks" style="font-size:32px;opacity:.3;display:block;margin-bottom:10px;"></i>Aucune action issue de réunion. Signez un PV pour générer le plan d'action.</div>
    @endforelse
  </div>
</div>

<script>
  document.querySelectorAll('.rn-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.rn-tab').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.rn-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      var panel = document.getElementById('panel-' + btn.dataset.panel);
      if (panel) panel.classList.add('active');
    });
  });

  // Activer l'onglet demandé via query ?tab=
  const params = new URLSearchParams(window.location.search);
  const tab = params.get('tab');
  if (tab) {
    const target = document.querySelector('.rn-tab[data-panel="' + tab + '"]');
    if (target) target.click();
  }

  function generateOdjIA() {
    const sujet = document.getElementById('odjSujetInput').value;
    if (!sujet.trim()) { secToast('Saisissez un sujet pour générer l\'ODJ.', 'warn'); return; }
    const btn = event.target.closest('button');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';
    let fd = new FormData();
    fd.append('sujet', sujet);
    fd.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    fetch("{{ route('gel-secretary.reunions.odj.ia') }}", {
      method: 'POST', body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
      if (data.error) throw new Error(data.error);
      document.getElementById('odjTextarea').value = data.ordre_du_jour.join('\n');
      secToast('Ordre du jour généré par l\'IA.', 'success');
    })
    .catch(err => secToast('Erreur de génération.', 'error'))
    .finally(() => { btn.disabled = false; btn.innerHTML = original; });
  }
</script>
@endsection
