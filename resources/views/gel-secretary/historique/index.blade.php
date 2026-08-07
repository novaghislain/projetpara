@extends('layouts.gel-secretary')

@section('title', "Historique & Audit — GEL Secrétariat")

@section('content')
<div class="sec-page-header">
    <div>
        <h1 class="sec-page-title">
            <i class="fas fa-history" style="color:var(--sec-primary); margin-right:8px;"></i>Traçabilité & Historique d'Audit
        </h1>
        <p class="sec-page-sub">Registre complet et sécurisé de toutes les actions et manipulations de données du cabinet</p>
    </div>
</div>

{{-- ─── Filtres de recherche ─── --}}
<div style="background:#fff; border:1px solid var(--sec-border); border-radius:12px; padding:20px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,.04);">
    <form action="{{ route('gel-secretary.historique') }}" method="GET" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)) 120px; gap:16px; align-items:end;">
        <div class="sec-form-group" style="margin:0;">
            <label>Recherche textuelle</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, email, action, IP..." class="sec-form-control">
        </div>

        <div class="sec-form-group" style="margin:0;">
            <label>Filtrer par Entreprise</label>
            <select name="client_id" class="sec-form-select">
                <option value="">— Toutes —</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->nom_entreprise }}</option>
                @endforeach
            </select>
        </div>

        <div class="sec-form-group" style="margin:0;">
            <label>Événement</label>
            <select name="event" class="sec-form-select">
                <option value="">— Tous —</option>
                @foreach($events as $event)
                    @php
                        $map = \App\Models\Gel\AuditLog::getEventMap();
                        $translatedEvent = $map[$event] ?? ucfirst(str_replace(['.', '_'], ' ', $event));
                    @endphp
                    <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>{{ $translatedEvent }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; gap:8px;">
            <button type="submit" class="sec-btn sec-btn-primary" style="flex:1; justify-content:center; padding:10px;">
                <i class="fas fa-filter"></i>
            </button>
            <a href="{{ route('gel-secretary.historique') }}" class="sec-btn sec-btn-secondary" style="display:flex; align-items:center; justify-content:center; width:40px;">
                <i class="fas fa-sync-alt"></i>
            </a>
        </div>
    </form>
</div>

{{-- ─── Liste des journaux ─── --}}
<div class="sec-card" style="border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.04);">
    <div style="overflow-x:auto;">
        <table class="sec-table">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:14px 18px;">Horodatage</th>
                    <th style="padding:14px 18px;">Acteur / Utilisateur</th>
                    <th style="padding:14px 18px;">Rôle</th>
                    <th style="padding:14px 18px;">Événement</th>
                    <th style="padding:14px 18px;">Description</th>
                    <th style="padding:14px 18px;">IP / Navigateur</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:14px 18px; font-weight:600; color:#475569; white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                        </td>
                        <td style="padding:14px 18px;">
                            <div style="font-weight:700; color:#1e293b;">{{ $log->actor_name ?? 'Système' }}</div>
                            <div style="font-size:11px; color:#64748b; font-family:monospace;">{{ $log->actor_email }}</div>
                        </td>
                        <td style="padding:14px 18px; white-space:nowrap;">
                            <span class="sec-badge sec-badge-info" style="background:#f0fdfa; color:var(--sec-primary);">
                                {{ $log->actor_role }}
                            </span>
                        </td>
                        <td style="padding:14px 18px;">
                            <span class="sec-badge {{ $log->event === 'deleted' || $log->event === 'document.delete' ? 'sec-badge-danger' : (in_array($log->event, ['created', 'document.upload']) ? 'sec-badge-success' : 'sec-badge-muted') }}" style="text-transform:uppercase;">
                                {{ $log->readable_event }}
                            </span>
                        </td>
                        <td style="padding:14px 18px; font-weight:500; color:#334155;">
                            {{ $log->readable_description }}
                        </td>
                        <td style="padding:14px 18px; font-family:monospace; font-size:11px; color:#64748b;">
                            <div>IP: {{ $log->ip_address }}</div>
                            <div style="font-size:10px; color:#94a3b8; max-width:180px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="{{ $log->user_agent }}">
                                {{ $log->user_agent }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:40px; text-align:center; color:#94a3b8; font-weight:600;">
                            <i class="fas fa-info-circle" style="font-size:24px; display:block; margin-bottom:8px; color:#cbd5e1;"></i>
                            Aucune activité d'audit enregistrée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div style="padding:14px 18px; border-top:1px solid var(--sec-border); display:flex; justify-content:center;">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
