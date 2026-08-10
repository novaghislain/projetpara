@extends('layouts.gel-accountant')

@section('content')
<div class="gel-factures-container">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Factures & Devis</h1>
            <p class="text-sm text-gray-500">Gérez la facturation de vos clients et suivez les paiements.</p>
        </div>
        <a href="{{ url('/gel/facturation/factures/create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition-all flex items-center gap-2">
            <i class="fas fa-file-invoice"></i> Nouvelle Facture
        </a>
    </div>

    <!-- Filtres -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex gap-4">
        <div class="flex-1 relative">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            <input type="text" id="search-facture" placeholder="Rechercher par N°, Client..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
        <select class="border rounded-lg px-4 py-2 bg-gray-50 text-gray-700 outline-none">
            <option value="">Tous les statuts</option>
            <option value="Brouillon">Brouillon</option>
            <option value="Validée">Validée</option>
            <option value="Payée">Payée</option>
        </select>
    </div>

    <!-- Table des factures -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b">
                    <th class="p-4 font-semibold">Numéro</th>
                    <th class="p-4 font-semibold">Client</th>
                    <th class="p-4 font-semibold">Date</th>
                    <th class="p-4 font-semibold text-right">Montant TTC</th>
                    <th class="p-4 font-semibold">Statut</th>
                    <th class="p-4 font-semibold text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="factures-tbody" class="text-gray-700 text-sm divide-y">
                <!-- Data loaded via JS -->
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadFactures();
    });

    function loadFactures() {
        fetch('/api/gel/facturation/factures')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('factures-tbody');
                tbody.innerHTML = '';
                data.forEach(facture => {
                    let badgeClass = 'bg-gray-100 text-gray-700';
                    if (facture.statut === 'Validée') badgeClass = 'bg-blue-100 text-blue-700';
                    if (facture.statut === 'Payée') badgeClass = 'bg-green-100 text-green-700';

                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-blue-50 transition-colors group cursor-pointer';
                    tr.innerHTML = `
                        <td class="p-4 font-semibold text-gray-800">${facture.numero}</td>
                        <td class="p-4">${facture.client_nom}</td>
                        <td class="p-4">${new Date(facture.date_facture).toLocaleDateString()}</td>
                        <td class="p-4 text-right font-bold text-gray-900">${new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(facture.total_ttc)}</td>
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold ${badgeClass}">
                                ${facture.statut}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button onclick="validerFacture(${facture.id})" class="w-8 h-8 rounded-full bg-green-50 text-green-600 hover:bg-green-600 hover:text-white transition-colors flex items-center justify-center" title="Valider">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="deleteFacture(${facture.id})" class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors flex items-center justify-center" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    function validerFacture(id) {
        if (confirm("Valider cette facture générera les écritures comptables. Continuer ?")) {
            fetch(`/api/gel/facturation/factures/${id}/valider`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.error) alert(data.error);
                loadFactures();
            });
        }
    }

    function deleteFacture(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cette facture ?")) {
            fetch(`/api/gel/facturation/factures/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(() => {
                loadFactures();
            });
        }
    }
</script>
@endsection
