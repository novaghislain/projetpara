@extends('layouts.gel-secretary')
@section('title', 'Bulletins de Paie — ' . $client->nom_entreprise)

@push('styles')
<style>
  @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
  .animate-fade { animation:fadeUp 0.3s ease-out forwards; opacity:0; }
  .delay-1 { animation-delay:0.05s; }
  .delay-2 { animation-delay:0.1s; }

  .bulletin-card {
    background:white;
    border:1px solid var(--sec-border);
    border-radius:14px;
    padding:20px;
    position:relative;
    overflow:hidden;
    transition:all 0.2s;
  }
  .bulletin-card::before {
    content:'';
    position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg, #0f766e, #16a34a);
  }
  .bulletin-card.valide::before { background:linear-gradient(90deg, #16a34a, #4ade80); }
  .bulletin-card:hover { box-shadow:0 8px 24px rgba(0,0,0,0.07); border-color:#cbd5e1; }

  .breakdown-row {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:6px 0;
    font-size:13px;
    border-bottom:1px dashed #f1f5f9;
  }
  .breakdown-row:last-child { border-bottom:none; }
</style>
@endpush

@section('content')

{{-- En-tête avec sélecteur de mois --}}
<div class="animate-fade" style="background:white; border:1px solid var(--sec-border); border-radius:16px; padding:24px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.04);">
  <div>
    <h1 style="font-size:22px; font-weight:700; color:var(--sec-text); margin-bottom:6px; letter-spacing:-0.5px;">
      <i class="fas fa-file-invoice-dollar" style="color:var(--sec-primary); margin-right:8px;"></i>
      Bulletins de Paie — {{ $client->nom_entreprise }}
    </h1>
    <p style="font-size:14px; color:var(--sec-muted); margin:0;">
      Génération mensuelle · CNSS 3.36% (salarié, plafond 450 000 F) · ITS progressif (Bénin)
    </p>
  </div>
  <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
    {{-- Sélecteur de mois/année --}}
    <form method="GET" action="{{ route('gel-secretary.rh.paie.index') }}" style="display:flex; gap:8px; align-items:center;">
      <input type="hidden" name="client_id" value="{{ $client->id }}">
      <select name="mois" onchange="this.form.submit()"
        style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 12px; font-size:13px; font-weight:600; background:white;">
        @foreach(['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin','07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'] as $num => $nom)
          <option value="{{ (int)$num }}" {{ (int)$mois == (int)$num ? 'selected' : '' }}>{{ $nom }}</option>
        @endforeach
      </select>
      <select name="annee" onchange="this.form.submit()"
        style="border:1px solid #e2e8f0; border-radius:8px; padding:8px 12px; font-size:13px; font-weight:600; background:white;">
        @for($y = date('Y'); $y >= 2022; $y--)
          <option value="{{ $y }}" {{ $annee == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
      </select>
    </form>

    {{-- Générer tous --}}
    <form method="POST" action="{{ route('gel-secretary.rh.paie.generer') }}">
      @csrf
      <input type="hidden" name="client_id" value="{{ $client->id }}">
      <input type="hidden" name="mois" value="{{ $mois }}">
      <input type="hidden" name="annee" value="{{ $annee }}">
      <button type="submit"
        style="background:var(--sec-primary); color:white; border:none; border-radius:10px; padding:10px 18px; font-size:14px; font-weight:600; cursor:pointer;">
        <i class="fas fa-magic"></i> Générer les Bulletins
      </button>
    </form>

    <a href="{{ route('gel-secretary.rh.salaries.index', ['client_id' => $client->id]) }}"
      style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; padding:10px 16px; text-decoration:none; font-size:14px; font-weight:600;">
      <i class="fas fa-users"></i> Personnel
    </a>
  </div>
</div>

@if(session('success'))
  <div class="animate-fade" style="background:#ecfdf5; border:1px solid #a7f3d0; color:#059669; padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:14px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
  </div>
@endif

{{-- Résumé Mensuel --}}
@php
  $totalBrut = $bulletins->sum('salaire_brut');
  $totalCNSS = $bulletins->sum('retenue_cnss');
  $totalITS  = $bulletins->sum('retenue_its');
  $totalNet  = $bulletins->sum('salaire_net');
  $moisNoms  = ['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin','07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'];
  $moisLabel = $moisNoms[str_pad($mois, 2, '0', STR_PAD_LEFT)] ?? $mois;
@endphp
<div class="animate-fade delay-1" style="background:linear-gradient(135deg, var(--sec-primary), #0d9488); border-radius:16px; padding:24px; margin-bottom:24px; color:white;">
  <div style="font-size:14px; font-weight:600; opacity:0.8; margin-bottom:16px; text-transform:uppercase; letter-spacing:1px;">
    Masse salariale — {{ $moisLabel }} {{ $annee }}
  </div>
  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:20px;">
    @foreach([
      ['Salaires Bruts', number_format($totalBrut, 0, ',', ' ') . ' F'],
      ['Retenues CNSS', number_format($totalCNSS, 0, ',', ' ') . ' F'],
      ['Retenues ITS + ORTB', number_format($totalITS, 0, ',', ' ') . ' F'],
      ['Total Nets', number_format($totalNet, 0, ',', ' ') . ' F'],
    ] as [$label, $val])
    <div>
      <div style="font-size:11px; opacity:0.7; font-weight:600; margin-bottom:4px;">{{ $label }}</div>
      <div style="font-size:20px; font-weight:800;">{{ $val }}</div>
    </div>
    @endforeach
  </div>
</div>

{{-- Bulletins --}}
@if($salaries->isEmpty())
  <div class="animate-fade delay-2" style="background:white; border:1px dashed #cbd5e1; border-radius:16px; padding:60px; text-align:center;">
    <i class="fas fa-users" style="font-size:48px; color:#e2e8f0; display:block; margin-bottom:16px;"></i>
    <p style="font-size:15px; color:var(--sec-muted); font-weight:500;">Aucun salarié actif. Ajoutez d'abord des employés dans le registre du personnel.</p>
    <a href="{{ route('gel-secretary.rh.salaries.index', ['client_id' => $client->id]) }}"
      style="display:inline-block; margin-top:16px; background:var(--sec-primary); color:white; border-radius:10px; padding:10px 20px; text-decoration:none; font-size:14px; font-weight:600;">
      <i class="fas fa-users"></i> Gérer le Personnel
    </a>
  </div>
@else
  <div class="animate-fade delay-2" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:18px;">
    @foreach($salaries as $salarie)
    @php
      $bulletin = $bulletins->get($salarie->id);
      $isValide = $bulletin && $bulletin->statut === 'valide';
    @endphp
    <div class="bulletin-card {{ $isValide ? 'valide' : '' }}">
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
        <div style="display:flex; align-items:center; gap:10px;">
          <div style="width:42px; height:42px; border-radius:50%; background:{{ $isValide ? '#dcfce7' : '#e0f2fe' }}; color:{{ $isValide ? '#16a34a' : '#0369a1' }}; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; flex-shrink:0;">
            {{ strtoupper(substr($salarie->prenom, 0, 1) . substr($salarie->nom, 0, 1)) }}
          </div>
          <div>
            <div style="font-weight:700; font-size:15px; color:var(--sec-text);">{{ $salarie->prenom }} {{ $salarie->nom }}</div>
            <div style="font-size:11px; color:var(--sec-muted);">Base : {{ number_format($salarie->salaire_base, 0, ',', ' ') }} F</div>
          </div>
        </div>
        @if($bulletin)
          <span style="background:{{ $isValide ? '#dcfce7' : '#fef3c7' }}; color:{{ $isValide ? '#16a34a' : '#d97706' }}; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">
            {{ $isValide ? '✓ Validé' : '⏳ Brouillon' }}
          </span>
        @else
          <span style="background:#f1f5f9; color:#94a3b8; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">Non généré</span>
        @endif
      </div>

      @if($bulletin)
        {{-- Détail du bulletin --}}
        <div style="background:#f8fafc; border-radius:10px; padding:14px; margin-bottom:14px;">
          <div class="breakdown-row">
            <span style="color:var(--sec-muted);">Salaire Brut</span>
            <span style="font-weight:700;">{{ number_format($bulletin->salaire_brut, 0, ',', ' ') }} F</span>
          </div>
          <div class="breakdown-row" style="color:#dc2626;">
            <span>— CNSS Salarié (3.36 %)</span>
            <span style="font-weight:600;">— {{ number_format($bulletin->retenue_cnss, 0, ',', ' ') }} F</span>
          </div>
          <div class="breakdown-row" style="color:#d97706;">
            <span>— ITS + ORTB (retenue DGI)</span>
            <span style="font-weight:600;">— {{ number_format($bulletin->retenue_its, 0, ',', ' ') }} F</span>
          </div>
          <div style="border-top:2px solid #e2e8f0; margin-top:8px; padding-top:8px; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-weight:700; font-size:13px; color:var(--sec-text);">Salaire Net à Payer</span>
            <span style="font-weight:800; font-size:16px; color:#059669;">{{ number_format($bulletin->salaire_net, 0, ',', ' ') }} F</span>
          </div>
        </div>

        @if(!$isValide)
        <form method="POST" action="{{ route('gel-secretary.rh.paie.valider', $bulletin->id) }}">
          @csrf
          <button type="submit"
            style="width:100%; background:#dcfce7; color:#16a34a; border:1px solid #a7f3d0; border-radius:9px; padding:9px; font-size:13px; font-weight:700; cursor:pointer; transition:all 0.2s;"
            onmouseover="this.style.background='#16a34a';this.style.color='white';"
            onmouseout="this.style.background='#dcfce7';this.style.color='#16a34a';">
            <i class="fas fa-check"></i> Valider ce Bulletin
          </button>
        </form>
        @else
        <div style="display:flex; align-items:center; justify-content:center; gap:6px; padding:9px; background:#f0fdf4; border-radius:9px; font-size:13px; font-weight:700; color:#16a34a;">
          <i class="fas fa-check-circle"></i> Bulletin Validé
        </div>
        @endif
      @else
        <div style="text-align:center; padding:16px; color:var(--sec-muted); font-size:13px;">
          <i class="fas fa-file-alt" style="font-size:28px; margin-bottom:8px; display:block; color:#e2e8f0;"></i>
          Cliquez sur "Générer les Bulletins" pour créer le bulletin de ce mois.
        </div>
      @endif
    </div>
    @endforeach
  </div>
@endif

@endsection
