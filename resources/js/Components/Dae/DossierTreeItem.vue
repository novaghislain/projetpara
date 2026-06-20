<template>
    <div class="dossier-tree-item">
        <!-- Dossier row -->
        <div class="dossier-row d-flex align-items-center gap-1 px-2 py-1"
             :class="{ 'dossier-actif': actif === dossier.id }"
             :style="{ paddingLeft: (8 + niveau * 16) + 'px' }"
             @click="$emit('select', dossier.id)">

            <!-- Expand toggle -->
            <button v-if="dossier.enfants?.length" class="btn btn-sm p-0 border-0 text-muted dossier-toggle"
                    @click.stop="toggleExpand" :style="{ width: '18px' }">
                <i class="bi" :class="expand ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
            </button>
            <span v-else style="width:18px;"></span>

            <!-- Color dot -->
            <span v-if="dossier.couleur" class="dossier-couleur rounded-circle d-inline-block"
                  :style="{ backgroundColor: dossier.couleur, width: '10px', height: '10px', minWidth: '10px' }"></span>
            <i v-else class="bi bi-folder text-muted" style="font-size:0.9rem;"></i>

            <!-- Name -->
            <span class="small text-truncate flex-grow-1">{{ dossier.nom }}</span>

            <!-- Count -->
            <span v-if="dossier.document_count > 0" class="badge bg-light text-muted" style="font-size:0.6rem;">
                {{ dossier.document_count }}
            </span>

            <!-- Actions -->
            <div class="dossier-actions d-none" @click.stop>
                <button class="btn btn-sm p-0 border-0 text-muted me-1" title="Modifier"
                        @click="$emit('edit', dossier)" style="font-size:0.7rem;">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm p-0 border-0 text-danger" title="Supprimer"
                        @click="$emit('delete', dossier)" style="font-size:0.7rem;">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>

        <!-- Children -->
        <div v-if="expand && dossier.enfants?.length" class="dossier-enfants">
            <DossierTreeItem
                v-for="enfant in dossier.enfants" :key="enfant.id"
                :dossier="enfant"
                :actif="actif"
                :niveau="niveau + 1"
                @select="$emit('select', $event)"
                @edit="$emit('edit', $event)"
                @delete="$emit('delete', $event)"
            />
        </div>
    </div>
</template>

<script>
export default {
    name: 'DossierTreeItem',

    props: {
        dossier: { type: Object, required: true },
        actif: { type: Number, default: null },
        niveau: { type: Number, default: 0 },
    },

    emits: ['select', 'edit', 'delete'],

    data() {
        return {
            expand: true,
        };
    },

    methods: {
        toggleExpand() {
            this.expand = !this.expand;
        },
    },
};
</script>

<style scoped>
.dossier-tree-item .dossier-row {
    cursor: pointer;
    transition: background 0.12s;
    border-radius: 4px;
    position: relative;
}
.dossier-tree-item .dossier-row:hover {
    background: rgba(255, 121, 0, 0.06);
}
.dossier-tree-item .dossier-row.dossier-actif {
    background: rgba(255, 121, 0, 0.1);
    font-weight: 600;
    border-left: 2px solid #FF7900;
}
.dossier-tree-item .dossier-row:hover .dossier-actions {
    display: inline-flex !important;
}
.dossier-toggle {
    background: none;
    line-height: 1;
}
.dossier-couleur {
    flex-shrink: 0;
}
</style>
