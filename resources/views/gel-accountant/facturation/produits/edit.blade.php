@extends('layouts.gel-accountant')

@section('title', 'Modifier un Produit/Service')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold">Modifier le Produit/Service ✨</h1>
        <p class="text-sm text-slate-500">Mettez à jour l'élément "{{ $produit->nom }}" de votre catalogue.</p>
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

    <div class="bg-white dark:bg-slate-800 shadow-lg rounded-sm border border-slate-200 dark:border-slate-700">
        <form action="{{ route('gel-accountant.facturation.produits.update', $produit->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nom -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Nom <span class="text-rose-500">*</span></label>
                    <input type="text" name="nom" class="form-input w-full" value="{{ old('nom', $produit->nom) }}" required />
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Description</label>
                    <textarea name="description" rows="3" class="form-textarea w-full">{{ old('description', $produit->description) }}</textarea>
                </div>

                <!-- Prix et Type -->
                <div>
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Prix unitaire (HT) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="prix_unitaire" class="form-input w-full" value="{{ old('prix_unitaire', $produit->prix_unitaire) }}" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Type d'élément</label>
                    <select name="est_service" class="form-select w-full">
                        <option value="1" {{ old('est_service', $produit->est_service) == 1 ? 'selected' : '' }}>Service (Prestation)</option>
                        <option value="0" {{ old('est_service', $produit->est_service) == 0 ? 'selected' : '' }}>Bien (Produit physique)</option>
                    </select>
                </div>

                <!-- Compte Comptable -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Compte de vente / Produit (70...) <span class="text-rose-500">*</span></label>
                    <select name="compte_comptable_id" class="form-select w-full" required>
                        <option value="">Sélectionner un compte comptable...</option>
                        @foreach($comptes as $compte)
                            <option value="{{ $compte->id }}" {{ old('compte_comptable_id', $produit->compte_comptable_id) == $compte->id ? 'selected' : '' }}>
                                {{ $compte->numero }} - {{ $compte->intitule }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('gel-accountant.facturation.produits.index') }}" class="btn bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-600 dark:text-slate-300">
                    Annuler
                </a>
                <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                    Mettre à jour le produit
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
