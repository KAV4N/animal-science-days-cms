<script setup lang="ts">
import Navbar from '@/components/site/Navbar.vue';
import SiteFooter from '@/components/site/Footer.vue';
</script>

<template>
  <div class="min-h-screen bg-surface-200 flex flex-col">
    <Navbar />
    <main class="pt-24 px-4 flex-grow">
      <router-view />
    </main>
    <SiteFooter />
  </div>
</template>
