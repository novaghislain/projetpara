@extends('layouts.gel-accountant')

@section('title', "Historique d'Audit Comptable — GEL Cabinet")

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
    <div>
        <h1 style="font-size:22px; font-weight:800; color:#0f172a; margin:0; letter-spacing:-0.3px;">
            <i class="bi bi-shield-check" style="color:#6366f1; margin-right:8px;"></i>Historique d'Audit & Traçabilité
        </h1>
        <p style="color:#64748b; margin:3px 0 0; font-size:14px;">Journal de bord chronologique et détaillé de toutes les actions comptables</p>
    </div>
</div>

{{-- ─── Filtres de recherche ────────────────────────────────────────── --}}
<div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.05); margin-bottom:24px;">
    <form action="{{ route('gel-accountant.comptabilite.historique') }}" method="GET" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)) 120px; gap:16px; align-items:end;">
        <div>
            <label style="font-size:12px; font-weight:700; color:#475569; display:block; margin-bottom:6px;">Recherche textuelle</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, email, description, IP..." style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
        </div>

        <div>
            <label style="font-size:12px; font-weight:700; color:#475569; display:block; margin-bottom:6px;">Filtrer par Client</label>
            <select name="client_id" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; background:#fff;">
                <option value="">— Tous —</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom_entreprise }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="font-size:12px; font-weight:700; color:#475569; display:block; margin-bottom:6px;">Événement</label>
            <select name="event" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; background:#fff;">
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
            <button type="submit" style="flex:1; padding:10px; background:#6366f1; color:#fff; border:none; border-radius:6px; font-size:13px; font-weight:700; cursor:pointer;">
                <i class="bi bi-funnel"></i>
            </button>
            <a href="{{ route('gel-accountant.comptabilite.historique') }}" style="display:flex; align-items:center; justify-content:center; width:40px; background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:6px; font-size:13px; text-decoration:none;">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

{{-- ─── Tableau historique ─────────────────────────────────────────── --}}
<div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.05);">
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:14px 18px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Date & Heure</th>
                    <th style="padding:14px 18px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Utilisateur (Acteur)</th>
                    <th style="padding:14px 18px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Rôle</th>
                    <th style="padding:14px 18px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Événement</th>
                    <th style="padding:14px 18px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Description</th>
                    <th style="padding:14px 18px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">IP / Agent</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:14px 18px; font-size:13px; font-weight:600; color:#334155; white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                        </td>
                        <td style="padding:14px 18px; font-size:13px; color:#0f172a;">
                            <div style="font-weight:700;">{{ $log->actor_name ?? 'Inconnu' }}</div>
                            <div style="font-size:11px; color:#64748b; font-family:monospace;">{{ $log->actor_email }}</div>
                        </td>
                        <td style="padding:14px 18px; font-size:12px;">
                            <span style="background:#e0e7ff; color:#4338ca; font-weight:700; padding:3px 8px; border-radius:12px;">
                                {{ $log->actor_role }}
                            </span>
                        </td>
                        <td style="padding:14px 18px; font-size:12px;">
                            <span style="font-weight:700; text-transform:uppercase; color:{{ $log->event === 'deleted' || $log->event === 'document.delete' ? '#dc2626' : (in_array($log->event, ['created', 'document.upload']) ? '#059669' : '#312e81') }}">
                                {{ $log->readable_event }}
                            </span>
                        </td>
                        <td style="padding:14px 18px; font-size:13px; color:#475569; font-weight:500;">
                            {{ $log->readable_description }}
                        </td>
                        <td style="padding:14px 18px; font-size:11px; color:#64748b; font-family:monospace;">
                            <div>IP: {{ $log->ip_address }}</div>
                            <div style="font-size:10px; color:#94a3b8; max-width:200px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="{{ $log->user_agent }}">
                                {{ $log->user_agent }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:40px; text-align:center; color:#94a3b8; font-weight:600;">
                            <i class="bi bi-info-circle" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                            Aucun log de traçabilité trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div style="padding:14px 18px; border-top:1px solid #e2e8f0; display:flex; justify-content:center;">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
