<template>
    <div class="min-h-screen bg-[#f8f9fc] flex font-sans text-slate-800">
        
        <!-- Sidebar (Glassmorphism) -->
        <aside class="w-64 hidden md:flex flex-col glass fixed h-full z-40 border-r border-slate-200/50">
            <div class="p-6 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold shadow-md">
                    G
                </div>
                <span class="font-bold text-lg tracking-tight">GEL Cabinet</span>
            </div>
            
            <nav class="flex-1 px-4 space-y-1 mt-4 overflow-y-auto">
                <a href="/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-blue-50 text-blue-700 font-medium transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    Tableau de bord
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Ressources Humaines
                </a>
                <a href="/comptabilite" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20"/><path d="M12 2v20"/><path d="m4.93 4.93 14.14 14.14"/><path d="m4.93 19.07 14.14-14.14"/></svg>
                    Finance & Compta
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Messagerie
                </a>
            </nav>
            
            <!-- User Profile mini -->
            <div class="p-4 border-t border-slate-200/50">
                <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-100 cursor-pointer transition-colors">
                    <div class="w-10 h-10 rounded-full bg-slate-300 overflow-hidden">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Felix" alt="Avatar" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-800 truncate">Ghislain C.</p>
                        <p class="text-xs text-slate-500 truncate">Super Admin</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 flex flex-col min-h-screen">
            
            <!-- Topbar (Glassmorphism) -->
            <header class="h-16 glass sticky top-0 z-30 px-6 flex items-center justify-between border-b border-slate-200/50">
                <div class="flex items-center gap-4">
                    <button class="md:hidden text-slate-500 hover:text-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                    </button>
                    <h2 class="text-lg font-bold text-slate-800 hidden sm:block">
                        <slot name="header">Tableau de bord</slot>
                    </h2>
                </div>
                <div class="flex items-center gap-4">
                    <button class="p-2 text-slate-400 hover:text-blue-600 transition-colors relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500 border-2 border-white"></span>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 p-6 relative overflow-hidden">
                <!-- Decorative Blur -->
                <div class="absolute top-0 right-0 w-[30%] h-[40%] bg-blue-300/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <slot />
            </div>

        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
</script>
