import re

file_path = "c:\\xampp\\htdocs\\Para\\resources\\views\\layouts\\gel-app.blade.php"

with open(file_path, "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

# 1. Replace Topbar Client Switcher
topbar_replacement = """    {{-- Sélecteur d'entreprise --}}
    @php
      $user = auth()->user();
      $affectations = $user ? $user->affectations()->where('statut', 'active')->with('entreprise')->get() : collect();
      $entreprises = $affectations->pluck('entreprise')->unique('id');
      $activeEntrepriseId = session('active_entreprise_id');
      $activeEntreprise = $activeEntrepriseId ? $entreprises->firstWhere('id', $activeEntrepriseId) : null;
    @endphp

    <div class="sec-client-switcher" onclick="toggleClientDropdown()" id="clientSwitcher">
      <i class="fas fa-building" style="opacity:.8;font-size:13px;"></i>
      <span class="name">{{ $activeEntreprise?->raison_sociale ?? 'Sélectionner une entreprise' }}</span>
      <i class="fas fa-chevron-down" style="font-size:10px;opacity:.7;"></i>

      <div class="client-dropdown" id="clientDropdown">
        @forelse($entreprises as $entreprise)
          <form method="POST" action="{{ route('dashboard.switch-entreprise') }}" style="margin:0;">
            @csrf
            <input type="hidden" name="entreprise_id" value="{{ $entreprise->id }}">
            <button type="submit" class="client-dd-item w-100 border-0 text-start {{ $activeEntreprise?->id == $entreprise->id ? 'active' : '' }}">
              <div class="client-dd-avatar">{{ strtoupper(substr($entreprise->raison_sociale ?? 'E', 0, 2)) }}</div>
              <div>
                <div style="font-size:13px;">{{ $entreprise->raison_sociale }}</div>
                <div style="font-size:11px;color:var(--sec-text-muted);">{{ $entreprise->secteur_activite ?? '—' }}</div>
              </div>
            </button>
          </form>
        @empty
          <div class="client-dd-item" style="color:var(--sec-text-muted);">Aucune entreprise enregistrée</div>
        @endforelse
      </div>
    </div>"""

# Replace everything from `{{-- Sélecteur d'entreprise --}}` to the `</div>` that closes `@endif`
content = re.sub(r'\{\{-- Sélecteur d\'entreprise --\}\}.*?@endif\s*</div>\s*@endif', lambda m: topbar_replacement, content, flags=re.DOTALL)

# 2. Replace Sidebar Navigation
sidebar_replacement = """    <ul class="sec-nav">
      <li class="sec-nav-section">Général</li>
      <li>
        <a href="{{ route('dashboard') }}" class="sec-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <i class="fas fa-tachometer-alt"></i> Accueil
        </a>
      </li>

      @if(isset($activeEntreprise) && $activeEntreprise)
        @php
            $modulesActifs = \App\Models\ModuleEntreprise::where('entreprise_id', $activeEntreprise->id)
                                ->where('actif', true)
                                ->pluck('module_code')
                                ->toArray();
            $affectation = request()->attributes->get('current_affectation');
        @endphp

        @if(in_array('secretariat', $modulesActifs))
          <li class="sec-nav-section">Secrétariat</li>
          <li><a href="#" class="sec-nav-item"><i class="fas fa-envelope-open-text"></i> Courriers</a></li>
          <li><a href="#" class="sec-nav-item"><i class="fas fa-address-book"></i> Contacts</a></li>
          <li><a href="#" class="sec-nav-item"><i class="fas fa-folder-open"></i> Documents</a></li>
        @endif

        @if(in_array('comptabilite', $modulesActifs))
          <li class="sec-nav-section">Comptabilité</li>
          <li><a href="#" class="sec-nav-item"><i class="fas fa-file-invoice-dollar"></i> Facturation</a></li>
          <li><a href="#" class="sec-nav-item"><i class="fas fa-book"></i> Grand Livre</a></li>
          <li><a href="#" class="sec-nav-item"><i class="fas fa-chart-pie"></i> Bilan</a></li>
        @endif
        
        @if(in_array('rh', $modulesActifs))
          <li class="sec-nav-section">Ressources Humaines</li>
          <li><a href="#" class="sec-nav-item"><i class="fas fa-users"></i> Employés</a></li>
          <li><a href="#" class="sec-nav-item"><i class="fas fa-calendar-check"></i> Congés</a></li>
        @endif

        <li class="sec-nav-section">Paramètres</li>
        <li>
          <a href="#" class="sec-nav-item">
            <i class="fas fa-cogs"></i> Configuration Entreprise
          </a>
        </li>
      @else
        <li class="sec-nav-section">Mes Entreprises</li>
        <li>
          <a href="#" class="sec-nav-item" style="color: #F59E0B;">
            <i class="fas fa-exclamation-triangle"></i> Veuillez sélectionner une entreprise
          </a>
        </li>
      @endif
    </ul>"""

content = re.sub(r'<ul class="sec-nav">.*?</ul>', lambda m: sidebar_replacement, content, flags=re.DOTALL)

with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Modification successfully applied via regex!")
