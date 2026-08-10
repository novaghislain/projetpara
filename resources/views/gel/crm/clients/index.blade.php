@extends('layouts.gel-accountant')

@section('content')
<div class="gel-crm-clients-container">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Gestion des Clients (CRM)</h1>
            <p class="text-sm text-gray-500">Gérez vos clients finaux, leurs coordonnées et leurs catégories.</p>
        </div>
        <button onclick="openClientModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition-all flex items-center gap-2">
            <i class="fas fa-plus"></i> Nouveau Client
        </button>
    </div>

    <!-- Filtres et Recherche -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex gap-4">
        <div class="flex-1 relative">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            <input type="text" id="search-client" placeholder="Rechercher un client (nom, email...)" class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
        <select class="border rounded-lg px-4 py-2 bg-gray-50 text-gray-700 outline-none">
            <option value="">Toutes les catégories</option>
            <option value="VIP">VIP</option>
            <option value="Standard">Standard</option>
        </select>
    </div>

    <!-- Table des clients -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b">
                    <th class="p-4 font-semibold">Nom / Entreprise</th>
                    <th class="p-4 font-semibold">Contact</th>
                    <th class="p-4 font-semibold">Catégorie</th>
                    <th class="p-4 font-semibold text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="clients-tbody" class="text-gray-700 text-sm divide-y">
                <!-- Data loaded via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajout/Édition -->
<div id="client-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 transition-opacity">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden transform transition-all scale-95 opacity-0" id="client-modal-content">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-xl font-bold text-gray-800" id="modal-title">Nouveau Client</h3>
            <button onclick="closeClientModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <form id="client-form">
                <input type="hidden" id="client-id">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                        <input type="text" id="first_name" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input type="text" id="last_name" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Entreprise</label>
                        <input type="text" id="company" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <select id="category" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                            <option value="Standard">Standard</option>
                            <option value="VIP">VIP</option>
                            <option value="Prospect">Prospect</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input type="text" id="phone" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes / Tags</label>
                    <textarea id="notes" rows="2" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>
            </form>
        </div>
        <div class="p-6 border-t bg-gray-50 flex justify-end gap-3">
            <button onclick="closeClientModal()" class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">Annuler</button>
            <button onclick="saveClient()" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition-colors">Enregistrer</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadClients();
    });

    function loadClients() {
        fetch('/api/gel/crm/clients')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('clients-tbody');
                tbody.innerHTML = '';
                data.forEach(client => {
                    const initials = (client.first_name[0] + client.last_name[0]).toUpperCase();
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-blue-50 transition-colors group cursor-pointer';
                    tr.innerHTML = `
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                                    ${initials}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800">${client.first_name} ${client.last_name}</div>
                                    <div class="text-xs text-gray-500">${client.company || 'Particulier'}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="flex flex-col gap-1">
                                ${client.email ? `<span class="flex items-center gap-2 text-gray-600"><i class="fas fa-envelope text-gray-400"></i> ${client.email}</span>` : ''}
                                ${client.phone ? `<span class="flex items-center gap-2 text-gray-600"><i class="fas fa-phone text-gray-400"></i> ${client.phone}</span>` : ''}
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold ${client.category === 'VIP' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700'}">
                                ${client.category || 'Standard'}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button onclick="editClient(${client.id})" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors flex items-center justify-center">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button onclick="deleteClient(${client.id})" class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors flex items-center justify-center">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    function openClientModal() {
        document.getElementById('client-form').reset();
        document.getElementById('client-id').value = '';
        document.getElementById('modal-title').innerText = 'Nouveau Client';
        const modal = document.getElementById('client-modal');
        const content = document.getElementById('client-modal-content');
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeClientModal() {
        const modal = document.getElementById('client-modal');
        const content = document.getElementById('client-modal-content');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    function editClient(id) {
        fetch(`/api/gel/crm/clients/${id}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('client-id').value = data.id;
                document.getElementById('first_name').value = data.first_name;
                document.getElementById('last_name').value = data.last_name;
                document.getElementById('company').value = data.company || '';
                document.getElementById('email').value = data.email || '';
                document.getElementById('phone').value = data.phone || '';
                document.getElementById('category').value = data.category || 'Standard';
                document.getElementById('notes').value = data.notes || '';
                
                document.getElementById('modal-title').innerText = 'Modifier le Client';
                const modal = document.getElementById('client-modal');
                const content = document.getElementById('client-modal-content');
                modal.classList.remove('hidden');
                setTimeout(() => {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }, 10);
            });
    }

    function saveClient() {
        const id = document.getElementById('client-id').value;
        const data = {
            first_name: document.getElementById('first_name').value,
            last_name: document.getElementById('last_name').value,
            company: document.getElementById('company').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            category: document.getElementById('category').value,
            notes: document.getElementById('notes').value,
        };

        const method = id ? 'PUT' : 'POST';
        const url = id ? `/api/gel/crm/clients/${id}` : `/api/gel/crm/clients`;

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            closeClientModal();
            loadClients();
        });
    }

    function deleteClient(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce client ?")) {
            fetch(`/api/gel/crm/clients/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(() => {
                loadClients();
            });
        }
    }
</script>
@endsection
