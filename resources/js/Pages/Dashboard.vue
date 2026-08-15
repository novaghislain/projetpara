<template>
    <AuthenticatedLayout>
        <template #header>
            Vue d'ensemble
        </template>

        <!-- Bento Grid Dashboard -->
        <div class="bento-grid relative z-10">
            
            <!-- CA & Trésorerie (Large) -->
            <div class="bento-item bento-item-large flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Chiffre d'Affaires</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">+14% ce mois</span>
                </div>
                <div>
                    <p class="text-4xl font-extrabold text-slate-900 tracking-tight mb-1">24 500 000 FCFA</p>
                    <p class="text-sm text-slate-500">Objectif annuel : 65% atteint</p>
                </div>
                <div class="mt-6 h-32 bg-gradient-to-r from-emerald-50 to-blue-50 rounded-xl relative overflow-hidden border border-emerald-100/50">
                    <!-- Fake Graph -->
                    <div class="absolute bottom-0 left-0 w-full h-24 bg-emerald-500/20" style="clip-path: polygon(0 100%, 0 70%, 10% 65%, 20% 75%, 40% 40%, 60% 50%, 80% 20%, 100% 30%, 100% 100%);"></div>
                </div>
            </div>

            <!-- RH Congés (Normal) -->
            <div class="bento-item flex flex-col justify-between bg-gradient-to-br from-indigo-500 to-purple-600 text-white border-none">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3 class="text-sm font-bold">Ressources Humaines</h3>
                </div>
                <div>
                    <p class="text-3xl font-extrabold mb-1">4</p>
                    <p class="text-sm text-indigo-100">Demandes de congés en attente</p>
                </div>
                <button class="mt-4 w-full py-2 rounded-lg bg-white/20 hover:bg-white/30 transition-colors text-sm font-semibold backdrop-blur-md">
                    Gérer les demandes
                </button>
            </div>

            <!-- Alertes & Stocks (Tall) -->
            <div class="bento-item bento-item-tall flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">Ruptures de stock</h3>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-bold text-slate-800">Paracétamol 500mg</p>
                                <p class="text-xs text-red-500 font-medium">Stock: 12 (Min: 50)</p>
                            </div>
                            <button class="text-blue-600 hover:text-blue-800"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg></button>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-bold text-slate-800">Seringues 5ml</p>
                                <p class="text-xs text-red-500 font-medium">Stock: 5 (Min: 200)</p>
                            </div>
                            <button class="text-blue-600 hover:text-blue-800"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg></button>
                        </div>
                    </div>
                </div>
                <a href="#" class="mt-4 text-center text-sm font-semibold text-blue-600 hover:text-blue-800">Voir tout l'inventaire &rarr;</a>
            </div>

            <!-- Projets (Normal) -->
            <div class="bento-item flex flex-col justify-between">
                <h3 class="text-sm font-bold text-slate-800 mb-2">Projets en cours</h3>
                <div class="flex-1 flex items-center justify-center">
                    <div class="relative w-24 h-24 flex items-center justify-center">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-slate-100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="100, 100" />
                            <path class="text-blue-500" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="65, 100" />
                        </svg>
                        <span class="absolute text-xl font-bold text-slate-800">12</span>
                    </div>
                </div>
                <p class="text-center text-xs text-slate-500 mt-2">65% des tâches complétées</p>
            </div>

            <!-- Raccourcis Rapides (Wide) -->
            <div class="bento-item bento-item-wide bg-slate-900 text-white border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold mb-1">Actions Rapides</h3>
                    <p class="text-slate-400 text-sm mb-4">Gagnez du temps sur vos opérations courantes.</p>
                    <div class="flex gap-3">
                        <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 font-medium text-sm transition-colors shadow-md shadow-blue-500/20">
                            + Nouvelle Facture
                        </button>
                        <button class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 font-medium text-sm transition-colors">
                            Scanner un Reçu (OCR)
                        </button>
                    </div>
                </div>
                <div class="hidden md:block w-32 h-32 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYwYyIvPjwvc3ZnPg==')] opacity-20"></div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>
