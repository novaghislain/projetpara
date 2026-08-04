@extends('layouts.gel-accountant')

@section('title', "Administration des Logs d'Audit — GEL Cabinet")

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
    <div>
        <h1 style="font-size:22px; font-weight:800; color:#0f172a; margin:0; letter-spacing:-0.3px;">
            <i class="bi bi-shield-lock-fill" style="color:#ef4444; margin-right:8px;"></i>Console d'Audit de Sécurité
        </h1>
        <p style="color:#64748b; margin:3px 0 0; font-size:14px;">Surveillance complète des actions et accès sur l'ensemble de la plateforme</p>
    </div>
</div>

{{-- ─── Filtres de recherche ────────────────────────────────────────── --}}
<div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.05); margin-bottom:24px;">
    <form action="{{ route('gel-accountant.historique-admin') }}" method="GET" style="display:grid; grid-template-columns:1fr 200px 120px; gap:16px; align-items:end;">
        <div>
            <label style="font-size:12px; font-weight:700; color:#475569; display:block; margin-bottom:6px;">Recherche textuelle</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, email, action, IP..." style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
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
            <button type="submit" style="flex:1; padding:10px; background:#ef4444; color:#fff; border:none; border-radius:6px; font-size:13px; font-weight:700; cursor:pointer;">
                <i class="bi bi-search"></i> Filtrer
            </button>
            <a href="{{ route('gel-accountant.historique-admin') }}" style="display:flex; align-items:center; justify-content:center; width:40px; background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:6px; font-size:13px; text-decoration:none;">
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
                    <th style="padding:14px 18px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Horodatage</th>
                    <th style="padding:14px 18px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Cabinet</th>
                    <th style="padding:14px 18px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Acteur / Rôle</th>
                    <th style="padding:14px 18px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Type</th>
                    <th style="padding:14px 18px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Action / Description</th>
                    <th style="padding:14px 18px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">IP & Identifiants</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:14px 18px; font-size:13px; font-weight:600; color:#ef4444; white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                        </td>
                        <td style="padding:14px 18px; font-size:13px; color:#334155; font-weight:600;">
                            {{ $log->cabinet?->nom ?? '—' }}
                        </td>
                        <td style="padding:14px 18px; font-size:13px; color:#0f172a;">
                            <div style="font-weight:700;">{{ $log->actor_name ?? 'Inconnu' }}</div>
                            <div style="font-size:11px; color:#64748b;">{{ $log->actor_email }}</div>
                            <div style="font-size:10px; margin-top:2px;"><span style="background:#fee2e2; color:#991b1b; padding:2px 6px; border-radius:10px; font-weight:700;">{{ $log->actor_role }}</span></div>
                        </td>
                        <td style="padding:14px 18px; font-size:11px; font-weight:700; text-transform:uppercase;">
                            {{ $log->readable_event }}
                        </td>
                        <td style="padding:14px 18px; font-size:13px; color:#475569; font-weight:500;">
                            {{ $log->readable_description }}
                        </td>
                        <td style="padding:14px 18px; font-size:11px; color:#64748b; font-family:monospace;">
                            <div>IP: {{ $log->ip_address }}</div>
                            <div>Session ID: <span style="font-size:9px; color:#94a3b8;">{{ $log->session_id ?? '—' }}</span></div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:40px; text-align:center; color:#94a3b8; font-weight:600;">
                            <i class="bi bi-info-circle" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                            Aucun log de sécurité disponible
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
