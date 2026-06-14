<script setup>
defineProps({
    title: { type: String, default: 'Imprimer' },
    filename: { type: String, default: 'document' },
    variant: { type: String, default: 'outline-primary' },
    size: { type: String, default: 'sm' },
});

function print() {
    window.print();
}
</script>

<template>
    <button class="btn d-inline-flex align-items-center gap-1" :class="'btn-' + variant + ' btn-' + size" @click="print">
        <i class="bi-printer"></i>
        <span>{{ title }}</span>
    </button>
</template>
