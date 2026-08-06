@extends('layouts.gel-secretary')
@section('title', 'Centre de Modèles — Administration')

@section('content')
<style>
  .tpl-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:20px; margin-top:24px; }
  .tpl-card {
    background:#fff; border:1px solid var(--sec-border); border-radius:12px;
    overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.04); transition:.2s;
    display:flex; flex-direction:column;
  }
  .tpl-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.09); transform:translateY(-2px); }
  .tpl-header { padding:18px 20px; border-bottom:1px solid var(--sec-border); display:flex; gap:14px; align-items:flex-start; }
  .tpl-icon { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
  .tpl-body { padding:16px 20px; flex:1; }
  .tpl-footer { padding:14px 20px; border-top:1px solid var(--sec-border); background:#F8FAFC; }
  .cat-badge { font-size:11px; background:#F1F5F9; color:#475569; padding:2px 9px; border-radius:12px; font-weight:600; }

  /* Filter tabs */
  .filter-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:4px; }
  .filter-tab { padding:7px 14px; border-radius:20px; border:1px solid var(--sec-border);
                font-size:12px; font-weight:600; color:var(--sec-text-muted); cursor:pointer;
                background:#fff; transition:.15s; }
  .filter-tab:hover { border-color:var(--sec-primary); color:var(--sec-primary); }
  .filter-tab.active { background:var(--sec-primary); color:#fff; border-color:var(--sec-primary); }
  .tpl-card.hidden { display:none; }
</style>

{{-- Header --}}
<div class="sec-page-header">
  <div>
    <div class="sec-page-title"><i class="fas fa-file-signature" style="color:var(--sec-primary);margin-right:8px;"></i>Centre de Modèles</div>
    <div class="sec-page-sub">Bibliothèque de {{ $templates->count() }} modèles avec pré-remplissage automatique des données entreprise</div>
  </div>
  @if($activeClient)
    <div class="ms-auto">
      <span class="sec-badge" style="background:#EFF6FF;color:#2563EB;border:1px solid #BFDBFE;padding:7px 14px;">
        <i class="fas fa-building me-1"></i> Contexte : {{ $activeClient->nom_entreprise ?? $activeClient->company_name }}
      </span>
    </div>
  @endif
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" style="border-radius:10px;border-left:4px solid #10B981;margin-top:16px;" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

{{-- Filtre par catégorie --}}
@php $categories = $templates->pluck('category')->unique()->sort()->values(); @endphp
<div class="filter-tabs mt-4">
  <button class="filter-tab active" data-cat="all">Tous ({{ $templates->count() }})</button>
  @foreach($categories as $cat)
    <button class="filter-tab" data-cat="{{ $cat }}">{{ $cat }} ({{ $templates->where('category',$cat)->count() }})</button>
  @endforeach
</div>

{{-- Grille de modèles --}}
<div class="tpl-grid" id="templatesGrid">
  @php
    $catColors = [
      'Communication' => ['bg'=>'#EFF6FF','color'=>'#2563EB','icon'=>'fa-envelope'],
      'Réunions'      => ['bg'=>'#F5F3FF','color'=>'#7C3AED','icon'=>'fa-users'],
      'Rapports'      => ['bg'=>'#ECFDF5','color'=>'#059669','icon'=>'fa-chart-bar'],
      'Commercial'    => ['bg'=>'#FFFBEB','color'=>'#D97706','icon'=>'fa-handshake'],
      'Juridique'     => ['bg'=>'#FEF2F2','color'=>'#DC2626','icon'=>'fa-balance-scale'],
      'RH'            => ['bg'=>'#FDF4FF','color'=>'#9333EA','icon'=>'fa-user-tie'],
      'Achats'        => ['bg'=>'#F0FDF4','color'=>'#16A34A','icon'=>'fa-shopping-cart'],
      'Logistique'    => ['bg'=>'#FFF7ED','color'=>'#EA580C','icon'=>'fa-truck'],
    ];
  @endphp

  @forelse($templates as $template)
    @php
      $meta = $catColors[$template->category] ?? ['bg'=>'#F8FAFC','color'=>'#64748B','icon'=>'fa-file-alt'];
    @endphp
    <div class="tpl-card" data-cat="{{ $template->category }}">
      <div class="tpl-header">
        <div class="tpl-icon" style="background:{{ $meta['bg'] }};color:{{ $meta['color'] }};">
          <i class="fas {{ $meta['icon'] }}"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:14px;font-weight:700;color:var(--sec-text);margin-bottom:4px;">{{ $template->name }}</div>
          <span class="cat-badge">{{ $template->category }}</span>
        </div>
      </div>
      <div class="tpl-body">
        <p style="font-size:13px;color:#64748B;margin:0;line-height:1.5;">{{ $template->description }}</p>
        @if($activeClient)
          <div style="margin-top:12px;padding:10px 12px;background:#F0FDF4;border-left:3px solid #10B981;border-radius:6px;font-size:12px;color:#059669;">
            <i class="fas fa-magic me-1"></i>
            Pré-rempli avec les données de <strong>{{ $activeClient->nom_entreprise ?? $activeClient->company_name }}</strong>
          </div>
        @endif
      </div>
      <div class="tpl-footer">
        <form method="POST" action="{{ route('gel-secretary.administration.templates.generate', $template->id) }}">
          @csrf
          <div style="display:flex;gap:10px;align-items:center;">
            @if($clients->count() > 0)
              <select name="client_id" class="form-select form-select-sm" style="border-radius:8px;flex:1;">
                <option value="">{{ $activeClient ? ($activeClient->nom_entreprise ?? $activeClient->company_name) : 'Sélectionner un client...' }}</option>
                @foreach($clients as $c)
                  <option value="{{ $c->id }}" {{ ($activeClient && $activeClient->id == $c->id) ? 'selected' : '' }}>
                    {{ $c->nom_entreprise ?? $c->company_name }}
                  </option>
                @endforeach
              </select>
            @else
              <input type="hidden" name="client_id" value="{{ $activeClient?->id }}">
            @endif
            <button type="submit" class="sec-btn sec-btn-primary" style="white-space:nowrap;">
              <i class="fas fa-magic me-1"></i> Générer
            </button>
          </div>
        </form>
      </div>
    </div>
  @empty
    <div class="col-12" style="grid-column:1/-1">
      <div style="text-align:center;padding:60px;background:#fff;border-radius:12px;border:1px solid var(--sec-border);">
        <i class="fas fa-folder-open" style="font-size:48px;color:#cbd5e1;margin-bottom:16px;"></i>
        <h3 style="color:#475569;">Aucun modèle disponible</h3>
        <p style="color:#94a3b8;">Les modèles seront disponibles après la première initialisation.</p>
      </div>
    </div>
  @endforelse
</div>

<script>
  // Filtre par catégorie sans rechargement
  document.querySelectorAll('.filter-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      var cat = btn.dataset.cat;
      document.querySelectorAll('.tpl-card').forEach(function(card) {
        if (cat === 'all' || card.dataset.cat === cat) {
          card.classList.remove('hidden');
        } else {
          card.classList.add('hidden');
        }
      });
    });
  });
</script>
@endsection
