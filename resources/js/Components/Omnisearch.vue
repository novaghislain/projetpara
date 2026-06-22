<script setup>
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue';

const emit = defineEmits(['navigate']);
const props = defineProps({
    placeholder: { type: String, default: 'Rechercher... (clients, factures, pages...)' },
});

const isOpen = ref(false);
const query = ref('');
const results = ref([]);
const loading = ref(false);
const selectedIndex = ref(-1);
const searchInput = ref(null);
const recentSearches = ref(loadRecent());

const categories = [
    { key: 'clients', icon: 'bi-building', label: 'Clients' },
    { key: 'invoices', icon: 'bi-receipt', label: 'Factures' },
    { key: 'transactions', icon: 'bi-journal-text', label: 'Écritures comptables' },
    { key: 'contacts', icon: 'bi-people', label: 'Contacts' },
    { key: 'employees', icon: 'bi-person-badge', label: 'Employés' },
    { key: 'documents', icon: 'bi-file-text', label: 'Documents' },
    { key: 'navigation', icon: 'bi-link', label: 'Pages' },
];

const categoryIcons = Object.fromEntries(categories.map(c => [c.key, c.icon]));
const categoryLabels = Object.fromEntries(categories.map(c => [c.key, c.label]));

function loadRecent() {
    try {
        return JSON.parse(localStorage.getItem('omnisearch_recent') || '[]');
    } catch { return []; }
}

function saveRecent(q) {
    let recents = loadRecent().filter(r => r !== q);
    recents.unshift(q);
    if (recents.length > 5) recents = recents.slice(0, 5);
    localStorage.setItem('omnisearch_recent', JSON.stringify(recents));
    recentSearches.value = recents;
}

let debounceTimer = null;
watch(query, (val) => {
    clearTimeout(debounceTimer);
    selectedIndex.value = -1;
    if (!val || val.length < 2) {
        results.value = [];
        loading.value = false;
        return;
    }
    loading.value = true;
    debounceTimer = setTimeout(() => performSearch(val), 300);
});

async function performSearch(q) {
    try {
        const resp = await window.axios.get(`/api/search?q=${encodeURIComponent(q)}`);
        results.value = resp.data.results || [];
    } catch (e) {
        results.value = [];
    }
    loading.value = false;
}

function open() {
    isOpen.value = true;
    query.value = '';
    results.value = [];
    selectedIndex.value = -1;
    nextTick(() => searchInput.value?.focus());
}

function close() {
    isOpen.value = false;
    query.value = '';
    results.value = [];
    selectedIndex.value = -1;
}

function navigate(item) {
    if (!item) return;
    if (query.value.trim().length >= 2) saveRecent(query.value.trim());
    close();
    if (item.route) {
        emit('navigate', item.route);
        window.location.href = item.route.startsWith('/') ? item.route : '/' + item.route;
    }
}

function onKeydown(e) {
    if (!isOpen.value) return;

    const flatItems = results.value.flatMap(g => g.items || []);
    if (!flatItems.length) {
        if (e.key === 'Enter' && query.value.trim().length >= 2) {
            close();
        }
        return;
    }

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = Math.min(selectedIndex.value + 1, flatItems.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = Math.max(selectedIndex.value - 1, -1);
    } else if (e.key === 'Enter' && selectedIndex.value >= 0) {
        e.preventDefault();
        navigate(flatItems[selectedIndex.value]);
    } else if (e.key === 'Escape') {
        close();
    }
}

function selectRecent(q) {
    query.value = q;
}

function clearRecent() {
    localStorage.removeItem('omnisearch_recent');
    recentSearches.value = [];
}

function handleGlobalKeydown(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        if (isOpen.value) close();
        else open();
    }
}

onMounted(() => document.addEventListener('keydown', handleGlobalKeydown));
onUnmounted(() => document.removeEventListener('keydown', handleGlobalKeydown));

// Expose open/close for parent access
defineExpose({ open, close });
</script>

