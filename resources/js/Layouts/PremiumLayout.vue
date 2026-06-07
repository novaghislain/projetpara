<script setup>
import Header from '../Components/Header.vue';
import Footer from '../Components/Footer.vue';
import LiaAssistant from '../Components/LiaAssistant.vue';
import AIVisualBackground from '../Components/AIVisualBackground.vue';
</script>

<template>
  <div class="min-vh-100 d-flex flex-column bg-white overflow-hidden position-relative">
    <AIVisualBackground />
    <Header />
    
    <main class="flex-grow-1 position-relative z-10">
      <slot />
    </main>

    <Footer />
    <LiaAssistant />
  </div>
</template>

<style scoped>
.z-10 { z-index: 10; }
</style>
