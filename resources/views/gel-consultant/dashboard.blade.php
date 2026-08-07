@extends('layouts.gel-consultant')
@section('content')

<div class="page-header">
  <h1 class="page-title">Mes Dossiers Actifs</h1>
  <p class="page-sub">Voici l'ensemble des missions qui vous ont été confiées. Votre accès est strictement limité à ces dossiers.</p>
</div>

@if($missions->isEmpty())
  <div class="c-card">
    <div class="c-card-body" style="text-align:center; padding: 60px 20px;">
      <i class="fas fa-folder-open" style="font-size:48px; color:var(--muted); margin-bottom:16px;"></i>
      <div style="font-size:16px; font-weight:600; margin-bottom:8px;">Aucun dossier actif</div>
      <p class="text-muted" style="font-size:13px;">Vous n'avez pas encore de mission confiée, ou vos accès ont expiré.<br>Contactez l'administrateur si vous pensez qu'il s'agit d'une erreur.</p>
    </div>
  </div>
@else
  @foreach($missions as $mission)
    <a href="{{ $mission->is_expired ? '#' : route('gel-consultant.missions.show', $mission->id) }}"
       class="mission-card {{ $mission->is_expired ? 'expired' : '' }}">
      <div class="mission-top">
        <div>
          <div class="mission-title">{{ $mission->title }}</div>
          <div class="mission-company">
            <i class="fas fa-building" style="font-size:11px;"></i>
            {{ $mission->entreprise?->nom ?? 'Entreprise non renseignée' }}
          </div>
        </div>
        <div>
          @if($mission->is_expired)
            <span class="badge badge-gray"><i class="fas fa-lock"></i> Accès expiré</span>
          @elseif($mission->status === 'cloture')
            <span class="badge badge-gray"><i class="fas fa-check"></i> Clôturé</span>
          @elseif($mission->status === 'livre')
            <span class="badge badge-info"><i class="fas fa-inbox"></i> Livrable déposé</span>
          @elseif($mission->status === 'valide')
            <span class="badge badge-success"><i class="fas fa-check-double"></i> Validé</span>
          @else
            <span class="badge badge-success"><i class="fas fa-circle" style="font-size:8px;"></i> En cours</span>
          @endif
        </div>
      </div>

      <p class="mission-desc">{{ Str::limit($mission->description, 160) }}</p>

      <div class="mission-footer">
        <span class="badge badge-purple">
          <i class="fas fa-user-graduate"></i> {{ $mission->specialty }}
        </span>
        <span style="font-size:12px; color:var(--muted);">
          <i class="fas fa-calendar-alt" style="margin-right:4px;"></i>
          Accès jusqu'au {{ $mission->end_date->format('d/m/Y') }}
        </span>
        @if(!$mission->is_expired)
          @if($mission->days_remaining <= 3)
            <span class="countdown-pill urgent">⚠ J-{{ $mission->days_remaining }}</span>
          @elseif($mission->days_remaining <= 7)
            <span class="countdown-pill">J-{{ $mission->days_remaining }}</span>
          @endif
        @endif
      </div>
    </a>
  @endforeach
@endif

@endsection
