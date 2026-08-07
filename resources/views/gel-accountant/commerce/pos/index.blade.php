@extends('layouts.gel-accountant')
@section('title', 'Caisses & POS - GEL Accountant')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-cash-register" style="color:var(--gel-primary); margin-right:8px;"></i> Gestion des Caisses</h1>
        <p class="gel-page-subtitle">Supervision des points de vente, clôtures et rapports Z.</p>
    </div>
</div>

<div class="row">
    @forelse($registers as $register)
        <div class="col-md-6 mb-4">
            <div class="gel-card p-4" style="border-top: 4px solid {{ $register->status === 'opened' ? '#10b981' : '#64748b' }};">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="font-size:16px; font-weight:700; margin:0;">{{ $register->name }}</h3>
                    @if($register->status === 'opened')
                        <span class="badge" style="background:#d1fae5; color:#065f46; font-size:11px; padding:4px 8px; border-radius:12px;">En service</span>
                    @else
                        <span class="badge" style="background:#f1f5f9; color:#475569; font-size:11px; padding:4px 8px; border-radius:12px;">Fermée</span>
                    @endif
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                    <div>
                        <div style="font-size:11px; color:var(--gel-text-muted); text-transform:uppercase; font-weight:600;">Caissier(e)</div>
                        <div style="font-size:14px; font-weight:500;">{{ $register->cashier_name ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:var(--gel-text-muted); text-transform:uppercase; font-weight:600;">Ouverture</div>
                        <div style="font-size:14px; font-weight:500;">{{ $register->opened_at ? \Carbon\Carbon::parse($register->opened_at)->format('d/m/Y H:i') : '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:var(--gel-text-muted); text-transform:uppercase; font-weight:600;">Transactions (Session)</div>
                        <div style="font-size:14px; font-weight:500;">{{ $register->transactions_count }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:var(--gel-text-muted); text-transform:uppercase; font-weight:600;">Solde actuel</div>
                        <div style="font-size:16px; font-weight:700; color:var(--gel-primary);">{{ number_format($register->current_balance, 2, ',', ' ') }} €</div>
                    </div>
                </div>

                <div style="display:flex; gap:10px;">
                    @if($register->status === 'opened')
                        <form method="POST" action="{{ route('gel-accountant.commerce.pos.close', $register->id) }}" style="margin:0;">
                            @csrf
                            <button type="submit" class="gel-btn gel-btn-primary" style="background:#f59e0b; border-color:#f59e0b;" onclick="return confirm('Générer le rapport Z et clôturer cette caisse ?');">
                                <i class="fas fa-lock"></i> Clôturer (Rapport Z)
                            </button>
                        </form>
                    @endif
                    <button class="gel-btn gel-btn-secondary"><i class="fas fa-history"></i> Historique</button>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="gel-card p-5" style="text-align:center; color:var(--gel-text-muted);">
                <i class="fas fa-cash-register" style="font-size:48px; color:#cbd5e1; margin-bottom:16px;"></i>
                <h3 style="font-size:18px; font-weight:600; color:#0f172a;">Aucune caisse enregistreuse</h3>
                <p>Configurez vos terminaux de point de vente pour commencer à encaisser.</p>
                <button class="gel-btn gel-btn-primary mt-2"><i class="fas fa-plus"></i> Ajouter une caisse</button>
            </div>
        </div>
    @endforelse
</div>

@endsection
