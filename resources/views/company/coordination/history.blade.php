@extends('layouts.app')
@section('title', 'Historique de coordination — ' . ($client->company_name ?? $client->name ?? 'Entreprise'))

@section('content')
<div style="max-width:1000px; margin:0 auto; padding:24px 16px;">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
    <div>
      <div style="font-size:20px; font-weight:800; color:#0F172A;">Historique de coordination</div>
      <div style="font-size:13px; color:#64748B;">
        Suivi des échanges entre votre secrétaire et votre comptable sur
        <strong>{{ $client->company_name ?? $client->name ?? 'votre entreprise' }}</strong>
        — lecture seule.
      </div>
    </div>
    <a href="{{ route('company.dashboard') }}" class="btn btn-sm btn-outline-secondary">← Tableau de bord</a>
  </div>

  @if($events->isEmpty())
    <div style="background:#fff; border:1px solid #E2E8F0; border-radius:14px; padding:60px 20px; text-align:center; color:#94A3B8;">
      <i class="fas fa-people-arrows" style="font-size:40px; color:#CBD5E1; margin-bottom:12px; display:block;"></i>
      Aucun échange de coordination enregistré entre la secrétaire et le comptable.
    </div>
  @else
    <div style="background:#fff; border:1px solid #E2E8F0; border-radius:14px; overflow:hidden;">
      @foreach($events as $evt)
        <div style="display:flex; gap:14px; padding:14px 20px; border-bottom:1px solid #F1F5F9; align-items:flex-start;">
          <div style="width:38px;height:38px;border-radius:50%;background:{{ $evt->actor_role === 'comptable' ? 'rgba(37,99,235,.1)' : 'rgba(13,148,136,.1)' }}; color:{{ $evt->actor_role === 'comptable' ? '#2563EB' : '#0D9488' }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="{{ $evt->icon }}"></i>
          </div>
          <div style="flex:1; min-width:0;">
            <div style="display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap;">
              <div style="font-size:14px; font-weight:600; color:#0F172A;">{{ $evt->subject }}</div>
              <div style="font-size:11px; color:#94A3B8; white-space:nowrap;">{{ \Carbon\Carbon::parse($evt->created_at)->format('d/m/Y H:i') }}</div>
            </div>
            @if($evt->body)
              <div style="font-size:13px; color:#475569; margin-top:4px;">{{ $evt->body }}</div>
            @endif
            <div style="font-size:11px; color:#94A3B8; margin-top:6px;">
              <span style="display:inline-block; background:#F1F5F9; border-radius:6px; padding:2px 8px; text-transform:capitalize;">{{ $evt->actor_role }}</span>
              {{ $evt->actor_name }}
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  <div style="margin-top:16px; font-size:12px; color:#94A3B8; text-align:center;">
    Tous les échanges (transmissions, demandes, confirmations, alertes) sont journalisés en français et consultables par l'administrateur de l'entreprise.
  </div>
</div>
@endsection