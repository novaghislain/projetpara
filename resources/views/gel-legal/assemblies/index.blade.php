@extends('layouts.gel-legal')
@section('title', 'Assemblées')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">Assemblées (AGO / AGE)</h1>
  </div>
  <button class="sec-btn sec-btn-primary" onclick="document.getElementById('addAssemblyModal').style.display='flex'">
    <i class="fas fa-plus"></i> Planifier Assemblée
  </button>
</div>

<div class="sec-card">
  <div class="sec-card-body p-0">
    <table class="sec-table">
      <thead>
        <tr>
          <th>Type</th>
          <th>Date Prévue</th>
          <th>Ordre du Jour</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        @foreach($assemblies as $a)
        <tr>
          <td style="font-weight:600;">{{ strtoupper($a->type) }}</td>
          <td>{{ \Carbon\Carbon::parse($a->date_assemblee)->format('d/m/Y') }}</td>
          <td>{{ \Illuminate\Support\Str::limit($a->ordre_du_jour, 50) }}</td>
          <td><span class="sec-badge sec-badge-warning">Planifiée</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($assemblies->isEmpty())
      <div class="gel-empty">
        <i class="fas fa-users-cog"></i>
        <h3>Aucune assemblée</h3>
        <p>Les assemblées planifiées s'afficheront ici.</p>
      </div>
    @endif
  </div>
</div>

<div id="addAssemblyModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; width:500px; border-radius:8px; padding:24px;">
        <h3 style="margin-top:0;">Planifier une assemblée</h3>
        <form action="{{ route('gel-legal.assemblies.store') }}" method="POST">
            @csrf
            <div class="sec-form-group">
                <label>Type d'assemblée</label>
                <select name="type" class="sec-form-control">
                    <option value="AGO">AGO (Ordinaire)</option>
                    <option value="AGE">AGE (Extraordinaire)</option>
                    <option value="MIXTE">Mixte</option>
                </select>
            </div>
            <div class="sec-form-group">
                <label>Date</label>
                <input type="date" name="date_assemblee" class="sec-form-control" required>
            </div>
            <div class="sec-form-group">
                <label>Ordre du jour</label>
                <textarea name="ordre_du_jour" class="sec-form-control" rows="3" required></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="sec-btn sec-btn-secondary" onclick="document.getElementById('addAssemblyModal').style.display='none'">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
