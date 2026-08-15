<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/><path d="M6 8h2l2 4 2-6 2 6 2-4 2 2"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800 leading-tight">Comptabilité</h2>
                    <p class="text-sm text-slate-500">Pilotage financier & SYSCOA</p>
                </div>
            </div>
        </template>

        <div class="bento-grid relative z-10 p-6 max-w-7xl mx-auto">
            
            <!-- Trésorerie (Large) -->
            <div class="bento-item bento-item-large flex flex-col justify-between bg-white/70 backdrop-blur-md shadow-lg border border-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Trésorerie Globale</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">+8.4% vs mois dernier</span>
                </div>
                <div>
                    <p class="text-4xl font-extrabold text-slate-900 tracking-tight mb-1">42 850 000 <span class="text-2xl text-slate-500 font-bold">FCFA</span></p>
                    <p class="text-sm text-slate-500">Solde consolidé au 15 Août</p>
                </div>
                <div class="mt-6 h-32 bg-gradient-to-r from-emerald-50 to-blue-50 rounded-xl relative overflow-hidden border border-emerald-100/50 flex items-end">
                    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNlNWU3ZWIiLz48L3N2Zz4=')] opacity-50"></div>
                    <!-- Fake Graph Area -->
                    <div class="w-full h-24 bg-emerald-500/20 relative z-10" style="clip-path: polygon(0 100%, 0 60%, 15% 70%, 30% 40%, 45% 55%, 60% 30%, 75% 45%, 100% 20%, 100% 100%);">
                        <div class="w-full h-1 bg-emerald-400 absolute top-0" style="clip-path: polygon(0 60%, 15% 70%, 30% 40%, 45% 55%, 60% 30%, 75% 45%, 100% 20%, 100% 100%);"></div>
                    </div>
                </div>
            </div>

            <!-- Raccourcis OCR (Normal) -->
            <div class="bento-item flex flex-col justify-between bg-gradient-to-br from-blue-500 to-indigo-600 text-white border-none shadow-lg shadow-indigo-500/30">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h14a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"/><polyline points="14 2 14 8 20 8"/><path d="M2 15h10"/><path d="m9 18 3-3-3-3"/></svg>
                    </div>
                    <h3 class="text-sm font-bold">Extraction OCR IA</h3>
                </div>
                <div>
                    <p class="text-lg font-medium mb-1 leading-snug text-indigo-100">Scannez une facture pour générer l'écriture auto.</p>
                </div>
                <button class="mt-4 w-full py-2 rounded-lg bg-white text-indigo-600 hover:bg-indigo-50 transition-colors text-sm font-bold shadow-sm">
                    Scanner un document
                </button>
            </div>

            <!-- Documents Récents (Tall) -->
            <div class="bento-item bento-item-tall flex flex-col justify-between bg-white/70 backdrop-blur-md border border-white">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">Écritures en attente</h3>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="p-3 rounded-xl bg-slate-50/50 border border-slate-100 flex justify-between items-center transition hover:bg-slate-50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-500">HA</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Achat Fournitures</p>
                                    <p class="text-xs text-slate-500">FA-2026-081</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-slate-700">125 000</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50/50 border border-slate-100 flex justify-between items-center transition hover:bg-slate-50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-500">VT</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Facture Client SA</p>
                                    <p class="text-xs text-slate-500">FC-2026-112</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-slate-700">2 450 000</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50/50 border border-slate-100 flex justify-between items-center transition hover:bg-slate-50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-500">BQ</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Virement Loyer</p>
                                    <p class="text-xs text-slate-500">OP-08-12</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-slate-700">850 000</span>
                        </div>
                    </div>
                </div>
                <a href="#" class="mt-4 text-center text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors">Valider le brouillard &rarr;</a>
            </div>

            <!-- Bilan Rapide (Normal) -->
            <div class="bento-item flex flex-col justify-between bg-white/70 backdrop-blur-md border border-white">
                <h3 class="text-sm font-bold text-slate-800 mb-2">Bilan (Actif vs Passif)</h3>
                <div class="flex-1 flex flex-col justify-center gap-3">
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1"><span class="text-slate-600">Actif</span> <span class="text-slate-800">120 M</span></div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: 75%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1"><span class="text-slate-600">Passif</span> <span class="text-slate-800">120 M</span></div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-orange-500 h-2 rounded-full" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
                <p class="text-center text-xs text-emerald-600 font-semibold mt-4">Bilan équilibré <i class="bi-check-circle-fill"></i></p>
            </div>

            <!-- Plan Comptable & Journaux (Wide) -->
            <div class="bento-item bento-item-wide bg-slate-900 text-white border-slate-800 flex items-center justify-between shadow-xl">
                <div>
                    <h3 class="text-lg font-bold mb-1">Configuration SYSCOA</h3>
                    <p class="text-slate-400 text-sm mb-4">Gérez votre plan comptable, vos journaux et l'ouverture d'exercice.</p>
                    <div class="flex gap-3">
                        <button class="px-4 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 font-medium text-sm transition-colors shadow-md shadow-orange-500/20">
                            Plan Comptable
                        </button>
                        <button class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 font-medium text-sm transition-colors">
                            Journaux
                        </button>
                    </div>
                </div>
                <div class="hidden md:flex gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center backdrop-blur-sm">
                        <svg class="w-8 h-8 text-white/40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<style scoped>
/* Bento Grid Layout specific to the dashboard */
.bento-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-auto-rows: 180px;
    gap: 1.5rem;
}

.bento-item {
    border-radius: 1.5rem;
    padding: 1.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.bento-item:hover {
    transform: translateY(-4px);
}

/* Modifier Classes */
.bento-item-large {
    grid-column: span 2;
    grid-row: span 2;
}

.bento-item-wide {
    grid-column: span 3;
    grid-row: span 1;
}

.bento-item-tall {
    grid-column: span 1;
    grid-row: span 2;
}

@media (max-width: 1024px) {
    .bento-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .bento-item-wide {
        grid-column: span 2;
    }
}

@media (max-width: 768px) {
    .bento-grid {
        grid-template-columns: 1fr;
    }
    .bento-item-large, .bento-item-wide, .bento-item-tall {
        grid-column: span 1;
    }
}
</style>
