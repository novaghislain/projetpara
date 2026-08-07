@extends(auth()->user()->account_type === 'informaticien' ? 'layouts.gel-informaticien' : 'layouts.gel-communication')

@section('content')
<div class="container-fluid py-4" style="font-family: 'Times New Roman', serif;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark" style="font-family: 'Times New Roman', serif; font-weight: bold;">Calendrier Éditorial (Vue Agence)</h1>
            <p class="text-muted mb-0">Planification stratégique et détaillée par plateforme</p>
        </div>
        <div>
            <a href="{{ route('gel-communication.calendar.export') }}" class="btn btn-sm btn-outline-secondary me-2" style="font-family: 'Times New Roman', serif;">
                Exporter (Excel)
            </a>
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#quickAddModal" style="font-family: 'Times New Roman', serif;">
                Ajouter un contenu
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-3" id="calendarTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active text-dark bg-light border" id="agenda-tab" data-bs-toggle="tab" data-bs-target="#agenda-pane" type="button" role="tab" style="font-family: 'Times New Roman', serif;">
                Agenda Mois 1
            </button>
        </li>
        @foreach($platforms as $index => $platform)
            <li class="nav-item" role="presentation">
                <button class="nav-link text-dark bg-light border ms-1" id="plat-{{ Str::slug($platform) }}-tab" data-bs-toggle="tab" data-bs-target="#plat-{{ Str::slug($platform) }}-pane" type="button" role="tab" style="font-family: 'Times New Roman', serif;">
                    CE {{ strtoupper($platform) }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content bg-white" id="calendarTabsContent">
        
        <!-- Agenda Tab -->
        <div class="tab-pane fade show active" id="agenda-pane" role="tabpanel" tabindex="0">
            <div class="table-responsive">
                <table class="table table-bordered border-dark text-center align-middle" style="font-family: 'Times New Roman', serif;">
                    <thead>
                        <tr>
                            <th style="width: 20%;">THÈME DU MOIS</th>
                            <th style="width: 20%;">SEMAINE 1</th>
                            <th style="width: 20%;">SEMAINE 2</th>
                            <th style="width: 20%;">SEMAINE 3</th>
                            <th style="width: 20%;">SEMAINE 4 (optionnel)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start">THÈMES</td>
                            <td colspan="4" class="text-start text-muted">Les thèmes sont définis dans les contenus détaillés.</td>
                        </tr>
                        <tr>
                            <td class="text-start">Nouveauté IA</td>
                            <td colspan="4" class="text-start">
                                <strong>Explication du calendrier éditorial :</strong><br>
                                <strong>Lundi :</strong> Dédié à des thématiques stratégiques autour de RéFIA comme la présentation, la gouvernance, et les partenariats.<br>
                                <strong>Mercredi :</strong> Axé sur les nouveautés IA dans le monde, avec des focus sur les dernières avancées et leurs impacts dans divers secteurs (éducation, santé, environnement).<br>
                                <strong>Vendredi :</strong> Focalisé sur des contenus plus personnels et interactifs, comme des interviews d'experts, des témoignages, et des retours sur les événements.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Platform Tabs -->
        @foreach($platforms as $index => $platform)
            <div class="tab-pane fade" id="plat-{{ Str::slug($platform) }}-pane" role="tabpanel" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-bordered border-dark align-middle agency-table" style="font-family: 'Times New Roman', serif; font-size: 0.9rem;">
                        <thead>
                            <tr>
                                <th colspan="10" class="text-center text-white py-2 fs-5" style="background-color: #800000; font-weight: bold; text-transform: uppercase;">
                                    {{ $platform }}
                                </th>
                            </tr>
                            <tr style="background-color: #f4cccc; color: #800000;" class="text-center">
                                <th colspan="10" class="py-1">PLANNING DE PUBLICATION DU -- / -- / --</th>
                            </tr>
                            <tr class="text-center align-middle" style="background-color: #f3f3f3; font-weight: bold;">
                                <th style="width: 6%">Jour</th>
                                <th style="width: 6%">Heure</th>
                                <th style="width: 8%">Canal</th>
                                <th style="width: 10%">Pilier</th>
                                <th style="width: 15%">Thème</th>
                                <th style="width: 25%">Description</th>
                                <th style="width: 10%">Format</th>
                                <th style="width: 10%">Mot. clés</th>
                                <th style="width: 5%">RSD</th>
                                <th style="width: 10%">Lien de la publication</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $platContents = $platformContents->get(strtolower($platform), collect());
                                // Group by Day of week
                                $groupedByDay = $platContents->groupBy(function($item) {
                                    return $item->publish_date->translatedFormat('l');
                                });
                                // Force order: Lundi, Mardi, Mercredi...
                                $daysOrder = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
                                $sortedDays = $groupedByDay->sortBy(function($value, $key) use ($daysOrder) {
                                    $pos = array_search(strtolower($key), $daysOrder);
                                    return $pos === false ? 99 : $pos;
                                });
                            @endphp
                            
                            @forelse($sortedDays as $dayName => $dayContents)
                                @foreach($dayContents as $idx => $content)
                                <tr>
                                    @if($idx === 0)
                                    <td rowspan="{{ $dayContents->count() }}" class="text-center fw-bold align-middle" style="border-right: 2px solid #800000;">
                                        {{ ucfirst($dayName) }}
                                    </td>
                                    @endif
                                    <td class="text-center">
                                        <div class="editable-cell" data-id="{{ $content->id }}" data-field="publish_date" data-type="datetime-local">
                                            {{ $content->publish_date->format('H\hi') }}
                                        </div>
                                    </td>
                                    <td class="text-center">{{ ucfirst($content->platform) }}</td>
                                    <td>
                                        <div class="editable-cell" data-id="{{ $content->id }}" data-field="pillar">
                                            {{ $content->pillar ?? '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="editable-cell" data-id="{{ $content->id }}" data-field="theme">
                                            {{ $content->theme ?? '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="editable-cell" data-id="{{ $content->id }}" data-field="description" data-type="textarea">
                                            {{ $content->description ?? '' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="editable-cell" data-id="{{ $content->id }}" data-field="content_format">
                                            {{ $content->content_format ?? '' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="editable-cell" data-id="{{ $content->id }}" data-field="keywords">
                                            {{ $content->keywords ?? '' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="editable-cell" data-id="{{ $content->id }}" data-field="rsd">
                                            {{ $content->rsd ?? '' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="editable-cell" data-id="{{ $content->id }}" data-field="published_link">
                                            {{ $content->published_link ?? '' }}
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                <!-- Separator row to match design -->
                                <tr><td colspan="10" style="background-color: #800000; height: 10px; padding: 0;"></td></tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">Aucune publication planifiée pour ce canal.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
.agency-table th, .agency-table td {
    border: 1px solid #000 !important;
}
.editable-cell {
    min-height: 20px;
    cursor: pointer;
    padding: 2px;
    border-radius: 2px;
}
.editable-cell:hover {
    background-color: #f8f9fa;
    outline: 1px dashed #ccc;
}
.editing-input {
    width: 100%;
    border: 1px solid #0d6efd;
    border-radius: 2px;
    padding: 2px;
    font-size: inherit;
    font-family: inherit;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cells = document.querySelectorAll('.editable-cell');
    
    cells.forEach(cell => {
        cell.addEventListener('click', function(e) {
            if (this.querySelector('input') || this.querySelector('textarea')) return;
            
            const currentValue = this.innerText.trim();
            const id = this.getAttribute('data-id');
            const field = this.getAttribute('data-field');
            const type = this.getAttribute('data-type') || 'text';
            
            let input;
            if (type === 'textarea') {
                input = document.createElement('textarea');
                input.className = 'editing-input';
                input.rows = 4;
            } else {
                input = document.createElement('input');
                input.type = type;
                input.className = 'editing-input';
            }
            
            input.value = currentValue;
            
            this.innerHTML = '';
            this.appendChild(input);
            input.focus();
            
            const saveValue = async () => {
                const newValue = input.value;
                this.innerHTML = newValue;
                
                if (newValue !== currentValue) {
                    try {
                        const response = await fetch("{{ route('gel-communication.calendar.updateCell') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id: id,
                                field: field,
                                value: newValue
                            })
                        });
                        const data = await response.json();
                        if (!data.success) {
                            alert('Erreur lors de la sauvegarde');
                            this.innerHTML = currentValue;
                        } else {
                            if (field === 'publish_date') window.location.reload();
                        }
                    } catch (error) {
                        console.error('Error saving:', error);
                        this.innerHTML = currentValue;
                    }
                }
            };
            
            input.addEventListener('blur', saveValue);
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && type !== 'textarea') {
                    saveValue();
                }
                if (e.key === 'Escape') {
                    cell.innerHTML = currentValue;
                }
            });
        });
    });
});
</script>

<!-- Quick Add Modal omitted for brevity, we can append it if needed -->
@endsection