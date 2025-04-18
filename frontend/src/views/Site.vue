<script setup lang="ts">
import Navbar from '@/components/site/Navbar.vue';
import Home from '@/views/site/Home.vue';
</script>

<template>
  <div class="min-h-screen">
    <Navbar />
    <main class="pt-24 px-4">
      <Home />
    </main>
  </div>
</template>
