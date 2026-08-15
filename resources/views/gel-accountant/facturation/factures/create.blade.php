@extends('layouts.gel-accountant')

@section('title', 'Créer une Facture')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" x-data="invoiceForm()">
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold">Nouvelle Facture ✨</h1>
        <p class="text-sm text-slate-500">Créez une facture pour un client. L'intégration comptable se fera à la validation.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-sm text-sm bg-rose-100 border border-rose-200 text-rose-600">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('gel-accountant.facturation.factures.store') }}" method="POST">
        @csrf
        
        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">Informations Générales</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Client -->
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Client CRM <span class="text-rose-500">*</span></label>
                    <select name="contact_id" class="form-select w-full" required>
                        <option value="">Sélectionner un client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('contact_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->company ?? ($client->last_name . ' ' . $client->first_name) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Numéro -->
                <div>
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Numéro de Facture <span class="text-rose-500">*</span></label>
                    <input type="text" name="numero" class="form-input w-full bg-slate-100" value="{{ old('numero', $numeroFacture) }}" required readonly />
                </div>

                <!-- Dates -->
                <div>
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Date de facturation <span class="text-rose-500">*</span></label>
                    <input type="date" name="date_facture" class="form-input w-full" value="{{ old('date_facture', date('Y-m-d')) }}" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Date d'échéance</label>
                    <input type="date" name="date_echeance" class="form-input w-full" value="{{ old('date_echeance') }}" />
                </div>
            </div>
        </div>

        <!-- Lignes de facturation -->
        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">Détail des Produits & Services</h2>
            
            <div class="overflow-x-auto">
                <table class="table-auto w-full dark:text-slate-300 mb-4">
                    <thead class="text-xs uppercase text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-700 dark:bg-opacity-50 rounded-sm">
                        <tr>
                            <th class="p-2 w-1/3"><div class="font-semibold text-left">Produit / Service</div></th>
                            <th class="p-2 w-1/3"><div class="font-semibold text-left">Description</div></th>
                            <th class="p-2 w-32"><div class="font-semibold text-left">Quantité</div></th>
                            <th class="p-2 w-32"><div class="font-semibold text-left">Prix Unitaire</div></th>
                            <th class="p-2"><div class="font-semibold text-center">Action</div></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(ligne, index) in lignes" :key="index">
                            <tr>
                                <td class="p-2">
                                    <select :name="`lignes[${index}][produit_id]`" x-model="ligne.produit_id" @change="updateLigne(index)" class="form-select w-full" required>
                                        <option value="">Sélectionner...</option>
                                        @foreach($produits as $produit)
                                            <option value="{{ $produit->id }}" data-prix="{{ $produit->prix_unitaire }}" data-nom="{{ $produit->nom }}">{{ $produit->nom }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-2">
                                    <input type="text" :name="`lignes[${index}][description]`" x-model="ligne.description" class="form-input w-full" required />
                                </td>
                                <td class="p-2">
                                    <input type="number" step="0.01" min="0.01" :name="`lignes[${index}][quantite]`" x-model="ligne.quantite" class="form-input w-full text-right" required />
                                </td>
                                <td class="p-2">
                                    <input type="number" step="0.01" min="0" :name="`lignes[${index}][prix_unitaire]`" x-model="ligne.prix_unitaire" class="form-input w-full text-right" required />
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="removeLigne(index)" class="text-rose-500 hover:text-rose-600" x-show="lignes.length > 1">
                                        <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 16 16">
                                            <path d="M5 7h6v2H5z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            
            <button type="button" @click="addLigne()" class="btn-sm bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-indigo-500">
                + Ajouter une ligne
            </button>
        </div>

        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">Notes (optionnel)</h2>
            <textarea name="notes" rows="3" class="form-textarea w-full" placeholder="Conditions de paiement, remarques..."></textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('gel-accountant.facturation.factures.index') }}" class="btn bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-600 dark:text-slate-300">
                Annuler
            </a>
            <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                Créer la facture
            </button>
        </div>
    </form>
</div>

<script>
    function invoiceForm() {
        return {
            lignes: [
                { produit_id: '', description: '', quantite: 1, prix_unitaire: 0 }
            ],
            addLigne() {
                this.lignes.push({ produit_id: '', description: '', quantite: 1, prix_unitaire: 0 });
            },
            removeLigne(index) {
                if (this.lignes.length > 1) {
                    this.lignes.splice(index, 1);
                }
            },
            updateLigne(index) {
                // Find selected option and get its data attributes
                const selectEl = document.querySelector(`select[name="lignes[${index}][produit_id]"]`);
                if (selectEl && selectEl.selectedOptions.length > 0) {
                    const option = selectEl.selectedOptions[0];
                    if (option.value) {
                        this.lignes[index].prix_unitaire = parseFloat(option.dataset.prix);
                        this.lignes[index].description = option.dataset.nom;
                    }
                }
            }
        }
    }
</script>
@endsection
