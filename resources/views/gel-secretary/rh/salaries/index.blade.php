@extends('layouts.gel-secretary')
@section('title', 'Registre du Personnel — ' . $client->nom_entreprise)

@push('styles')
<style>
  @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
  .animate-fade { animation:fadeUp 0.3s ease-out forwards; opacity:0; }
  .delay-1 { animation-delay:0.05s; }
  .delay-2 { animation-delay:0.1s; }

  .table-rh th { background:#f8fafc; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--sec-muted); letter-spacing:0.5px; padding:12px 16px; border-bottom:2px solid var(--sec-border); }
  .table-rh td { padding:14px 16px; font-size:14px; color:var(--sec-text); border-bottom:1px solid var(--sec-border); vertical-align:middle; }
  .table-rh tr:last-child td { border-bottom:none; }
  .table-rh tr:hover td { background:#fafafa; }

  .avatar-circle {
    width:36px; height:36px; border-radius:50%; background:var(--sec-primary);
    color:white; display:flex; align-items:center; justify-content:center;
    font-size:13px; font-weight:700; flex-shrink:0;
  }
  
  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px); }
  .modal-overlay.open { display:flex; }
  .modal-box { background:white; border-radius:20px; padding:28px; width:560px; max-width:95vw; max-height:90vh; overflow-y:auto; box-shadow:0 24px 60px rgba(0,0,0,0.2); }
  
  .form-group { margin-bottom:14px; }
  .form-group label { font-size:12px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:5px; }
  .form-group input, .form-group select { width:100%; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; font-size:14px; box-sizing:border-box; }
  .form-group input:focus, .form-group select:focus { outline:none; border-color:var(--sec-primary); box-shadow:0 0 0 3px rgba(15,118,110,0.1); }
</style>
@endpush

@section('content')

{{-- En-tête --}}
<div class="animate-fade" style="background:white; border:1px solid var(--sec-border); border-radius:16px; padding:24px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.04);">
  <div>
    <h1 style="font-size:22px; font-weight:700; color:var(--sec-text); margin-bottom:6px; letter-spacing:-0.5px;">
      <i class="fas fa-users" style="color:var(--sec-primary); margin-right:8px;"></i>
      Registre du Personnel — {{ $client->nom_entreprise }}
    </h1>
    <p style="font-size:14px; color:var(--sec-muted); margin:0;">Gestion des employés · CNSS · Données sociales</p>
  </div>
  <div style="display:flex; gap:12px; align-items:center;">
    <a href="{{ route('gel-secretary.rh.paie.index', ['client_id' => $client->id]) }}"
      style="background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; border-radius:10px; padding:10px 16px; text-decoration:none; font-size:14px; font-weight:600;">
      <i class="fas fa-file-invoice-dollar"></i> Bulletins de Paie
    </a>
    <button onclick="document.getElementById('modal-add-salarie').classList.add('open')"
      style="background:var(--sec-primary); color:white; border:none; border-radius:10px; padding:10px 18px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px;">
      <i class="fas fa-user-plus"></i> Ajouter un Salarié
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

{{-- KPIs --}}
@php $actifs = $salaries->where('statut', 'actif'); @endphp
<div class="animate-fade delay-1" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:24px;">
  @foreach([
    ['fas fa-users', 'Total Personnel', $salaries->count() . ' personnes', '#e0f2fe', '#0369a1'],
    ['fas fa-user-check', 'Actifs', $actifs->count() . ' employés', '#dcfce7', '#16a34a'],
    ['fas fa-coins', 'Masse Salariale', number_format($actifs->sum('salaire_base'), 0, ',', ' ') . ' F/mois', '#fef3c7', '#d97706'],
    ['fas fa-shield-halved', 'CNSS (Part Salarié)', number_format($actifs->sum('salaire_base') * 0.036, 0, ',', ' ') . ' F/mois', '#f3e8ff', '#9333ea'],
  ] as [$icon, $label, $val, $bg, $color])
  <div style="background:white; border:1px solid var(--sec-border); border-radius:12px; padding:18px; display:flex; align-items:center; gap:14px; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
    <div style="width:42px; height:42px; border-radius:11px; background:{{ $bg }}; display:flex; align-items:center; justify-content:center; font-size:18px; color:{{ $color }}; flex-shrink:0;">
      <i class="{{ $icon }}"></i>
    </div>
    <div>
      <div style="font-size:11px; color:var(--sec-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.4px;">{{ $label }}</div>
      <div style="font-size:16px; font-weight:700; color:var(--sec-text);">{{ $val }}</div>
    </div>
  </div>
  @endforeach
</div>

