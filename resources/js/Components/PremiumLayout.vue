<script setup>
import Header from './Header.vue';
import Footer from './Footer.vue';
import LiaAssistant from './LiaAssistant.vue';
import AIVisualBackground from './AIVisualBackground.vue';

defineProps({
    title: String
});
</script>

<template>
    <div class="premium-layout min-vh-100 d-flex flex-column bg-white overflow-hidden">
        <!-- AI Background (Always present for premium feel) -->
        <AIVisualBackground />

        <!-- Header -->
        <Header />

        <!-- Main Content -->
        <main class="flex-grow-1 position-relative z-1">
            <slot />
        </main>

        <!-- Footer -->
        <Footer />

        <!-- AI Assistant -->
        <LiaAssistant />
    </div>
</template>

<style>
/* Local overrides if needed, but core design now in app.css */
</style>
