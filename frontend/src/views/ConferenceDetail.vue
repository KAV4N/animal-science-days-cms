<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePastConferencesStore } from '@/stores/pastConferencesStore';

import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Divider from 'primevue/divider';
import Skeleton from 'primevue/skeleton';

const route = useRoute();
const router = useRouter();
const pastConferencesStore = usePastConferencesStore();
const slug = computed(() => route.params.slug as string);

const loading = ref(true);

onMounted(async () => {
  if (pastConferencesStore.conferences.length === 0) {
    await pastConferencesStore.fetchConferences();
  }
  
  loading.value = false;
  
  // If not found, redirect
  if (!conference.value) {
    router.replace('/conferences');
  }
});

// Computed prop.
const conference = computed(() => {
  return pastConferencesStore.getConferenceBySlug(slug.value);
});

// Formatting
function formatDate(date: Date | null) {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

function formatDateRange(startDate: Date | null, endDate: Date | null) {
  if (!startDate) return '';
  
  const start = new Date(startDate);
  const end = endDate ? new Date(endDate) : null;
  
  const options: Intl.DateTimeFormatOptions = {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  };
  
  if (end) {
    if (start.getFullYear() === end.getFullYear() &&
        start.getMonth() === end.getMonth()) {
      // Same month and year: "January 10-15, 2023"
      return `${start.toLocaleDateString('en-US', { month: 'long', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { day: 'numeric', year: 'numeric' })}`;
    } else if (start.getFullYear() === end.getFullYear()) {
      // Same year: "January 10 - February 15, 2023"
      return `${start.toLocaleDateString('en-US', { month: 'long', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`;
    } else {
      // Different years: "January 10, 2022 - February 15, 2023"
      return `${start.toLocaleDateString('en-US', options)} - ${end.toLocaleDateString('en-US', options)}`;
    }
  }
  
  // Only start date
  return start.toLocaleDateString('en-US', options);
}

function calculateDuration(startDate: Date | null, endDate: Date | null): number {
  if (!startDate || !endDate) return 0;
  
  const start = new Date(startDate);
  start.setHours(0, 0, 0, 0);
  
  const end = new Date(endDate);
  end.setHours(0, 0, 0, 0);
  
  const diffTime = Math.abs(end.getTime() - start.getTime());
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
}

function goBack() {
  router.push('/conferences');
}
</script>

<template>
  <div class="conference-detail-page pt-24 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Back -->
      <Button 
        icon="pi pi-arrow-left" 
        label="Back to Conferences" 
        text 
        @click="goBack"
        class="mb-8"
      />
      
      <!-- Loading state -->
      <div v-if="loading" class="loading-state">
        <Skeleton height="300px" class="mb-4" />
        <Skeleton width="70%" height="42px" class="mb-3" />
        <Skeleton width="40%" height="24px" class="mb-6" />
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <Skeleton height="100px" />
          <Skeleton height="100px" />
          <Skeleton height="100px" />
        </div>
        
        <Skeleton width="100%" height="24px" class="mb-3" />
        <Skeleton width="100%" height="24px" class="mb-3" />
        <Skeleton width="85%" height="24px" class="mb-6" />
        
        <Skeleton width="100%" height="200px" />
      </div>
      
      <!-- Conference Not Found -->
      <div v-else-if="!conference" class="not-found text-center py-12">
        <i class="pi pi-exclamation-circle text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
        <h2 class="text-2xl font-medium mb-2">Conference Not Found</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6">
          The conference you're looking for doesn't exist or may have been removed.
        </p>
        <Button label="Browse All Conferences" icon="pi pi-search" @click="goBack" />
      </div>
      
      <!-- Conference Details -->
      <div v-else class="conference-details">
        <!-- Banner -->
        <div 
          class="conference-banner relative h-64 rounded-xl mb-8 overflow-hidden flex items-end"
          :style="{
            background: `linear-gradient(135deg, ${conference.primaryColor}, ${conference.secondaryColor})`
          }"
        >
          <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
          <div class="relative z-10 p-6 text-white">
            <div class="text-sm font-medium mb-1">
              {{ formatDateRange(conference.startDate, conference.endDate) }}
            </div>
            <h1 class="text-3xl font-bold mb-2">{{ conference.name }}</h1>
            <div class="flex items-center gap-2">
              <i class="pi pi-map-marker"></i>
              <span>{{ conference.location }}</span>
            </div>
          </div>
        </div>
        
        <!-- Quick Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
          <!-- University -->
          <Card class="shadow-sm">
            <template #content>
              <div class="flex items-start gap-3">
                <i class="pi pi-building text-xl mt-1 text-primary-500"></i>
                <div>
                  <h3 class="text-lg font-semibold mb-1">Hosting Institution</h3>
                  <p>{{ conference.university ? conference.university.name : 'Not specified' }}</p>
                </div>
              </div>
            </template>
          </Card>
          
          <!-- Duration -->
          <Card class="shadow-sm">
            <template #content>
              <div class="flex items-start gap-3">
                <i class="pi pi-calendar text-xl mt-1 text-primary-500"></i>
                <div>
                  <h3 class="text-lg font-semibold mb-1">Duration</h3>
                  <p>{{ calculateDuration(conference.startDate, conference.endDate) }} days</p>
                </div>
              </div>
            </template>
          </Card>
          
          <!-- Year -->
          <Card class="shadow-sm">
            <template #content>
              <div class="flex items-start gap-3">
                <i class="pi pi-clock text-xl mt-1 text-primary-500"></i>
                <div>
                  <h3 class="text-lg font-semibold mb-1">Year</h3>
                  <p>{{ conference.startDate ? new Date(conference.startDate).getFullYear() : 'Unknown' }}</p>
                </div>
              </div>
            </template>
          </Card>
        </div>
        
        <Card class="mb-8 shadow-sm">
          <template #title>
            <h2 class="text-xl font-semibold">About the Conference</h2>
          </template>
          
          <template #content>
            <p class="mb-4">{{ conference.description }}</p>
            
            <div v-if="conference.venueDetails" class="mt-4">
              <h3 class="text-lg font-semibold mb-2">Venue Details</h3>
              <p>{{ conference.venueDetails }}</p>
            </div>
          </template>
        </Card>
        
        <!-- Conference Editors -->
        <Card v-if="conference.editors && conference.editors.length > 0" class="shadow-sm">
          <template #title>
            <h2 class="text-xl font-semibold">Conference Organizers</h2>
          </template>
          
          <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="editor in conference.editors" :key="editor.id" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary-200 dark:bg-primary-800 flex items-center justify-center">
                  <span class="font-bold text-primary-800 dark:text-primary-200">
                    {{ editor.name.charAt(0) }}
                  </span>
                </div>
                <div>
                  <div class="font-semibold">{{ editor.name }}</div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">{{ editor.email }}</div>
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>
  </div>
</template>

<style scoped>
.conference-banner {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>