@extends('layouts.gel-secretary')
@section('title', 'Déclarations Fiscales — ' . $client->nom_entreprise)

@push('styles')
<style>
  @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
  .animate-fade { animation:fadeUp 0.3s ease-out forwards; opacity:0; }
  .delay-1 { animation-delay:0.05s; }
  .delay-2 { animation-delay:0.1s; }

  .dec-card {
    background:white; border:1px solid var(--sec-border); border-radius:14px;
    padding:20px; transition:all 0.2s; position:relative; overflow:hidden;
  }
  .dec-card:hover { box-shadow:0 8px 24px rgba(0,0,0,0.06); border-color:#cbd5e1; transform:translateY(-2px); }

  .status-badge {
    padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;
  }
  .status-brouillon { background:#f1f5f9; color:#64748b; }
  .status-preparee { background:#fef3c7; color:#d97706; }
  .status-deposee { background:#e0f2fe; color:#0369a1; }
  .status-validee { background:#dcfce7; color:#16a34a; }
  
  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(3px); }
  .modal-overlay.open { display:flex; }
  .modal-box { background:white; border-radius:20px; padding:28px; width:400px; max-width:95vw; box-shadow:0 24px 60px rgba(0,0,0,0.2); }
</style>
@endpush

@section('content')

{{-- En-tête --}}
<div class="animate-fade" style="background:white; border:1px solid var(--sec-border); border-radius:16px; padding:24px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.04);">
  <div>
    <h1 style="font-size:22px; font-weight:700; color:var(--sec-text); margin-bottom:6px; letter-spacing:-0.5px;">
      <i class="fas fa-file-invoice" style="color:var(--sec-primary); margin-right:8px;"></i>
      Déclarations Fiscales — {{ $client->nom_entreprise }}
    </h1>
    <p style="font-size:14px; color:var(--sec-muted); margin:0;">
      Suivi des échéances et calcul automatisé de la TVA.
    </p>
  </div>
  <div style="display:flex; gap:12px; align-items:center;">
    <button onclick="document.getElementById('modal-generate-tva').classList.add('open')"
      style="background:var(--sec-primary); color:white; border:none; border-radius:10px; padding:10px 18px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px;">
      <i class="fas fa-magic"></i> Préparer la TVA
    </button>
    <a href="{{ route('gel-secretary.clients.show', $client->id) }}"
      style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; padding:10px 16px; text-decoration:none; font-size:14px; font-weight:600;">
      <i class="fas fa-arrow-left"></i> Retour
    </a>
  </div>
</div>

@if(session('success'))
  <div class="animate-fade" style="background:#ecfdf5; border:1px solid #a7f3d0; color:#059669; padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:14px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
  </div>
@endif

{{-- Liste des déclarations --}}
@if($declarations->isEmpty())
  <div class="animate-fade delay-1" style="background:white; border:1px dashed #cbd5e1; border-radius:16px; padding:60px; text-align:center;">
    <i class="fas fa-file-invoice" style="font-size:48px; color:#e2e8f0; margin-bottom:16px; display:block;"></i>
    <p style="font-size:16px; color:var(--sec-muted); font-weight:500;">Aucune déclaration générée pour ce client.</p>
  </div>
@else
  <div class="animate-fade delay-1" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(350px, 1fr)); gap:18px;">
    @foreach($declarations as $dec)
    @php
      $moisNoms = [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
      $moisStr = $moisNoms[(int)$dec->periode_mois] . ' ' . $dec->periode_annee;
      
      $isCredit = $dec->montant_impot < 0;
      $montantAbs = abs($dec->montant_impot);
      $colorMontant = $isCredit ? '#16a34a' : '#ef4444';
      $labelMontant = $isCredit ? 'Crédit de TVA' : 'TVA Nette à Payer';
    @endphp
    
    <div class="dec-card">
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
        <div>
          <div style="font-size:12px; font-weight:700; color:var(--sec-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">{{ $dec->type_impot }} Mensuelle</div>
          <div style="font-size:18px; font-weight:800; color:var(--sec-text);">Période : {{ $moisStr }}</div>
        </div>
        <span class="status-badge status-{{ $dec->statut }}">
          {{ $dec->statut }}
        </span>
      </div>
      
      <div style="background:#f8fafc; border-radius:10px; padding:16px; margin-bottom:20px; text-align:center;">
        <div style="font-size:12px; font-weight:600; color:var(--sec-muted); text-transform:uppercase; margin-bottom:4px;">{{ $labelMontant }}</div>
        <div style="font-size:24px; font-weight:800; color:{{ $colorMontant }};">{{ number_format($montantAbs, 0, ',', ' ') }} F</div>
      </div>
      
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <span style="font-size:11px; color:var(--sec-muted);">
          Générée le {{ $dec->created_at->format('d/m/Y') }}
        </span>
        <a href="{{ route('gel-secretary.declarations.show', $dec->id) }}"
          style="background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; border-radius:8px; padding:8px 14px; text-decoration:none; font-size:13px; font-weight:700; transition:all 0.2s;">
          Consulter la Liasse <i class="fas fa-arrow-right" style="margin-left:4px;"></i>
        </a>
      </div>
    </div>
    @endforeach
  </div>
@endif

{{-- Modal Générer TVA --}}
<div id="modal-generate-tva" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h2 style="font-size:18px; font-weight:700; color:var(--sec-text);">
        <i class="fas fa-magic" style="color:var(--sec-primary); margin-right:8px;"></i>
        Préparer une Déclaration
      </h2>
      <button onclick="document.getElementById('modal-generate-tva').classList.remove('open')"
        style="background:#f1f5f9; border:none; border-radius:8px; width:32px; height:32px; cursor:pointer; font-size:16px; color:#64748b;">✕</button>
    </div>
    
    <p style="font-size:13px; color:var(--sec-muted); margin-bottom:20px;">
      Génération automatique à partir des écritures comptables du mois (comptes 443 et 445).
    </p>
    
    <form method="POST" action="{{ route('gel-secretary.declarations.store') }}">
      @csrf
      <input type="hidden" name="client_id" value="{{ $client->id }}">
      
      <div style="margin-bottom:14px;">
        <label style="font-size:12px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:5px;">Mois à déclarer</label>
        <select name="periode_mois" style="width:100%; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; font-size:14px; box-sizing:border-box;">
          @foreach(['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin','07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'] as $num => $nom)
            <option value="{{ (int)$num }}" {{ (int)date('m') == (int)$num ? 'selected' : '' }}>{{ $nom }}</option>
          @endforeach
        </select>
      </div>
      
      <div style="margin-bottom:24px;">
        <label style="font-size:12px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:5px;">Année</label>
        <select name="periode_annee" style="width:100%; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; font-size:14px; box-sizing:border-box;">
          @for($y = date('Y'); $y >= 2022; $y--)
            <option value="{{ $y }}">{{ $y }}</option>
          @endfor
        </select>
      </div>

      <div style="display:flex; gap:12px; justify-content:flex-end;">
        <button type="button" onclick="document.getElementById('modal-generate-tva').classList.remove('open')"
          style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; padding:10px 18px; font-size:14px; font-weight:600; cursor:pointer;">
          Annuler
        </button>
        <button type="submit"
          style="background:var(--sec-primary); color:white; border:none; border-radius:10px; padding:10px 20px; font-size:14px; font-weight:600; cursor:pointer;">
          Générer la Liasse
        </button>
      </div>
    </form>
  </div>
</div>

@endsection
