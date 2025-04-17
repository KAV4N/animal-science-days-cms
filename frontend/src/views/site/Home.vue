<script setup lang="ts">
</script>

<template>
  <main class="pt-24 px-4">
    <section class="max-w-4xl mx-auto">
      <h1 class="text-4xl font-bold mb-8">Welcome to School Conference CMS</h1>

      <div class="space-y-8">
        <div v-for="i in 20" :key="i" class="p-8 bg-surface-500 rounded-lg">
          <h2 class="text-2xl font-semibold mb-4">Sample Section {{ i }}</h2>
          <p class="text-lg">
            This is sample content to test the navbar scroll animation.
            Scroll down to see the navbar change its appearance.
          </p>
        </div>
      </div>
    </section>
  </main>
</template>
