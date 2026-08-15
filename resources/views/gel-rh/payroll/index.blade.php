@extends('layouts.gel-rh')
@section('title', 'Gestion de la Paie')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">Paie & Bulletins</h1>
  </div>
  <div>
    <button class="sec-btn sec-btn-primary" onclick="document.getElementById('formPaie').submit();">
        <i class="fas fa-plus"></i> Générer ce mois
    </button>
    <form id="formPaie" action="{{ route('gel-rh.payroll.generate') }}" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="periode" value="{{ date('Y-m') }}">
    </form>
  </div>
</div>

<div class="sec-card">
  <div class="sec-card-body p-0">
    <table class="sec-table">
      <thead>
        <tr>
          <th>Période</th>
          <th>Total Salaires (Base)</th>
          <th>Total Charges</th>
          <th>Généré le</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        @foreach($payrolls as $p)
        <tr>
          <td style="font-weight:600;">{{ \Carbon\Carbon::parse($p->periode.'-01')->translatedFormat('F Y') }}</td>
          <td>{{ number_format($p->total_salaries, 0, ',', ' ') }} F</td>
          <td>{{ number_format($p->total_charges, 0, ',', ' ') }} F</td>
          <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}</td>
          <td>
            @if($p->statut == 'valide')
              <span class="sec-badge sec-badge-success"><i class="fas fa-check"></i> Validé</span>
            @else
              <span class="sec-badge sec-badge-warning">Brouillon</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($payrolls->isEmpty())
      <div class="gel-empty">
        <i class="fas fa-file-invoice-dollar"></i>
        <h3>Aucune paie</h3>
        <p>Générez la paie pour voir l'historique ici.</p>
      </div>
    @endif
  </div>
</div>
@endsection
