@extends('layouts.gel-accountant')
@section('title', 'Fil d\'Activité IA')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-stream" style="color:var(--gel-primary); margin-right:8px;"></i> Fil d'Activité IA</h1>
        <p class="gel-page-subtitle">Passez en revue et approuvez les suggestions des agents GEL Intelligence.</p>
    </div>
</div>

{{-- Statistiques Rapides --}}
<div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; margin-bottom:24px;">
    <div class="gel-card" style="padding:16px; border-left:4px solid #ef4444;">
        <div style="color:var(--gel-text-muted); font-size:12px; text-transform:uppercase; font-weight:700;">Critiques (En attente)</div>
        <div style="font-size:24px; font-weight:800; color:#0f172a;">{{ $counters['critical'] }}</div>
    </div>
    <div class="gel-card" style="padding:16px; border-left:4px solid #f59e0b;">
        <div style="color:var(--gel-text-muted); font-size:12px; text-transform:uppercase; font-weight:700;">Hautes</div>
        <div style="font-size:24px; font-weight:800; color:#0f172a;">{{ $counters['high'] }}</div>
    </div>
    <div class="gel-card" style="padding:16px; border-left:4px solid #3b82f6;">
        <div style="color:var(--gel-text-muted); font-size:12px; text-transform:uppercase; font-weight:700;">Normales</div>
        <div style="font-size:24px; font-weight:800; color:#0f172a;">{{ $counters['normal'] }}</div>
    </div>
    <div class="gel-card" style="padding:16px; border-left:4px solid #10b981;">
        <div style="color:var(--gel-text-muted); font-size:12px; text-transform:uppercase; font-weight:700;">Total En attente</div>
        <div style="font-size:24px; font-weight:800; color:#0f172a;">{{ $counters['total'] }}</div>
    </div>
</div>

<div class="row">
    {{-- Filtres --}}
    <div class="col-md-3">
        <div class="gel-card p-4">
            <h4 style="font-size:14px; font-weight:700; margin-bottom:16px;"><i class="fas fa-filter"></i> Filtres</h4>
            <form method="GET">
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px; font-weight:600;">Statut</label>
                    <select name="status" class="gel-form-select" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvée</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px; font-weight:600;">Priorité</label>
                    <select name="priority" class="gel-form-select" onchange="this.form.submit()">
                        <option value="">Toutes priorités</option>
                        <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>🔴 Critique</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>🟡 Haute</option>
                        <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>🔵 Normale</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>⚪ Faible</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px; font-weight:600;">Agent IA</label>
                    <select name="agent" class="gel-form-select" onchange="this.form.submit()">
                        <option value="">Tous les agents</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent }}" {{ request('agent') === $agent ? 'selected' : '' }}>{{ ucfirst($agent) }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request()->anyFilled(['status', 'priority', 'agent']))
                    <a href="{{ route('gel-accountant.ia.feed') }}" class="gel-btn gel-btn-secondary" style="width:100%; text-align:center; font-size:12px;">Réinitialiser</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Fil d'activité --}}
    <div class="col-md-9">
        @forelse($suggestions as $suggestion)
            @php
                $colors = [
                    'critical' => '#ef4444',
                    'high'     => '#f59e0b',
                    'normal'   => '#3b82f6',
                    'low'      => '#94a3b8',
                ];
                $sPriority = $suggestion->metadata['priority'] ?? 'normal';
                $color = $colors[$sPriority] ?? '#3b82f6';
            @endphp
            <div class="gel-card p-4 mb-3" style="border-left: 4px solid {{ $color }}; opacity: {{ $suggestion->status !== 'pending' ? '0.7' : '1' }};">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                            <span style="background:rgba(15,23,42,0.05); color:#475569; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:700; text-transform:uppercase;">
                                <i class="fas fa-robot"></i> {{ $suggestion->agent }}
                            </span>
                            <span style="color:var(--gel-text-muted); font-size:12px;"><i class="fas fa-clock"></i> {{ $suggestion->created_at->diffForHumans() }}</span>
                        </div>
                        <h3 style="font-size:16px; font-weight:700; margin:0 0 8px 0; color:#0f172a;">{{ $suggestion->title }}</h3>
                        <p style="font-size:14px; color:#475569; margin:0 0 16px 0;">{{ $suggestion->description }}</p>

                        @if(isset($suggestion->data['action_type']) && isset($suggestion->data['action_payload']))
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px; font-family:monospace; font-size:11px; color:#475569; margin-bottom:16px;">
                                <strong>Action proposée:</strong> {{ $suggestion->data['action_type'] }}<br>
                                {{ json_encode($suggestion->data['action_payload']) }}
                            </div>
                        @endif
                    </div>

                    @if($suggestion->status === 'pending')
                    <div style="display:flex; gap:8px;" id="actions-{{ $suggestion->id }}">
                        <button onclick="handleAiAction({{ $suggestion->id }}, 'reject')" class="gel-btn gel-btn-secondary" style="color:#ef4444; border-color:#ef4444; background:white;"><i class="fas fa-times"></i> Rejeter</button>
                        <button onclick="handleAiAction({{ $suggestion->id }}, 'approve')" class="gel-btn gel-btn-primary" style="background:#10b981; border-color:#10b981;"><i class="fas fa-check"></i> Approuver</button>
                    </div>
                    @else
                    <div style="font-size:12px; font-weight:700; padding:4px 12px; border-radius:999px; background:{{ $suggestion->status === 'approved' ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)' }}; color:{{ $suggestion->status === 'approved' ? '#10b981' : '#ef4444' }};">
                        {{ $suggestion->status === 'approved' ? '✓ Approuvée' : '✗ Rejetée' }}
                    </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="gel-card p-5" style="text-align:center; color:var(--gel-text-muted);">
                <i class="fas fa-check-circle" style="font-size:48px; color:#10b981; margin-bottom:16px; opacity:0.5;"></i>
                <h3 style="font-size:18px; font-weight:600; color:#0f172a;">Tout est à jour !</h3>
                <p>Aucune suggestion IA ne correspond à vos filtres.</p>
            </div>
        @endforelse

        <div style="margin-top:20px;">
            {{ $suggestions->links() }}
        </div>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function handleAiAction(id, action) {
    if(!confirm(`Êtes-vous sûr de vouloir ${action === 'approve' ? 'approuver' : 'rejeter'} cette suggestion ?`)) return;

    fetch(`/api/ai/suggestions/${id}/${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            window.location.reload();
        } else {
            alert('Erreur: ' + (data.message || 'Une erreur est survenue.'));
        }
    })
    .catch(() => alert('Erreur réseau.'));
}
</script>
@endsection
