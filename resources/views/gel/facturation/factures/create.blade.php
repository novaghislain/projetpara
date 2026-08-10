@extends('layouts.gel-accountant')

@section('content')
<div class="gel-facture-create-container max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Nouvelle Facture</h1>
            <p class="text-sm text-gray-500">Créez une facture et ajoutez les lignes de produits/services.</p>
        </div>
        <a href="{{ url('/gel/facturation/factures') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <form id="facture-form" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <!-- Informations Générales -->
        <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Informations Générales</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select id="type" class="w-full border rounded-lg px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="client">Facture Client</option>
                    <option value="fournisseur">Facture Fournisseur</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Facture</label>
                <input type="date" id="date_facture" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Client CRM</label>
                <select id="client_id" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    <!-- Loaded via JS -->
                </select>
                <input type="hidden" id="client_nom">
            </div>
        </div>

        <!-- Lignes de Facture -->
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-lg font-bold text-gray-800">Lignes de la facture</h2>
            <button type="button" onclick="addLigne()" class="text-sm px-3 py-1 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition-colors">
                <i class="fas fa-plus"></i> Ajouter une ligne
            </button>
        </div>
        
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                        <th class="p-3 w-1/3">Désignation</th>
                        <th class="p-3 w-1/6">Quantité</th>
                        <th class="p-3 w-1/6">Prix Unitaire</th>
                        <th class="p-3 w-1/6">TVA (%)</th>
                        <th class="p-3 text-right">Total HT</th>
                        <th class="p-3 w-10"></th>
                    </tr>
                </thead>
                <tbody id="lignes-tbody" class="text-gray-700 text-sm">
                    <!-- Lignes injectées par JS -->
                </tbody>
            </table>
        </div>

        <!-- Totaux -->
        <div class="flex justify-end mb-8">
            <div class="w-1/3 bg-gray-50 rounded-lg p-4 border border-gray-100">
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Total HT</span>
                    <span class="font-bold text-gray-800" id="total-ht">0 FCFA</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Total TVA</span>
                    <span class="font-bold text-gray-800" id="total-tva">0 FCFA</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-200 mt-2">
                    <span class="text-lg font-bold text-gray-900">Total TTC</span>
                    <span class="text-lg font-bold text-blue-600" id="total-ttc">0 FCFA</span>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <button type="button" onclick="window.history.back()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Annuler</button>
            <button type="button" onclick="saveFacture()" class="px-6 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition-colors font-medium">Enregistrer la facture</button>
        </div>
    </form>
</div>

<script>
    let lignes = [];

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('date_facture').valueAsDate = new Date();
        loadClients();
        addLigne(); // add first line by default
    });

    function loadClients() {
        fetch('/api/gel/crm/clients')
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('client_id');
                select.innerHTML = '<option value="">Sélectionnez un client...</option>';
                data.forEach(c => {
                    select.innerHTML += `<option value="${c.id}" data-name="${c.first_name} ${c.last_name}">${c.company || (c.first_name + ' ' + c.last_name)}</option>`;
                });
            });
    }

    function addLigne() {
        const id = Date.now();
        lignes.push({
            id: id,
            designation: '',
            quantite: 1,
            prix_unitaire: 0,
            taux_tva: 18
        });
        renderLignes();
    }

    function removeLigne(id) {
        lignes = lignes.filter(l => l.id !== id);
        renderLignes();
    }

    function updateLigne(id, field, value) {
        const ligne = lignes.find(l => l.id === id);
        if (ligne) {
            ligne[field] = value;
            renderLignes();
        }
    }

    function renderLignes() {
        const tbody = document.getElementById('lignes-tbody');
        tbody.innerHTML = '';
        
        let totalHtGlobal = 0;
        let totalTvaGlobal = 0;

        lignes.forEach(ligne => {
            const totalHt = (ligne.quantite || 0) * (ligne.prix_unitaire || 0);
            const totalTva = totalHt * ((ligne.taux_tva || 0) / 100);
            
            totalHtGlobal += totalHt;
            totalTvaGlobal += totalTva;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="p-2">
                    <input type="text" placeholder="Désignation du produit/service" value="${ligne.designation}" onchange="updateLigne(${ligne.id}, 'designation', this.value)" class="w-full border rounded px-2 py-1 outline-none focus:ring-1 focus:ring-blue-500">
                </td>
                <td class="p-2">
                    <input type="number" step="0.01" min="0" value="${ligne.quantite}" onchange="updateLigne(${ligne.id}, 'quantite', parseFloat(this.value))" class="w-full border rounded px-2 py-1 outline-none focus:ring-1 focus:ring-blue-500">
                </td>
                <td class="p-2">
                    <input type="number" step="0.01" min="0" value="${ligne.prix_unitaire}" onchange="updateLigne(${ligne.id}, 'prix_unitaire', parseFloat(this.value))" class="w-full border rounded px-2 py-1 outline-none focus:ring-1 focus:ring-blue-500">
                </td>
                <td class="p-2">
                    <select onchange="updateLigne(${ligne.id}, 'taux_tva', parseFloat(this.value))" class="w-full border rounded px-2 py-1 outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="18" ${ligne.taux_tva === 18 ? 'selected' : ''}>18%</option>
                        <option value="0" ${ligne.taux_tva === 0 ? 'selected' : ''}>0%</option>
                    </select>
                </td>
                <td class="p-2 text-right font-semibold text-gray-700">
                    ${new Intl.NumberFormat('fr-FR').format(totalHt)}
                </td>
                <td class="p-2 text-center">
                    <button type="button" onclick="removeLigne(${ligne.id})" class="text-red-400 hover:text-red-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Update Totals
        document.getElementById('total-ht').innerText = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(totalHtGlobal);
        document.getElementById('total-tva').innerText = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(totalTvaGlobal);
        document.getElementById('total-ttc').innerText = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(totalHtGlobal + totalTvaGlobal);
    }

    function saveFacture() {
        const clientIdSelect = document.getElementById('client_id');
        const clientId = clientIdSelect.value;
        const clientNom = clientIdSelect.options[clientIdSelect.selectedIndex]?.dataset.name || '';
        
        if (!clientId) {
            alert("Veuillez sélectionner un client.");
            return;
        }

        const data = {
            entreprise_id: '{{ auth()->user()->entreprise_id ?? "00000000-0000-0000-0000-000000000000" }}', // Temporary for testing
            client_id: clientId,
            type: document.getElementById('type').value,
            client_nom: clientNom,
            date_facture: document.getElementById('date_facture').value,
            statut: 'Brouillon',
            lignes: lignes
        };

        fetch('/api/gel/facturation/factures', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            if (res.error) {
                alert("Erreur: " + res.error);
            } else {
                window.location.href = '/gel/facturation/factures';
            }
        });
    }
</script>
@endsection
