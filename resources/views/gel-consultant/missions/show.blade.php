@extends('layouts.gel-consultant')
@section('content')

<div class="page-header" style="display:flex; align-items:flex-start; justify-content:space-between;">
  <div>
    <a href="{{ route('gel-consultant.dashboard') }}" style="font-size:12px; color:var(--muted); text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:12px;">
      <i class="fas fa-arrow-left"></i> Retour à mes dossiers
    </a>
    <h1 class="page-title">{{ $mission->title }}</h1>
    <p class="page-sub">
      <i class="fas fa-building" style="font-size:11px;"></i>
      {{ $mission->entreprise?->nom ?? 'Entreprise' }} •
      <span class="badge badge-purple" style="font-size:11px; padding:2px 8px;">{{ $mission->specialty }}</span>
    </p>
  </div>
  <div style="text-align:right;">
    <div style="font-size:12px; color:var(--muted);">Votre accès expire le</div>
    <div style="font-size:16px; font-weight:700; color:var(--warning);">{{ $mission->end_date->format('d/m/Y') }}</div>
  </div>
</div>

<!-- Description du Périmètre -->
<div class="c-card">
  <div class="c-card-header">
    <span class="c-card-title"><i class="fas fa-clipboard-list" style="color:var(--accent-light); margin-right:8px;"></i> Périmètre Confié</span>
  </div>
  <div class="c-card-body">
    <p style="font-size:14px; line-height:1.7; color:var(--text);">{{ $mission->description }}</p>
    <div style="margin-top:16px; display:flex; gap:16px; flex-wrap:wrap;">
      @if($mission->start_date)
        <div style="font-size:12px; color:var(--muted);">
          <i class="fas fa-calendar-check" style="margin-right:4px;"></i>
          Début : <strong style="color:var(--text);">{{ $mission->start_date->format('d/m/Y') }}</strong>
        </div>
      @endif
      <div style="font-size:12px; color:var(--muted);">
        <i class="fas fa-calendar-times" style="margin-right:4px;"></i>
        Fin d'accès : <strong style="color:var(--warning);">{{ $mission->end_date->format('d/m/Y') }}</strong>
      </div>
    </div>
  </div>
</div>

<!-- Dépôt de Livrable -->
<div class="c-card">
  <div class="c-card-header">
    <span class="c-card-title"><i class="fas fa-upload" style="color:var(--success); margin-right:8px;"></i> Déposer un Livrable</span>
  </div>
  <div class="c-card-body">
    <p style="font-size:13px; color:var(--muted); margin-bottom:16px;">
      Déposez ici votre rapport, avis juridique, recommandation ou tout autre livrable lié à ce dossier.
      <strong>Vous ne pouvez pas modifier les documents originaux de l'entreprise.</strong>
    </p>
    <form action="{{ route('gel-consultant.deliverables.store', $mission->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div style="margin-bottom:14px;">
        <label style="font-size:12px; font-weight:600; color:var(--text); display:block; margin-bottom:6px;">Fichier (PDF, Word, Excel — 50 Mo max) *</label>
        <input type="file" name="file" required
          style="width:100%; padding:10px; background:var(--surface2); border:1px solid var(--border); border-radius:8px; color:var(--text); font-size:13px;">
      </div>
      <div style="margin-bottom:16px;">
        <label style="font-size:12px; font-weight:600; color:var(--text); display:block; margin-bottom:6px;">Notes / Contexte (facultatif)</label>
        <textarea name="notes" rows="3"
          style="width:100%; padding:10px 12px; background:var(--surface2); border:1px solid var(--border); border-radius:8px; color:var(--text); font-size:13px; resize:vertical;"
          placeholder="Décrivez brièvement le contenu de ce livrable..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-cloud-upload-alt"></i> Déposer le livrable
      </button>
    </form>
  </div>
</div>

<!-- Livrables déjà déposés -->
@if($mission->deliverables->isNotEmpty())
<div class="c-card">
  <div class="c-card-header">
    <span class="c-card-title"><i class="fas fa-folder-open" style="color:var(--info); margin-right:8px;"></i> Livrables Déposés ({{ $mission->deliverables->count() }})</span>
  </div>
  <div class="c-card-body" style="padding:0;">
    @foreach($mission->deliverables as $deliverable)
      <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--border);">
        <div style="display:flex; align-items:center; gap:12px;">
          <div style="width:36px; height:36px; border-radius:8px; background:rgba(59,130,246,.15); display:flex; align-items:center; justify-content:center;">
            <i class="fas fa-file-alt" style="color:var(--info);"></i>
          </div>
          <div>
            <div style="font-size:13px; font-weight:500; color:var(--text);">{{ $deliverable->original_name }}</div>
            <div style="font-size:11px; color:var(--muted);">Déposé le {{ $deliverable->created_at->format('d/m/Y à H:i') }}</div>
            @if($deliverable->notes)
              <div style="font-size:11px; color:var(--muted); margin-top:2px;">{{ $deliverable->notes }}</div>
            @endif
          </div>
        </div>
        <form action="{{ route('gel-consultant.deliverables.destroy', $deliverable->id) }}" method="POST"
              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce livrable ?');">
          @csrf @method('DELETE')
          <button type="submit" style="background:none; border:none; color:var(--danger); cursor:pointer; font-size:13px; padding:6px;">
            <i class="fas fa-trash"></i>
          </button>
        </form>
      </div>
    @endforeach
  </div>
</div>
@endif

<!-- Historique d'activité (propre au consultant sur ce dossier) -->
@if($mission->audits->isNotEmpty())
<div class="c-card">
  <div class="c-card-header">
    <span class="c-card-title"><i class="fas fa-history" style="color:var(--muted); margin-right:8px;"></i> Historique de vos Actions</span>
  </div>
  <div class="c-card-body" style="padding:0;">
    @foreach($mission->audits->sortByDesc('created_at')->take(10) as $audit)
      <div style="display:flex; align-items:flex-start; gap:12px; padding:12px 20px; border-bottom:1px solid var(--border);">
        <i class="fas fa-circle" style="font-size:7px; color:var(--muted); margin-top:5px; flex-shrink:0;"></i>
        <div>
          <div style="font-size:13px; color:var(--text);">{{ $audit->action }}</div>
          <div style="font-size:11px; color:var(--muted);">{{ $audit->created_at->format('d/m/Y à H:i') }} — {{ $audit->ip_address }}</div>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endif

@endsection
