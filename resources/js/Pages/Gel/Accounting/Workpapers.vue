<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import GelLayout from '../../../Layouts/GelLayout.vue';

const props = defineProps({
    client: Object,
    fiscalYear: Object,
    period: String,
    workpapers: Array,
    stats: Object,
});

// Group workpapers by account class (first digit of account number)
const groupedWorkpapers = computed(() => {
    const groups = {};
    
    props.workpapers.forEach(wp => {
        const account = wp.account;
        if (!account) return;
        
        const accountNumber = account.account_number || account.code;
        const firstDigit = accountNumber.toString().charAt(0);
        const className = `Classe ${firstDigit}`;
        
        if (!groups[className]) {
            groups[className] = {
                name: className,
                isOpen: true, // par défaut ouvert
                items: [],
            };
        }
        
        groups[className].items.push(wp);
    });
    
    // Sort keys logically
    return Object.keys(groups).sort().map(key => groups[key]);
});

const toggleGroup = (group) => {
    group.isOpen = !group.isOpen;
};

// Actions
const updateStatus = (workpaper, status) => {
    // Optimistic update
    const previousStatus = workpaper.status;
    workpaper.status = status;
    
    router.post(`/gel-accountant/workpapers/${workpaper.id}/status`, {
        status: status,
        notes: workpaper.notes,
    }, {
        preserveScroll: true,
        onError: () => {
            // Revert on error
            workpaper.status = previousStatus;
            alert("Erreur lors de la mise à jour du statut.");
        }
    });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(val || 0);
};
</script>

<template>
    <Head title="Workpapers - Dossiers de Révision" />

    <GelLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dossier de Révision: {{ client.name }} ({{ fiscalYear.name }})
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- En-tête : Barre de progression -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-medium text-gray-900">Progression de la révision</h3>
                            <span class="text-sm font-bold text-gray-700">{{ stats.percentage }}% complété</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                            <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: stats.percentage + '%' }"></div>
                        </div>
                        <div class="flex gap-4 text-sm text-gray-600">
                            <span>Total: {{ stats.total }}</span>
                            <span class="text-green-600">Révisés: {{ stats.reviewed }}</span>
                            <span class="text-yellow-600">En cours: {{ stats.pending }}</span>
                            <span class="text-red-600" v-if="stats.error">Erreurs: {{ stats.error }}</span>
                        </div>
                    </div>
                </div>

                <!-- DataGrid des Workpapers -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Compte</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Solde N-1</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Solde N</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Analyse IA</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template v-for="group in groupedWorkpapers" :key="group.name">
                                    <!-- En-tête de classe -->
                                    <tr class="bg-gray-100 cursor-pointer hover:bg-gray-200 transition-colors" @click="toggleGroup(group)">
                                        <td colspan="6" class="px-6 py-2 text-sm font-bold text-gray-800">
                                            <i class="bi" :class="group.isOpen ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                                            {{ group.name }}
                                        </td>
                                    </tr>
                                    
                                    <!-- Lignes des comptes (si ouvert) -->
                                    <template v-if="group.isOpen">
                                        <tr v-for="wp in group.items" :key="wp.id" :class="{'bg-red-50': wp.ai_variance_flag}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                                {{ wp.account.account_number || wp.account.code }} - {{ wp.account.name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                                {{ formatCurrency(wp.balance_n_minus_1) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                                {{ formatCurrency(wp.balance_n) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                <span v-if="wp.ai_variance_flag" class="inline-flex items-center gap-1 text-red-600 font-medium text-xs">
                                                    <i class="bi bi-robot"></i> {{ wp.ai_variance_flag }}
                                                </span>
                                                <span v-else class="text-green-500 text-xs">
                                                    <i class="bi bi-check-circle"></i> Cohérent
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span v-if="wp.status === 'reviewed'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Révisé</span>
                                                <span v-else-if="wp.status === 'error'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Erreur</span>
                                                <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">À réviser</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end gap-2">
                                                    <button v-if="wp.status !== 'reviewed'" @click="updateStatus(wp, 'reviewed')" class="text-green-600 hover:text-green-900" title="Marquer comme révisé">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                    <button v-if="wp.status !== 'pending'" @click="updateStatus(wp, 'pending')" class="text-gray-500 hover:text-gray-900" title="Remettre en attente">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                    <button class="text-blue-600 hover:text-blue-900" title="Ajouter une note ou O.D.">
                                                        <i class="bi bi-journal-text"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
    </GelLayout>
</template>

<style scoped>
/* Scoped styles can go here if needed, but Tailwind classes cover most needs */
</style>
