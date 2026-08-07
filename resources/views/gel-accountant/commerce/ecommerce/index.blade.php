@extends('layouts.gel-accountant')
@section('title', 'E-commerce & Web - GEL Accountant')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-shopping-cart" style="color:var(--gel-primary); margin-right:8px;"></i> Ventes E-commerce</h1>
        <p class="gel-page-subtitle">Supervision des ventes en ligne et synchronisation des stocks.</p>
    </div>
    
    <form method="POST" action="{{ route('gel-accountant.commerce.ecommerce.sync') }}" style="margin:0;">
        @csrf
        <button type="submit" class="gel-btn gel-btn-primary" style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-sync-alt"></i> Synchroniser maintenant
        </button>
    </form>
</div>

<div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; margin-bottom:24px;">
    <div class="gel-card p-3" style="border-left:4px solid #3b82f6;">
        <div style="font-size:11px; font-weight:700; color:var(--gel-text-muted); text-transform:uppercase;">Commandes du jour</div>
        <div style="font-size:24px; font-weight:800; color:#0f172a; margin-top:4px;">12</div>
    </div>
    <div class="gel-card p-3" style="border-left:4px solid #10b981;">
        <div style="font-size:11px; font-weight:700; color:var(--gel-text-muted); text-transform:uppercase;">Chiffre d'affaires web (Aujourd'hui)</div>
        <div style="font-size:24px; font-weight:800; color:#0f172a; margin-top:4px;">1 240,50 €</div>
    </div>
    <div class="gel-card p-3" style="border-left:4px solid #f59e0b;">
        <div style="font-size:11px; font-weight:700; color:var(--gel-text-muted); text-transform:uppercase;">En attente d'expédition</div>
        <div style="font-size:24px; font-weight:800; color:#0f172a; margin-top:4px;">5</div>
    </div>
</div>

<div class="gel-card" style="overflow:hidden;">
    <div style="padding:16px 20px; border-bottom:1px solid var(--gel-border); background:#f8fafc; font-weight:700;">
        <i class="fas fa-list-ul me-2"></i> Dernières commandes synchronisées
    </div>
    <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead style="background:white; color:var(--gel-text-muted); font-size:11px; text-transform:uppercase;">
            <tr style="border-bottom:1px solid var(--gel-border);">
                <th style="padding:12px 20px; text-align:left;">N° Commande</th>
                <th style="padding:12px 20px; text-align:left;">Date</th>
                <th style="padding:12px 20px; text-align:left;">Client</th>
                <th style="padding:12px 20px; text-align:right;">Montant</th>
                <th style="padding:12px 20px; text-align:center;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentOrders as $order)
            <tr style="border-bottom:1px solid var(--gel-border);">
                <td style="padding:12px 20px; font-weight:600; color:var(--gel-primary);">#WEB-{{ $order->id }}</td>
                <td style="padding:12px 20px; color:#475569;">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</td>
                <td style="padding:12px 20px; font-weight:500;">{{ $order->customer_name }}</td>
                <td style="padding:12px 20px; text-align:right; font-weight:700;">{{ number_format($order->amount, 2, ',', ' ') }} €</td>
                <td style="padding:12px 20px; text-align:center;">
                    @if($order->status === 'paid')
                        <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:12px; background:#d1fae5; color:#065f46;">Payée</span>
                    @else
                        <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:12px; background:#fef3c7; color:#b45309;">En attente</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:40px; text-align:center; color:var(--gel-text-muted);">
                    <i class="fas fa-shopping-bag" style="font-size:32px; color:#cbd5e1; margin-bottom:12px; display:block;"></i>
                    Aucune commande web récente.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
