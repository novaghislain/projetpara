<template>
    <div class="min-h-screen bg-[#f8f9fc] relative overflow-hidden">
        <!-- Background Decorations (Glassmorphism Support) -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-400/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[30%] h-[30%] bg-indigo-400/20 rounded-full blur-3xl pointer-events-none"></div>

        <nav class="glass sticky top-0 z-50 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-lg">
                    G
                </div>
                <span class="font-bold text-xl tracking-tight text-slate-800">GEL Cabinet</span>
            </div>
            <div class="flex gap-4">
                <a href="/login" class="px-5 py-2 rounded-full font-medium text-slate-600 hover:text-blue-600 transition-colors">Connexion</a>
                <a href="/register" class="px-5 py-2 rounded-full font-medium bg-blue-600 text-white hover:bg-blue-700 transition-colors shadow-md shadow-blue-500/20">Essai Gratuit</a>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-6 py-12">
            <!-- Hero Section -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h1 class="text-5xl md:text-6xl font-extrabold text-slate-900 tracking-tight mb-6">
                    L'ERP SaaS conçu pour propulser <span class="text-gradient">votre croissance</span>.
                </h1>
                <p class="text-lg text-slate-600 mb-8">
                    GEL Cabinet unifie votre comptabilité, vos ressources humaines, vos projets et votre trésorerie dans une plateforme fluide, sécurisée et boostée à l'Intelligence Artificielle.
                </p>
                <div class="flex items-center justify-center gap-4">
                    <button class="px-8 py-3 rounded-full font-semibold bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30 transform hover:-translate-y-1">
                        Démarrer l'expérience
                    </button>
                    <button class="px-8 py-3 rounded-full font-semibold glass text-slate-700 hover:bg-white/90 transition-all transform hover:-translate-y-1">
                        Voir la démo
                    </button>
                </div>
            </div>

            <!-- Bento Grid Section -->
            <div class="bento-grid">
                
                <!-- Financial Core -->
                <div class="bento-item bento-item-large flex flex-col justify-between group">
                    <div>
                        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-800 mb-2">Comptabilité & Finance</h3>
                        <p class="text-slate-600">Normes OHADA, journaux comptables automatiques, trésorerie prévisionnelle et états financiers en temps réel.</p>
                    </div>
                    <div class="mt-8 h-32 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-100/50 relative overflow-hidden">
                        <!-- Mockup Graph -->
                        <div class="absolute bottom-0 left-0 w-full h-24 bg-blue-500/10" style="clip-path: polygon(0 100%, 0 50%, 20% 40%, 40% 60%, 60% 20%, 80% 50%, 100% 10%, 100% 100%);"></div>
                    </div>
                </div>

                <!-- AI Automation -->
                <div class="bento-item bg-gradient-to-br from-indigo-500 to-purple-600 text-white border-none relative overflow-hidden group">
                    <div class="absolute top-[-20%] right-[-20%] w-32 h-32 bg-white/20 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mb-4 backdrop-blur-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/></svg>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Extraction OCR (IA)</h3>
                        <p class="text-white/80 text-sm">Scannez vos factures, l'IA extrait les données et génère les écritures comptables instantanément.</p>
                    </div>
                </div>

                <!-- HR & Payroll -->
                <div class="bento-item">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Portail Collaborateur</h3>
                    <p class="text-slate-600 text-sm">Gestion des congés, fiches de paie, contrats dynamiques et LMS intégré pour vos équipes.</p>
                </div>

                <!-- SaaS Architecture -->
                <div class="bento-item bento-item-wide flex flex-col md:flex-row items-center gap-6 bg-slate-900 text-white border-slate-800 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYwYyIvPjwvc3ZnPg==')] opacity-20"></div>
                    <div class="flex-1 relative z-10">
                        <h3 class="text-2xl font-bold mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                            Architecture Multi-Tenants SaaS
                        </h3>
                        <p class="text-slate-400">Déployez des instances ERP sécurisées par secteur d'activité (BTP, Santé, Commerce) avec des modules à la carte. Chaque client possède son espace de données étanche avec portabilité complète (RGPD).</p>
                    </div>
                    <div class="w-full md:w-1/3 flex justify-center relative z-10">
                        <div class="p-4 rounded-2xl bg-slate-800/50 border border-slate-700 backdrop-blur-md shadow-2xl transform group-hover:rotate-3 transition-transform">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-500 flex items-center justify-center">🏢</div>
                                <div>
                                    <div class="text-sm font-bold">Tenant A</div>
                                    <div class="text-xs text-slate-400">Logistique</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center">🏥</div>
                                <div>
                                    <div class="text-sm font-bold">Tenant B</div>
                                    <div class="text-xs text-slate-400">Clinique</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
</script>