<template>
    <!-- Search trigger button (can be placed anywhere) -->
    <button class="dp-omnisearch-trigger" @click="open" title="Recherche globale (Ctrl+K)">
        <i class="bi-search"></i>
        <span class="dp-omnisearch-trigger-text">Rechercher...</span>
        <kbd class="dp-kbd">Ctrl+K</kbd>
    </button>

    <!-- Modal overlay -->
    <Teleport to="body">
        <Transition name="omni">
            <div v-if="isOpen" class="omni-overlay" @click.self="close" @keydown="onKeydown" tabindex="-1">
                <div class="omni-modal">
                    <!-- Search input -->
                    <div class="omni-input-wrap">
                        <i class="bi-search omni-search-icon"></i>
                        <input ref="searchInput"
                               v-model="query"
                               class="omni-input"
                               :placeholder="placeholder"
                               @keydown="onKeydown"
                               autocomplete="off"
                               spellcheck="false" />
                        <button v-if="query" class="omni-clear" @click="query = ''; results = []">
                            <i class="bi-x-lg"></i>
                        </button>
                        <kbd class="omni-esc-kbd">ESC</kbd>
                    </div>

                    <!-- Loading -->
                    <div v-if="loading" class="omni-loading">
                        <div class="omni-spinner"></div>
                        <span>Recherche en cours...</span>
                    </div>

                    <!-- Recent searches -->
                    <div v-else-if="!query && recentSearches.length" class="omni-section">
                        <div class="omni-section-header">
                            <span><i class="bi-clock-history me-1"></i> Recherches récentes</span>
                            <button class="omni-clear-btn" @click="clearRecent">Effacer</button>
                        </div>
                        <div class="omni-recent-list">
                            <button v-for="(r, i) in recentSearches" :key="i"
                                    class="omni-recent-item" @click="selectRecent(r)">
                                <i class="bi-clock"></i>
                                <span>{{ r }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Results -->
                    <div v-else-if="results.length" class="omni-results">
                        <div v-for="(group, gi) in results" :key="group.category" class="omni-group">
                            <div class="omni-group-header">
                                <i :class="categoryIcons[group.category] || 'bi-file'"></i>
                                <span>{{ categoryLabels[group.category] || group.category }}</span>
                                <span class="omni-group-count">{{ group.items.length }}</span>
                            </div>
                            <div v-for="(item, ii) in group.items" :key="`${gi}-${ii}`"
                                 class="omni-item"
                                 :class="{ 'omni-item--selected': selectedIndex === flatIndex(gi, ii, results) }"
                                 @click="navigate(item)"
                                 @mouseenter="selectedIndex = flatIndex(gi, ii, results)">
                                <div class="omni-item-icon">
                                    <i :class="item.icon || categoryIcons[group.category] || 'bi-file'"></i>
                                </div>
                                <div class="omni-item-content">
                                    <div class="omni-item-title" v-html="item.title"></div>
                                    <div v-if="item.subtitle" class="omni-item-sub" v-html="item.subtitle"></div>
                                </div>
                                <div v-if="item.badge" class="omni-item-badge">{{ item.badge }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- No results -->
                    <div v-else-if="query.length >= 2 && !loading" class="omni-empty">
                        <i class="bi-search"></i>
                        <span>Aucun résultat pour "<strong>{{ query }}</strong>"</span>
                    </div>

                    <!-- Hints -->
                    <div v-else-if="!query" class="omni-hints">
                        <div class="omni-hint"><kbd>↑</kbd><kbd>↓</kbd> Navigation</div>
                        <div class="omni-hint"><kbd>↵</kbd> Ouvrir</div>
                        <div class="omni-hint"><kbd>Esc</kbd> Fermer</div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script>
// Helper: calculate flat index across groups
function flatIndex(gi, ii, groups) {
    let idx = 0;
    for (let g = 0; g < gi; g++) {
        idx += (groups[g].items || []).length;
    }
    return idx + ii;
}
</script>

<style scoped>
/* Trigger button - styled for topbar integration */
.dp-omnisearch-trigger {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 6px 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #64748b;
    font-size: 13px;
    min-width: 220px;
}
.dp-omnisearch-trigger:hover {
    background: #fff;
    border-color: #FF7900;
    color: #1e293b;
    box-shadow: 0 1px 4px rgba(255,121,0,0.1);
}
.dp-omnisearch-trigger-text {
    flex: 1;
    text-align: left;
    color: #94a3b8;
}
.dp-kbd {
    background: #e2e8f0;
    border-radius: 4px;
    padding: 1px 5px;
    font-size: 10px;
    font-weight: 600;
    color: #64748b;
    border: 1px solid #cbd5e1;
}

/* Modal */
.omni-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(15,23,42,0.6);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 10vh;
}
.omni-modal {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 24px 80px rgba(0,0,0,0.2);
    width: 640px;
    max-width: calc(100vw - 32px);
    max-height: 60vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: omniPop 0.2s ease;
}
@keyframes omniPop {
    from { opacity: 0; transform: scale(0.96) translateY(-10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

/* Input */
.omni-input-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border-bottom: 1px solid #e2e8f0;
}
.omni-search-icon { color: #94a3b8; font-size: 18px; flex-shrink: 0; }
.omni-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 15px;
    font-family: inherit;
    color: #1e293b;
    background: transparent;
}
.omni-input::placeholder { color: #94a3b8; }
.omni-clear {
    border: none;
    background: #f1f5f9;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 10px;
    cursor: pointer;
    flex-shrink: 0;
}
.omni-esc-kbd {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 1px 5px;
    font-size: 10px;
    color: #94a3b8;
    flex-shrink: 0;
}

/* Loading */
.omni-loading {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 24px;
    justify-content: center;
    color: #64748b;
    font-size: 13px;
}
.omni-spinner {
    width: 18px;
    height: 18px;
    border: 2px solid #e2e8f0;
    border-top-color: #FF7900;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Section / Results */
.omni-section {
    padding: 12px 18px;
}
.omni-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 8px;
}
.omni-clear-btn {
    border: none;
    background: none;
    color: #FF7900;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
}
.omni-clear-btn:hover { text-decoration: underline; }

.omni-recent-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.omni-recent-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    color: #475569;
    transition: background 0.15s;
}
.omni-recent-item:hover { background: #f1f5f9; }
.omni-recent-item i { color: #94a3b8; font-size: 14px; }

.omni-results {
    overflow-y: auto;
    padding: 4px 0;
    flex: 1;
}
.omni-group { padding: 0; }
.omni-group-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 18px 6px;
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.omni-group-header i { font-size: 12px; }
.omni-group-count {
    background: #f1f5f9;
    border-radius: 10px;
    padding: 0 6px;
    font-size: 10px;
    color: #94a3b8;
}
.omni-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 18px;
    cursor: pointer;
    transition: background 0.1s;
}
.omni-item:hover,
.omni-item--selected { background: #ffF5ed; }
.omni-item-icon {
    width: 32px;
    height: 32px;
    background: #f1f5f9;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FF7900;
    font-size: 14px;
    flex-shrink: 0;
}
.omni-item--selected .omni-item-icon { background: rgba(255,121,0,0.15); }
.omni-item-content { flex: 1; min-width: 0; }
.omni-item-title { font-size: 13px; font-weight: 500; color: #1e293b; }
.omni-item-title :deep(mark) { background: rgba(255,121,0,0.2); color: #1e293b; padding: 0 2px; border-radius: 2px; }
.omni-item-sub { font-size: 11px; color: #94a3b8; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.omni-item-badge {
    font-size: 10px;
    background: #f1f5f9;
    border-radius: 4px;
    padding: 2px 6px;
    color: #64748b;
    flex-shrink: 0;
}

/* Empty state */
.omni-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 36px 18px;
    color: #94a3b8;
    font-size: 13px;
}
.omni-empty i { font-size: 28px; opacity: 0.5; }

/* Hints */
.omni-hints {
    display: flex;
    gap: 16px;
    padding: 10px 18px;
    border-top: 1px solid #e2e8f0;
}
.omni-hint {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: #94a3b8;
}
.omni-hint kbd {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 3px;
    padding: 0 4px;
    font-size: 10px;
}

/* Transition */
.omni-enter-active { transition: opacity 0.2s ease; }
.omni-leave-active { transition: opacity 0.15s ease; }
.omni-enter-from,
.omni-leave-to { opacity: 0; }
</style>
