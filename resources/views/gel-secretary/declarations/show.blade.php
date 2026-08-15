@extends('layouts.gel-secretary')
@section('title', 'Liasse Fiscale — ' . $client->nom_entreprise)

@push('styles')
<style>
  @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
  .animate-fade { animation:fadeUp 0.3s ease-out forwards; opacity:0; }
  
  .liasse-container {
    background:white; border:1px solid var(--sec-border); border-radius:16px;
    padding:32px; box-shadow:0 10px 30px rgba(0,0,0,0.03); max-width:800px; margin:0 auto;
  }
  .liasse-header {
    border-bottom:2px solid var(--sec-border); padding-bottom:20px; margin-bottom:24px;
    display:flex; justify-content:space-between; align-items:flex-start;
  }
  .liasse-section {
    margin-bottom:32px;
  }
  .liasse-title {
    font-size:16px; font-weight:800; color:var(--sec-text); text-transform:uppercase;
    letter-spacing:1px; margin-bottom:16px; display:flex; align-items:center; gap:8px;
  }
  
  .liasse-row {
    display:flex; justify-content:space-between; align-items:center;
    padding:12px 16px; background:#f8fafc; border-radius:8px; margin-bottom:8px;
    font-size:14px;
  }
  .liasse-row.total {
    background:#e0f2fe; border:1px solid #bae6fd; font-weight:700;
  }
  
  .status-badge {
    padding:6px 16px; border-radius:20px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;
    display:inline-block;
  }
  .status-brouillon { background:#f1f5f9; color:#64748b; }
  .status-preparee { background:#fef3c7; color:#d97706; }
  .status-deposee { background:#e0f2fe; color:#0369a1; }
  .status-validee { background:#dcfce7; color:#16a34a; }

  /* Print Styles */
  @media print {
    /* Masquer la sidebar, la topbar, et les boutons d'action */
    .sec-sidebar,
    .sec-topbar,
    .print-actions,
    .no-print,
    .ai-widget-container,
    #aiChatWidget,
    .sec-toast-container {
      display: none !important;
    }

    /* Le contenu principal prend toute la largeur */
    .sec-content {
      margin-left: 0 !important;
      padding: 0 !important;
      width: 100% !important;
    }

    body, html {
      background: white !important;
      margin: 0 !important;
      padding: 0 !important;
    }

    /* Nettoyer le conteneur de la liasse */
    .liasse-container {
      box-shadow: none !important;
      border: none !important;
      margin: 0 !important;
      padding: 20px !important;
      max-width: 100% !important;
      width: 100% !important;
    }

    /* Forcer l'impression des couleurs */
    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    /* Éviter les coupures de page dans les tableaux */
    .liasse-section {
      page-break-inside: avoid;
    }
  }
</style>
@endpush

@section('content')

<div class="print-actions" style="max-width:800px; margin:0 auto 20px auto; display:flex; justify-content:space-between; align-items:center;">
  <a href="{{ route('gel-secretary.declarations.index', ['client_id' => $client->id]) }}"
    style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; padding:10px 16px; text-decoration:none; font-size:14px; font-weight:600;">
    <i class="fas fa-arrow-left"></i> Retour aux déclarations
  </a>
  
  <div style="display:flex; gap:10px;">
    @if($declaration->statut === 'brouillon')
      <form method="POST" action="{{ route('gel-secretary.declarations.status', $declaration->id) }}">
        @csrf
        <input type="hidden" name="statut" value="preparee">
        <button type="submit" style="background:#fef3c7; color:#d97706; border:1px solid #fde68a; border-radius:10px; padding:10px 16px; font-size:14px; font-weight:700; cursor:pointer;">
          <i class="fas fa-check"></i> Marquer comme Préparée
        </button>
      </form>
    @elseif($declaration->statut === 'preparee')
      <form method="POST" action="{{ route('gel-secretary.declarations.status', $declaration->id) }}">
        @csrf
        <input type="hidden" name="statut" value="deposee">
        <button type="submit" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; border-radius:10px; padding:10px 16px; font-size:14px; font-weight:700; cursor:pointer;">
          <i class="fas fa-upload"></i> Marquer comme Déposée
        </button>
      </form>
    @elseif($declaration->statut === 'deposee')
      <form method="POST" action="{{ route('gel-secretary.declarations.status', $declaration->id) }}">
        @csrf
        <input type="hidden" name="statut" value="validee">
        <button type="submit" style="background:#dcfce7; color:#16a34a; border:1px solid #bbf7d0; border-radius:10px; padding:10px 16px; font-size:14px; font-weight:700; cursor:pointer;">
          <i class="fas fa-check-double"></i> Valider la Déclaration
        </button>
      </form>
    @endif
    
    <button onclick="window.print()" style="background:var(--sec-primary); color:white; border:none; border-radius:10px; padding:10px 16px; font-size:14px; font-weight:600; cursor:pointer;">
      <i class="fas fa-print"></i> Imprimer
    </button>
  </div>
</div>

@if(session('success'))
  <div class="animate-fade" style="max-width:800px; margin:0 auto 20px auto; background:#ecfdf5; border:1px solid #a7f3d0; color:#059669; padding:12px 16px; border-radius:10px; font-size:14px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
  </div>
@endif

<div class="liasse-container animate-fade delay-1">
  
  <div class="liasse-header">
    <div>
      <div style="font-size:24px; font-weight:800; color:var(--sec-primary); letter-spacing:-0.5px; margin-bottom:4px;">
        Déclaration de {{ $declaration->type_impot }}
      </div>
      <div style="font-size:15px; color:var(--sec-muted); font-weight:600;">
        @php
          $moisNoms = [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
          $moisStr = $moisNoms[(int)$declaration->periode_mois] . ' ' . $declaration->periode_annee;
        @endphp
        Période : {{ $moisStr }}
      </div>
    </div>
    <div style="text-align:right;">
      <div class="status-badge status-{{ $declaration->statut }}" style="margin-bottom:8px;">
        Statut : {{ $declaration->statut }}
      </div>
      <div style="font-size:13px; color:var(--sec-muted);">
        Réf Client : {{ $client->ifu ?? 'N/A' }}
      </div>
    </div>
  </div>

  {{-- Identification --}}
  <div class="liasse-section">
    <div class="liasse-title"><i class="fas fa-building" style="color:var(--sec-muted);"></i> Identification de l'entreprise</div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; background:#f8fafc; padding:16px; border-radius:12px; border:1px solid var(--sec-border);">
      <div>
        <div style="font-size:11px; color:var(--sec-muted); text-transform:uppercase; font-weight:600; margin-bottom:4px;">Raison Sociale</div>
        <div style="font-weight:700;">{{ $client->nom_entreprise }}</div>
      </div>
      <div>
        <div style="font-size:11px; color:var(--sec-muted); text-transform:uppercase; font-weight:600; margin-bottom:4px;">IFU</div>
        <div style="font-weight:700;">{{ $client->ifu ?? 'Non renseigné' }}</div>
      </div>
      <div style="grid-column:1/-1;">
        <div style="font-size:11px; color:var(--sec-muted); text-transform:uppercase; font-weight:600; margin-bottom:4px;">Adresse</div>
        <div style="font-weight:600;">{{ $client->adresse ?? 'Non renseignée' }}</div>
      </div>
    </div>
  </div>

  {{-- Calculs --}}
  @php
    $details = $declaration->details ?? [];
    $caTaxable = $details['ca_taxable'] ?? 0;
    $tvaCollectee = $details['tva_collectee'] ?? 0;
    $tvaDeductible = $details['tva_deductible'] ?? 0;
    $netPayer = $declaration->montant_impot;
    
    $isCredit = $netPayer < 0;
    $montantAbs = abs($netPayer);
  @endphp

  <div class="liasse-section">
    <div class="liasse-title"><i class="fas fa-calculator" style="color:var(--sec-muted);"></i> Détermination de la TVA</div>
    
    <div class="liasse-row">
      <span style="color:var(--sec-muted);">I. Chiffre d'Affaires HT Taxable (Ventes)</span>
      <span style="font-weight:700;">{{ number_format($caTaxable, 0, ',', ' ') }} F</span>
    </div>
    
    <div class="liasse-row" style="border-left:4px solid #f59e0b;">
      <span style="font-weight:600;">II. TVA Brute Collectée (Comptes 443)</span>
      <span style="font-weight:700;">{{ number_format($tvaCollectee, 0, ',', ' ') }} F</span>
    </div>
    
    <div class="liasse-row" style="border-left:4px solid #3b82f6;">
      <span style="font-weight:600;">III. TVA Déductible sur Achats (Comptes 445)</span>
      <span style="font-weight:700;">{{ number_format($tvaDeductible, 0, ',', ' ') }} F</span>
    </div>

    <div class="liasse-row total" style="margin-top:16px; padding:16px; font-size:16px; border-left:4px solid {{ $isCredit ? '#16a34a' : '#ef4444' }}; background:{{ $isCredit ? '#f0fdf4' : '#fee2e2' }}; border-color:{{ $isCredit ? '#bbf7d0' : '#fecaca' }};">
      <span style="color:{{ $isCredit ? '#16a34a' : '#dc2626' }};">{{ $isCredit ? 'IV. Crédit de TVA' : 'IV. TVA Nette à Payer' }}</span>
      <span style="font-weight:800; color:{{ $isCredit ? '#16a34a' : '#dc2626' }};">{{ number_format($montantAbs, 0, ',', ' ') }} F</span>
    </div>
  </div>
  
  <div style="text-align:center; font-size:12px; color:var(--sec-muted); margin-top:40px; border-top:1px solid var(--sec-border); padding-top:16px;">
    Document généré par GEL® Secrétariat Autonome le {{ date('d/m/Y à H:i') }}.<br>
    Ce document est un brouillon de liasse fiscale basé sur les écritures comptables.
  </div>

</div>

@endsection
