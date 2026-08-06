@extends('layouts.gel-secretary')
@section('title', 'Déplacements Professionnels — Secrétariat')

@section('content')
<style>
  @keyframes fadeUp { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }
  .animate-fade{animation:fadeUp .35s ease-out forwards;opacity:0}
  .delay-1{animation-delay:.06s} .delay-2{animation-delay:.12s}

  .trip-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:18px; margin-top:20px; }
  .trip-card { background:#fff; border:1px solid var(--sec-border); border-radius:12px; overflow:hidden;
               box-shadow:0 1px 4px rgba(0,0,0,.04); transition:.2s; }
  .trip-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.08); transform:translateY(-2px); }
  .trip-head { padding:16px; border-bottom:1px solid var(--sec-border); display:flex; gap:12px; align-items:flex-start; }
  .trip-icon { width:44px;height:44px;border-radius:12px;background:#EFF6FF;color:#2563EB;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0; }
  .trip-body { padding:14px 16px; }
  .trip-row { display:flex; justify-content:space-between; font-size:12px; padding:5px 0; border-bottom:1px solid #F3F4F6; }
  .trip-row:last-child { border-bottom:none; }
  .trip-foot { padding:12px 16px; border-top:1px solid var(--sec-border); background:#F8FAFC; display:flex; gap:8px; }
  .status-pill { display:inline-block; padding:3px 10px; border-radius:20px; font-size:10px; font-weight:700; text-transform:uppercase; }
  .s-planifie   { background:#DBEAFE;color:#1D4ED8; }
  .s-en_cours   { background:#FEF3C7;color:#B45309; }
  .s-termine    { background:#DCFCE7;color:#15803D; }
  .s-annule     { background:#FEE2E2;color:#B91C1C; }
  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:3000; align-items:center; justify-content:center; }
  .modal-overlay.open { display:flex; }
  .modal-box { background:#fff; border-radius:16px; padding:32px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.2); }
</style>

{{-- Header --}}
<div class="sec-page-header animate-fade">
  <div>
    <div class="sec-page-title"><i class="fas fa-plane-departure" style="color:#2563EB;margin-right:8px;"></i>Déplacements Professionnels</div>
    <div class="sec-page-sub">Planification et suivi des voyages, vols et hébergements — {{ $trips->count() }} déplacement(s)</div>
  </div>
  <button class="sec-btn sec-btn-primary" onclick="document.getElementById('addTripModal').classList.add('open')">
    <i class="fas fa-plus me-1"></i> Nouveau déplacement
  </button>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" style="border-radius:10px;border-left:4px solid #10B981;margin-top:16px;" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

{{-- Filtres --}}
<div class="animate-fade delay-1" style="display:flex;gap:8px;margin-top:20px;flex-wrap:wrap;">
  @php $currentStatus = request('status'); @endphp
  <a href="{{ route('gel-secretary.services.business-trips.index') }}"
     class="sec-btn sec-btn-sm {{ !$currentStatus ? 'sec-btn-primary' : 'sec-btn-secondary' }}">Tous ({{ $trips->count() }})</a>
  @foreach(['planifie'=>'Planifiés','en_cours'=>'En cours','termine'=>'Terminés','annule'=>'Annulés'] as $s=>$l)
    <a href="{{ route('gel-secretary.services.business-trips.index', ['status'=>$s]) }}"
       class="sec-btn sec-btn-sm {{ $currentStatus===$s ? 'sec-btn-primary' : 'sec-btn-secondary' }}">{{ $l }}</a>
  @endforeach
</div>

{{-- Grille --}}
<div class="trip-grid animate-fade delay-2">
  @forelse($trips as $trip)
    @php
      $sClass = ['planifie'=>'s-planifie','en_cours'=>'s-en_cours','termine'=>'s-termine','annule'=>'s-annule'][$trip->status] ?? 's-planifie';
      $sLabel = ['planifie'=>'Planifié','en_cours'=>'En cours','termine'=>'Terminé','annule'=>'Annulé'][$trip->status] ?? $trip->status;
      $days = \Carbon\Carbon::parse($trip->start_date)->diffInDays(\Carbon\Carbon::parse($trip->end_date));
    @endphp
    <div class="trip-card">
      <div class="trip-head">
        <div class="trip-icon"><i class="fas fa-plane"></i></div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:14px;font-weight:700;color:var(--sec-text);margin-bottom:4px;">{{ $trip->destination }}</div>
          <div style="font-size:12px;color:var(--sec-text-muted);">{{ $trip->client?->nom_entreprise ?? '—' }}</div>
        </div>
        <span class="status-pill {{ $sClass }}">{{ $sLabel }}</span>
      </div>
      <div class="trip-body">
        <div class="trip-row">
          <span style="color:var(--sec-text-muted);"><i class="fas fa-user me-1"></i>Voyageur</span>
          <span style="font-weight:600;">{{ $trip->traveler_name }}</span>
        </div>
        <div class="trip-row">
          <span style="color:var(--sec-text-muted);"><i class="far fa-calendar me-1"></i>Départ</span>
          <span style="font-weight:600;">{{ \Carbon\Carbon::parse($trip->start_date)->format('d/m/Y') }}</span>
        </div>
        <div class="trip-row">
          <span style="color:var(--sec-text-muted);"><i class="far fa-calendar-check me-1"></i>Retour</span>
          <span style="font-weight:600;">{{ \Carbon\Carbon::parse($trip->end_date)->format('d/m/Y') }}</span>
        </div>
        <div class="trip-row">
          <span style="color:var(--sec-text-muted);"><i class="fas fa-moon me-1"></i>Durée</span>
          <span style="font-weight:600;">{{ $days }} nuit(s)</span>
        </div>
        @if($trip->budget)
        <div class="trip-row">
          <span style="color:var(--sec-text-muted);"><i class="fas fa-euro-sign me-1"></i>Budget</span>
          <span style="font-weight:600;color:#059669;">{{ number_format($trip->budget, 0, ',', ' ') }} €</span>
        </div>
        @endif
        @if($trip->purpose)
        <div style="margin-top:10px;padding:8px 10px;background:#F8FAFC;border-radius:8px;font-size:12px;color:var(--sec-text-muted);">
          <i class="fas fa-info-circle me-1"></i>{{ $trip->purpose }}
        </div>
        @endif
        @if($trip->transport_details)
        <div style="margin-top:6px;padding:7px 10px;background:#EFF6FF;border-radius:8px;font-size:11px;color:#1D4ED8;">
          <i class="fas fa-bus me-1"></i>{{ $trip->transport_details }}
        </div>
        @endif
        @if($trip->accommodation_details)
        <div style="margin-top:6px;padding:7px 10px;background:#F0FDF4;border-radius:8px;font-size:11px;color:#15803D;">
          <i class="fas fa-hotel me-1"></i>{{ $trip->accommodation_details }}
        </div>
        @endif
      </div>
      <div class="trip-foot">
        <a href="{{ route('gel-secretary.services.business-trips.edit', $trip->id) }}"
           class="sec-btn sec-btn-secondary sec-btn-sm" style="flex:1;text-align:center;">
          <i class="fas fa-edit"></i> Modifier
        </a>
        <form method="POST" action="{{ route('gel-secretary.services.business-trips.destroy', $trip->id) }}"
              onsubmit="return confirm('Supprimer ce déplacement ?');">
          @csrf @method('DELETE')
          <button type="submit" class="sec-btn sec-btn-sm"
            style="background:none;border:1px solid #E2E8F0;color:#94a3b8;cursor:pointer;"
            onmouseover="this.style.color='#ef4444';this.style.borderColor='#FECACA';"
            onmouseout="this.style.color='#94a3b8';this.style.borderColor='#E2E8F0';">
            <i class="fas fa-trash-alt"></i>
          </button>
        </form>
      </div>
    </div>
  @empty
    <div style="grid-column:1/-1;text-align:center;padding:60px 20px;background:#fff;border-radius:12px;border:1px dashed var(--sec-border);">
      <i class="fas fa-plane-slash" style="font-size:48px;color:#CBD5E1;display:block;margin-bottom:16px;"></i>
      <div style="font-size:16px;font-weight:700;color:var(--sec-text);margin-bottom:8px;">Aucun déplacement enregistré</div>
      <p style="font-size:13px;color:var(--sec-text-muted);margin-bottom:20px;">Planifiez vos voyages professionnels, vols et hébergements.</p>
      <button class="sec-btn sec-btn-primary" onclick="document.getElementById('addTripModal').classList.add('open')">
        <i class="fas fa-plus me-1"></i> Créer le premier déplacement
      </button>
    </div>
  @endforelse
</div>

{{-- Modal Nouveau déplacement --}}
<div id="addTripModal" class="modal-overlay">
  <div class="modal-box">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
      <h2 style="margin:0;font-size:18px;font-weight:700;color:var(--sec-text);">
        <i class="fas fa-plane-departure" style="color:#2563EB;margin-right:8px;"></i>Nouveau déplacement
      </h2>
      <button onclick="document.getElementById('addTripModal').classList.remove('open')"
              style="background:none;border:none;font-size:24px;cursor:pointer;color:#94a3b8;line-height:1;">&times;</button>
    </div>
    <form method="POST" action="{{ route('gel-secretary.services.business-trips.store') }}">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div style="grid-column:1/-1;">
          <label style="font-size:12px;font-weight:600;color:var(--sec-text-muted);margin-bottom:4px;display:block;">Voyageur *</label>
          <input type="text" name="traveler_name" class="form-control form-control-sm" required placeholder="Nom et prénom">
        </div>
        <div style="grid-column:1/-1;">
          <label style="font-size:12px;font-weight:600;color:var(--sec-text-muted);margin-bottom:4px;display:block;">Entreprise cliente *</label>
          <select name="client_id" class="form-select form-select-sm" required>
            <option value="">Sélectionner...</option>
            @foreach($clients as $c)
              <option value="{{ $c->id }}">{{ $c->nom_entreprise }}</option>
            @endforeach
          </select>
        </div>
        <div style="grid-column:1/-1;">
          <label style="font-size:12px;font-weight:600;color:var(--sec-text-muted);margin-bottom:4px;display:block;">Destination *</label>
          <input type="text" name="destination" class="form-control form-control-sm" required placeholder="Ex: Paris, France">
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:var(--sec-text-muted);margin-bottom:4px;display:block;">Date de départ *</label>
          <input type="date" name="start_date" class="form-control form-control-sm" required>
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:var(--sec-text-muted);margin-bottom:4px;display:block;">Date de retour *</label>
          <input type="date" name="end_date" class="form-control form-control-sm" required>
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:var(--sec-text-muted);margin-bottom:4px;display:block;">Statut *</label>
          <select name="status" class="form-select form-select-sm" required>
            <option value="planifie">Planifié</option>
            <option value="en_cours">En cours</option>
            <option value="termine">Terminé</option>
            <option value="annule">Annulé</option>
          </select>
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:var(--sec-text-muted);margin-bottom:4px;display:block;">Budget (€)</label>
          <input type="number" name="budget" class="form-control form-control-sm" placeholder="0">
        </div>
        <div style="grid-column:1/-1;">
          <label style="font-size:12px;font-weight:600;color:var(--sec-text-muted);margin-bottom:4px;display:block;">Objet du déplacement</label>
          <input type="text" name="purpose" class="form-control form-control-sm" placeholder="Ex: Salon, réunion client...">
        </div>
        <div style="grid-column:1/-1;">
          <label style="font-size:12px;font-weight:600;color:var(--sec-text-muted);margin-bottom:4px;display:block;">Transport (vol, train...)</label>
          <input type="text" name="transport_details" class="form-control form-control-sm" placeholder="Ex: Vol AF1234 CDG → LYS, 08h35">
        </div>
        <div style="grid-column:1/-1;">
          <label style="font-size:12px;font-weight:600;color:var(--sec-text-muted);margin-bottom:4px;display:block;">Hébergement</label>
          <input type="text" name="accommodation_details" class="form-control form-control-sm" placeholder="Ex: Hôtel Ibis Lyon, chambre simple">
        </div>
      </div>
      <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end;">
        <button type="button" onclick="document.getElementById('addTripModal').classList.remove('open')"
                class="sec-btn sec-btn-secondary">Annuler</button>
        <button type="submit" class="sec-btn sec-btn-primary">
          <i class="fas fa-save me-1"></i>Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('addTripModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
  });
</script>
@endsection
