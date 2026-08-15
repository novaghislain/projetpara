@extends('layouts.gel-secretary')
@section('title', 'Registre des Immobilisations — ' . $client->nom_entreprise)

@push('styles')
<style>
  @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
  .animate-fade { animation:fadeUp 0.3s ease-out forwards; opacity:0; }
  .delay-1 { animation-delay:0.05s; }
  .delay-2 { animation-delay:0.1s; }
  .delay-3 { animation-delay:0.15s; }

  .asset-card {
    background:white;
    border:1px solid var(--sec-border);
    border-radius:14px;
    padding:20px;
    transition:all 0.2s;
    position:relative;
    overflow:hidden;
  }
  .asset-card::before {
    content:'';
    position:absolute;
    top:0; left:0; right:0;
    height:3px;
    background:linear-gradient(90deg, var(--sec-primary), #0d9488);
  }
  .asset-card:hover { box-shadow:0 8px 24px rgba(0,0,0,0.08); border-color:#cbd5e1; }

  .progress-bar-bg { background:#f1f5f9; border-radius:999px; height:6px; overflow:hidden; }
  .progress-bar-fill { height:100%; border-radius:999px; transition:width 0.6s ease; }
  
  .stat-chip {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600;
  }
  
  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px); }
  .modal-overlay.open { display:flex; }
  .modal-box { background:white; border-radius:20px; padding:28px; width:560px; max-width:95vw; max-height:90vh; overflow-y:auto; box-shadow:0 24px 60px rgba(0,0,0,0.2); }
</style>
@endpush

@section('content')

{{-- En-tête --}}
<div class="animate-fade" style="background:white; border:1px solid var(--sec-border); border-radius:16px; padding:24px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.04);">
  <div>
    <h1 style="font-size:22px; font-weight:700; color:var(--sec-text); margin-bottom:6px; letter-spacing:-0.5px;">
      <i class="fas fa-building" style="color:var(--sec-primary); margin-right:8px;"></i>
      Immobilisations — {{ $client->nom_entreprise }}
    </h1>
    <p style="font-size:14px; color:var(--sec-muted); margin:0;">Registre des actifs immobilisés, plans d'amortissement et VNC.</p>
  </div>
  <div style="display:flex; gap:12px; align-items:center;">
    <button onclick="document.getElementById('modal-add-asset').classList.add('open')"
      style="background:var(--sec-primary); color:white; border:none; border-radius:10px; padding:10px 18px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px;">
      <i class="fas fa-plus"></i> Nouvelle Immobilisation
    </button>
    <a href="{{ route('gel-secretary.clients.show', $client->id) }}"
      style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; padding:10px 16px; text-decoration:none; font-size:14px; font-weight:600;">
      <i class="fas fa-arrow-left"></i> Retour
    </a>
  </div>
</div>

@if(session('success'))
  <div class="animate-fade" style="background:#ecfdf5; border:1px solid #a7f3d0; color:#059669; padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:14px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
  </div>
@endif

{{-- Statistiques globales --}}
@php
  $totalCout   = $assets->sum('cout_acquisition');
  $totalAmort  = $assets->sum('amort_cumule');
  $totalVNC    = $assets->sum('vnc');
  $nbActifs    = $assets->where('statut', 'actif')->count();
@endphp
<div class="animate-fade delay-1" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
  @foreach([
    ['fas fa-layer-group', 'Actifs', $nbActifs . ' biens', '#e0f2fe', '#0369a1'],
    ['fas fa-coins', 'Coût Total', number_format($totalCout, 0, ',', ' ') . ' F', '#fef3c7', '#d97706'],
    ['fas fa-chart-line', 'Amort. Cumulé', number_format($totalAmort, 0, ',', ' ') . ' F', '#f3e8ff', '#9333ea'],
    ['fas fa-balance-scale', 'VNC Totale', number_format($totalVNC, 0, ',', ' ') . ' F', '#dcfce7', '#16a34a'],
  ] as [$icon, $label, $val, $bg, $color])
  <div style="background:white; border:1px solid var(--sec-border); border-radius:12px; padding:18px; display:flex; align-items:center; gap:14px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
    <div style="width:44px; height:44px; border-radius:12px; background:{{ $bg }}; display:flex; align-items:center; justify-content:center; font-size:18px; color:{{ $color }}; flex-shrink:0;">
      <i class="{{ $icon }}"></i>
    </div>
    <div>
      <div style="font-size:12px; color:var(--sec-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">{{ $label }}</div>
      <div style="font-size:18px; font-weight:700; color:var(--sec-text);">{{ $val }}</div>
    </div>
  </div>
  @endforeach
</div>

{{-- Liste des immobilisations --}}
@if($assets->isEmpty())
  <div class="animate-fade delay-2" style="background:white; border:1px dashed #cbd5e1; border-radius:16px; padding:60px; text-align:center;">
    <i class="fas fa-box-open" style="font-size:48px; color:#cbd5e1; margin-bottom:16px; display:block;"></i>
    <p style="font-size:16px; color:var(--sec-muted); font-weight:500;">Aucune immobilisation enregistrée pour ce client.</p>
    <button onclick="document.getElementById('modal-add-asset').classList.add('open')"
      style="margin-top:16px; background:var(--sec-primary); color:white; border:none; border-radius:10px; padding:10px 20px; font-size:14px; font-weight:600; cursor:pointer;">
      <i class="fas fa-plus"></i> Enregistrer la première immobilisation
    </button>
  </div>
@else
  <div class="animate-fade delay-2" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:18px;">
    @foreach($assets as $asset)
    @php
      $colorVNC = $asset->progression >= 80 ? '#ef4444' : ($asset->progression >= 50 ? '#f59e0b' : '#10b981');
      $labelStatut = $asset->statut === 'cede' ? 'Cédé' : ($asset->statut === 'mis_au_rebut' ? 'Mis au rebut' : 'Actif');
      $bgStatut = $asset->statut === 'cede' ? '#fee2e2' : ($asset->statut === 'mis_au_rebut' ? '#fef3c7' : '#dcfce7');
      $fgStatut = $asset->statut === 'cede' ? '#dc2626' : ($asset->statut === 'mis_au_rebut' ? '#d97706' : '#16a34a');
    @endphp
    <div class="asset-card">
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px;">
        <div>
          <div style="font-size:16px; font-weight:700; color:var(--sec-text);">{{ $asset->nom }}</div>
          @if($asset->description)
            <div style="font-size:12px; color:var(--sec-muted); margin-top:3px;">{{ Str::limit($asset->description, 60) }}</div>
          @endif
        </div>
        <span class="stat-chip" style="background:{{ $bgStatut }}; color:{{ $fgStatut }}; border:1px solid {{ $bgStatut }};">
          {{ $labelStatut }}
        </span>
      </div>

      {{-- Grille de données --}}
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
        <div style="background:#f8fafc; border-radius:8px; padding:10px;">
          <div style="font-size:10px; color:var(--sec-muted); font-weight:600; text-transform:uppercase; margin-bottom:3px;">Coût d'acquisition</div>
          <div style="font-size:14px; font-weight:700; color:var(--sec-text);">{{ number_format($asset->cout_acquisition, 0, ',', ' ') }} F</div>
        </div>
        <div style="background:#f8fafc; border-radius:8px; padding:10px;">
          <div style="font-size:10px; color:var(--sec-muted); font-weight:600; text-transform:uppercase; margin-bottom:3px;">VNC actuelle</div>
          <div style="font-size:14px; font-weight:700; color:{{ $colorVNC }};">{{ number_format($asset->vnc, 0, ',', ' ') }} F</div>
        </div>
        <div style="background:#f8fafc; border-radius:8px; padding:10px;">
          <div style="font-size:10px; color:var(--sec-muted); font-weight:600; text-transform:uppercase; margin-bottom:3px;">Durée / Méthode</div>
          <div style="font-size:13px; font-weight:600; color:var(--sec-text);">{{ $asset->duree_vie }} ans · {{ ucfirst($asset->methode_amort ?? 'Linéaire') }}</div>
        </div>
        <div style="background:#f8fafc; border-radius:8px; padding:10px;">
          <div style="font-size:10px; color:var(--sec-muted); font-weight:600; text-transform:uppercase; margin-bottom:3px;">Annuité estimée</div>
          <div style="font-size:14px; font-weight:700; color:var(--sec-text);">{{ number_format($asset->annuite, 0, ',', ' ') }} F</div>
        </div>
      </div>

      {{-- Barre de progression de l'amortissement --}}
      <div style="margin-bottom:14px;">
        <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--sec-muted); margin-bottom:5px;">
          <span>Amortissement cumulé</span>
          <span style="font-weight:700; color:{{ $colorVNC }};">{{ $asset->progression }} %</span>
        </div>
        <div class="progress-bar-bg">
          <div class="progress-bar-fill" style="width:{{ $asset->progression }}%; background:{{ $colorVNC }};"></div>
        </div>
      </div>

      <div style="display:flex; justify-content:space-between; align-items:center;">
        <span style="font-size:11px; color:var(--sec-muted);">
          <i class="fas fa-calendar-alt" style="margin-right:4px;"></i>
          Acquis le {{ $asset->date_acquisition ? $asset->date_acquisition->format('d/m/Y') : '—' }}
        </span>
        <form method="POST" action="{{ route('gel-secretary.immobilisations.destroy', $asset->id) }}"
          onsubmit="return confirm('Archiver cette immobilisation ?')">
          @csrf @method('DELETE')
          <button type="submit" style="background:#fee2e2; color:#dc2626; border:none; border-radius:7px; padding:5px 10px; font-size:11px; cursor:pointer; font-weight:600;">
            <i class="fas fa-archive"></i> Archiver
          </button>
        </form>
      </div>
    </div>
    @endforeach
  </div>
@endif

{{-- Modal Ajout Immobilisation --}}
<div id="modal-add-asset" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h2 style="font-size:18px; font-weight:700; color:var(--sec-text);">
        <i class="fas fa-plus-circle" style="color:var(--sec-primary); margin-right:8px;"></i>
        Nouvelle Immobilisation
      </h2>
      <button onclick="document.getElementById('modal-add-asset').classList.remove('open')"
        style="background:#f1f5f9; border:none; border-radius:8px; width:32px; height:32px; cursor:pointer; font-size:16px; color:#64748b;">✕</button>
    </div>

    <form method="POST" action="{{ route('gel-secretary.immobilisations.store') }}">
      @csrf
      <input type="hidden" name="client_id" value="{{ $client->id }}">

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <div style="grid-column:1/-1;">
          <label style="font-size:12px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:5px;">Désignation *</label>
          <input type="text" name="nom" required placeholder="Ex: Ordinateur Dell XPS"
            style="width:100%; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; font-size:14px; box-sizing:border-box;">
        </div>
        <div style="grid-column:1/-1;">
          <label style="font-size:12px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:5px;">Description</label>
          <input type="text" name="description" placeholder="Description optionnelle"
            style="width:100%; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; font-size:14px; box-sizing:border-box;">
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:5px;">Date d'acquisition *</label>
          <input type="date" name="date_acquisition" required value="{{ date('Y-m-d') }}"
            style="width:100%; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; font-size:14px; box-sizing:border-box;">
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:5px;">Coût d'acquisition (F CFA) *</label>
          <input type="number" name="cout_acquisition" required min="0" step="1000" placeholder="0"
            style="width:100%; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; font-size:14px; box-sizing:border-box;">
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:5px;">Valeur résiduelle (F CFA)</label>
          <input type="number" name="valeur_residuelle" min="0" step="1000" value="0"
            style="width:100%; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; font-size:14px; box-sizing:border-box;">
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:5px;">Durée de vie (années) *</label>
          <input type="number" name="duree_vie" required min="1" max="50" placeholder="5"
            style="width:100%; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; font-size:14px; box-sizing:border-box;">
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:5px;">Méthode d'amortissement *</label>
          <select name="methode_amort"
            style="width:100%; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; font-size:14px; box-sizing:border-box; background:white;">
            <option value="lineaire">Linéaire</option>
            <option value="degressif">Dégressif</option>
          </select>
        </div>
      </div>

      <div style="display:flex; gap:12px; justify-content:flex-end; margin-top:24px;">
        <button type="button" onclick="document.getElementById('modal-add-asset').classList.remove('open')"
          style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; padding:10px 18px; font-size:14px; font-weight:600; cursor:pointer;">
          Annuler
        </button>
        <button type="submit"
          style="background:var(--sec-primary); color:white; border:none; border-radius:10px; padding:10px 20px; font-size:14px; font-weight:600; cursor:pointer;">
          <i class="fas fa-save"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

@endsection
