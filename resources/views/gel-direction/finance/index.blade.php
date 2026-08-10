@extends('layouts.gel-direction')

@section('title', 'Rapports Financiers — Direction')

@section('page_title', 'Rapports Financiers & Trésorerie')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
    <div>
        <h1 style="font-size:24px; font-weight:700; color:var(--dir-primary); margin:0;">Rapports Financiers</h1>
        <p style="color:var(--dir-text-muted); font-size:14px; margin:4px 0 0 0;">Analyse des revenus, prévisions et impayés.</p>
    </div>
    <div>
        <button class="btn btn-primary" style="background:var(--dir-primary); border:none; padding:8px 16px; font-weight:600; border-radius:8px;">
            <i class="fas fa-file-export"></i> Exporter Comptabilité (CSV)
        </button>
    </div>
</div>

<div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; margin-bottom:30px;">
    <!-- MRR -->
    <div style="background:white; padding:24px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02);">
        <div style="font-size:13px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; margin-bottom:8px;">Revenus Mensuels (MRR)</div>
        <div style="font-size:32px; font-weight:700; color:#3B82F6;">{{ number_format($mrr, 0, ',', ' ') }} €</div>
        <div style="font-size:12px; color:#10B981; margin-top:4px; font-weight:600;"><i class="fas fa-arrow-up"></i> Tendance positive</div>
    </div>
    
    <!-- ARR -->
    <div style="background:white; padding:24px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02);">
        <div style="font-size:13px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; margin-bottom:8px;">Revenus Annuels Prévisionnels</div>
        <div style="font-size:32px; font-weight:700; color:#10B981;">{{ number_format($arr, 0, ',', ' ') }} €</div>
        <div style="font-size:12px; color:var(--dir-text-muted); margin-top:4px;">Basé sur le MRR actuel x 12</div>
    </div>
    
    <!-- Impayés -->
    <div style="background:white; padding:24px; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02);">
        <div style="font-size:13px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; margin-bottom:8px;">Factures Impayées (>30j)</div>
        <div style="font-size:32px; font-weight:700; color:#EF4444;">{{ number_format($unpaidInvoices, 0, ',', ' ') }} €</div>
        <div style="font-size:12px; color:var(--dir-text-muted); margin-top:4px;"><a href="#" style="color:#EF4444; font-weight:600;">Lancer les relances automatiques</a></div>
    </div>
</div>

<div style="background:white; border-radius:12px; border:1px solid var(--dir-border); box-shadow:0 2px 4px rgba(0,0,0,0.02); overflow:hidden;">
    <div style="padding:20px; border-bottom:1px solid var(--dir-border);">
        <h3 style="font-size:16px; font-weight:600; color:var(--dir-text); margin:0;">Historique des Encaissements</h3>
    </div>
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#F8FAFC; border-bottom:1px solid var(--dir-border); text-align:left;">
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase;">Période</th>
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; text-align:right;">Chiffre d'Affaires</th>
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; text-align:right;">Dépenses</th>
                <th style="padding:16px 20px; font-size:12px; font-weight:600; color:var(--dir-text-muted); text-transform:uppercase; text-align:right;">Marge Nette</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history as $row)
            @php $marge = $row['revenue'] - $row['expenses']; @endphp
            <tr style="border-bottom:1px solid #F1F5F9;">
                <td style="padding:16px 20px; font-weight:600; color:var(--dir-text);">{{ $row['month'] }}</td>
                <td style="padding:16px 20px; text-align:right; font-weight:600; color:#3B82F6;">{{ number_format($row['revenue'], 0, ',', ' ') }} €</td>
                <td style="padding:16px 20px; text-align:right; color:var(--dir-text-muted);">{{ number_format($row['expenses'], 0, ',', ' ') }} €</td>
                <td style="padding:16px 20px; text-align:right; font-weight:700; color:{{ $marge > 15000 ? '#10B981' : '#F59E0B' }};">{{ number_format($marge, 0, ',', ' ') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
