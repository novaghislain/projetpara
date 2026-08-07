@extends('layouts.gel-accountant')
@section('title', 'Transactions Récurrentes')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-sync-alt" style="color:var(--gel-primary); margin-right:8px;"></i> Transactions Récurrentes</h1>
        <p class="gel-page-subtitle">Automatisez vos écritures périodiques (loyers, salaires, abonnements...)</p>
    </div>
    <a href="{{ route('gel-accountant.recurrentes.create') }}" class="gel-btn gel-btn-primary">
        <i class="fas fa-plus"></i> Nouveau Modèle
    </a>
</div>

@if(session('success'))
<div style="background:rgba(16,185,129,0.1); border:1px solid #10b981; border-radius:8px; padding:12px 16px; margin-bottom:20px; color:#065f46;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="gel-card" style="overflow:hidden;">
    <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead style="background:var(--gel-sidebar-bg); color:white;">
            <tr>
                <th style="padding:10px 14px; text-align:left;">Titre</th>
                <th style="padding:10px 14px; text-align:center;">Type</th>
                <th style="padding:10px 14px; text-align:center;">Fréquence</th>
                <th style="padding:10px 14px; text-align:center;">Prochaine occurrence</th>
                <th style="padding:10px 14px; text-align:center;">Actif</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
            @php
                $typeConfig = [
                    'scheduled' => ['color'=>'#3b82f6','label'=>'Programmée','icon'=>'fa-calendar-check'],
                    'reminder'  => ['color'=>'#f59e0b','label'=>'Rappel','icon'=>'fa-bell'],
                    'template'  => ['color'=>'#8b5cf6','label'=>'Template','icon'=>'fa-copy'],
                ];
                $t = $typeConfig[$tx->type] ?? $typeConfig['scheduled'];
            @endphp
            <tr style="border-bottom:1px solid var(--gel-border);">
                <td style="padding:10px 14px; font-weight:600;">
                    {{ $tx->title }}
                    <div style="font-size:11px; color:var(--gel-text-muted); margin-top:2px;">{{ $tx->transaction_type }}</div>
                </td>
                <td style="padding:10px 14px; text-align:center;">
                    <span style="display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600; color:{{ $t['color'] }}; background:{{ $t['color'] }}1a;">
                        <i class="fas {{ $t['icon'] }}"></i> {{ $t['label'] }}
                    </span>
                </td>
                <td style="padding:10px 14px; text-align:center; color:var(--gel-text-muted);">
                    {{ $tx->frequency_label }}
                </td>
                <td style="padding:10px 14px; text-align:center;">
                    @if($tx->next_occurrence)
                        <span style="font-weight:600; {{ $tx->next_occurrence->isPast() ? 'color:#ef4444;' : 'color:#10b981;' }}">
                            {{ $tx->next_occurrence->format('d/m/Y') }}
                        </span>
                    @else
                        <span style="color:var(--gel-text-muted);">—</span>
                    @endif
                </td>
                <td style="padding:10px 14px; text-align:center;">
                    <button onclick="toggleActive({{ $tx->id }}, this)" 
                        style="border:none; cursor:pointer; border-radius:999px; padding:4px 14px; font-size:12px; font-weight:700; transition:all 0.2s;
                        background:{{ $tx->is_active ? 'rgba(16,185,129,0.1)' : 'rgba(156,163,175,0.1)' }}; 
                        color:{{ $tx->is_active ? '#10b981' : '#9ca3af' }};"
                        data-active="{{ $tx->is_active ? '1' : '0' }}">
                        {{ $tx->is_active ? '✓ Actif' : '✗ Inactif' }}
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:40px; text-align:center; color:var(--gel-text-muted);">
                    <i class="fas fa-sync-alt" style="font-size:2rem; margin-bottom:10px; display:block; opacity:0.3;"></i>
                    Aucune transaction récurrente. Créez votre premier modèle !
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:12px 16px;">{{ $transactions->links() }}</div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function toggleActive(id, btn) {
    fetch(`/gel-accountant/recurrentes/${id}/toggle`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.dataset.active = data.is_active ? '1' : '0';
            btn.textContent = data.is_active ? '✓ Actif' : '✗ Inactif';
            btn.style.background = data.is_active ? 'rgba(16,185,129,0.1)' : 'rgba(156,163,175,0.1)';
            btn.style.color = data.is_active ? '#10b981' : '#9ca3af';
        }
    });
}
</script>
@endsection
