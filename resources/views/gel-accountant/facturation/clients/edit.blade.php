@extends('layouts.gel-accountant')

@section('title', 'Modifier un Client CRM')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold">Modifier le Client CRM ✨</h1>
        <p class="text-sm text-slate-500">Mettez à jour les informations du client {{ $contact->company ?? ($contact->first_name . ' ' . $contact->last_name) }}.</p>
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
        <form action="{{ route('gel-accountant.facturation.clients.update', $contact->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Entreprise -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Nom de l'entreprise</label>
                    <input type="text" name="company" class="form-input w-full" value="{{ old('company', $contact->company) }}" placeholder="Ex: Entreprise Alpha" />
                </div>

                <!-- Nom / Prénom -->
                <div>
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Nom du contact <span class="text-rose-500">*</span></label>
                    <input type="text" name="last_name" class="form-input w-full" value="{{ old('last_name', $contact->last_name) }}" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Prénom du contact</label>
                    <input type="text" name="first_name" class="form-input w-full" value="{{ old('first_name', $contact->first_name) }}" />
                </div>

                <!-- Contact -->
                <div>
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Email</label>
                    <input type="email" name="email" class="form-input w-full" value="{{ old('email', $contact->email) }}" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Téléphone</label>
                    <input type="text" name="phone" class="form-input w-full" value="{{ old('phone', $contact->phone) }}" />
                </div>

                <!-- Légal -->
                <div>
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Numéro IFU</label>
                    <input type="text" name="ifu" class="form-input w-full" value="{{ old('ifu', $contact->ifu) }}" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Numéro RCCM</label>
                    <input type="text" name="rccm" class="form-input w-full" value="{{ old('rccm', $contact->rccm) }}" />
                </div>

                <!-- Adresse -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Adresse</label>
                    <textarea name="address" rows="3" class="form-textarea w-full">{{ old('address', $contact->address) }}</textarea>
                </div>

                <!-- Compte Comptable -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1">Compte Comptable Client (411...)</label>
                    <select name="compte_comptable_id" class="form-select w-full">
                        <option value="">Sélectionner un compte comptable...</option>
                        @foreach($comptes as $compte)
                            <option value="{{ $compte->id }}" {{ old('compte_comptable_id', $contact->compte_comptable_id) == $compte->id ? 'selected' : '' }}>
                                {{ $compte->numero }} - {{ $compte->intitule }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('gel-accountant.facturation.clients.index') }}" class="btn bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-600 dark:text-slate-300">
                    Annuler
                </a>
                <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                    Mettre à jour le client
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
