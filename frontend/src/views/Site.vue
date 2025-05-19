<script setup lang="ts">
import Navbar from '@/components/site/Navbar.vue';
</script>

<template>
  <div class="min-h-screen">
    <Navbar />
    <main class="pt-24 px-4">
      <router-view />
    </main>
  </div>
</template>