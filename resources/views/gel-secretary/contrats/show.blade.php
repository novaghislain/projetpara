@extends('layouts.gel-secretary')
@section('title', 'Détails du Contrat')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title"><i class="fas fa-file-contract" style="color:var(--sec-primary); margin-right:8px;"></i>Détails du Contrat</h1>
  </div>
  <div>
    <a href="{{ route('gel-secretary.contrats.index') }}" class="sec-btn sec-btn-outline">
      <i class="fas fa-arrow-left"></i> Retour
    </a>
  </div>
</div>

<div class="sec-card animate-fade delay-2">
  <div class="sec-card-body">
    <h4>{{ $contrat->titre }}</h4>
    <p>Partie adverse : {{ $contrat->partie_adverse }}</p>
    <p>Type : {{ $contrat->type_contrat }}</p>
    <p>Statut : {{ $contrat->statut }}</p>
  </div>
</div>
@endsection