{{-- Tableau --}}
<div class="animate-fade delay-2" style="background:white; border:1px solid var(--sec-border); border-radius:16px; overflow:hidden; box-shadow:0 2px 4px rgba(0,0,0,0.03);">
  @if($salaries->isEmpty())
    <div style="padding:60px; text-align:center;">
      <i class="fas fa-users" style="font-size:48px; color:#e2e8f0; display:block; margin-bottom:16px;"></i>
      <p style="font-size:15px; color:var(--sec-muted);">Aucun salarié enregistré pour ce client.</p>
    </div>
  @else
    <table class="table-rh" style="width:100%; border-collapse:collapse;">
      <thead>
        <tr>
          <th>Salarié</th>
          <th>Date d'embauche</th>
          <th>N° CNSS</th>
          <th>Situation Fam.</th>
          <th style="text-align:right;">Salaire de Base</th>
          <th style="text-align:center;">Statut</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($salaries as $s)
        <tr>
          <td>
            <div style="display:flex; align-items:center; gap:10px;">
              <div class="avatar-circle">{{ strtoupper(substr($s->prenom, 0, 1) . substr($s->nom, 0, 1)) }}</div>
              <div>
                <div style="font-weight:600;">{{ $s->prenom }} {{ $s->nom }}</div>
              </div>
            </div>
          </td>
          <td>{{ $s->date_embauche ? $s->date_embauche->format('d/m/Y') : '—' }}</td>
          <td>{{ $s->numero_cnss ?: '—' }}</td>
          <td>{{ ucfirst($s->situation_matrimoniale ?: '—') }} · {{ $s->nombre_enfants }} enf.</td>
          <td style="text-align:right; font-weight:700;">{{ number_format($s->salaire_base, 0, ',', ' ') }} F</td>
          <td style="text-align:center;">
            <span style="background:{{ $s->statut === 'actif' ? '#dcfce7' : '#fee2e2' }}; color:{{ $s->statut === 'actif' ? '#16a34a' : '#dc2626' }}; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">
              {{ ucfirst($s->statut) }}
            </span>
          </td>
          <td>
            <form method="POST" action="{{ route('gel-secretary.rh.salaries.destroy', $s->id) }}"
              onsubmit="return confirm('Archiver ce salarié ?')">
              @csrf @method('DELETE')
              <button type="submit" style="background:#fee2e2; color:#dc2626; border:none; border-radius:7px; padding:5px 10px; font-size:11px; cursor:pointer; font-weight:600;">
                <i class="fas fa-archive"></i>
              </button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>

{{-- Modal Ajouter Salarié --}}
<div id="modal-add-salarie" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h2 style="font-size:18px; font-weight:700; color:var(--sec-text);">
        <i class="fas fa-user-plus" style="color:var(--sec-primary); margin-right:8px;"></i>
        Ajouter un Salarié
      </h2>
      <button onclick="document.getElementById('modal-add-salarie').classList.remove('open')"
        style="background:#f1f5f9; border:none; border-radius:8px; width:32px; height:32px; cursor:pointer; font-size:16px; color:#64748b;">✕</button>
    </div>
    <form method="POST" action="{{ route('gel-secretary.rh.salaries.store') }}">
      @csrf
      <input type="hidden" name="client_id" value="{{ $client->id }}">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
        <div class="form-group">
          <label>Prénom *</label>
          <input type="text" name="prenom" required placeholder="Jean">
        </div>
        <div class="form-group">
          <label>Nom *</label>
          <input type="text" name="nom" required placeholder="DUPONT">
        </div>
        <div class="form-group">
          <label>Date d'embauche</label>
          <input type="date" name="date_embauche">
        </div>
        <div class="form-group">
          <label>Salaire de base (F CFA) *</label>
          <input type="number" name="salaire_base" required min="0" step="1000" placeholder="80000">
        </div>
        <div class="form-group">
          <label>N° CNSS</label>
          <input type="text" name="numero_cnss" placeholder="CNSS-XXXXX">
        </div>
        <div class="form-group">
          <label>Situation matrimoniale</label>
          <select name="situation_matrimoniale">
            <option value="">Sélectionner</option>
            <option value="celibataire">Célibataire</option>
            <option value="marie">Marié(e)</option>
            <option value="divorce">Divorcé(e)</option>
            <option value="veuf">Veuf/Veuve</option>
          </select>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label>Nombre d'enfants à charge</label>
          <input type="number" name="nombre_enfants" min="0" value="0">
        </div>
      </div>
      <div style="display:flex; gap:12px; justify-content:flex-end; margin-top:8px;">
        <button type="button" onclick="document.getElementById('modal-add-salarie').classList.remove('open')"
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
