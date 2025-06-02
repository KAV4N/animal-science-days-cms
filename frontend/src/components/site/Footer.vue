<template>
  <footer class="bg-surface-50 text-surface-700 dark:bg-surface-800 dark:text-surface-100 border-t border-surface-200 dark:border-surface-700">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <div v-if="publicConferenceLoading" class="text-center text-surface-500 dark:text-surface-400">
        Loading conference details...
      </div>
      <div v-else-if="currentPublicConference" class="flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="text-center md:text-left">
          <div class="flex items-center justify-center md:justify-start mb-1">
            <img class="h-7 w-auto mr-2" src="/school-logo.png" alt="Default Logo" />
            <span class="font-semibold text-md">{{ currentPublicConference.title }}</span>
          </div>
          <p class="text-xs text-surface-500 dark:text-surface-400">
            {{ currentPublicConference.location }} | {{ formatDateRange(currentPublicConference.start_date, currentPublicConference.end_date) }}
          </p>
          <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">
            &copy; {{ new Date().getFullYear() }} {{ currentPublicConference.university?.full_name || 'Your Organization' }}. All rights reserved.
          </p>
        </div>

        <nav class="flex flex-wrap justify-center md:justify-end gap-x-5 gap-y-2">
          <router-link :to="conferenceHomeLink" class="text-sm hover:text-primary-600 dark:hover:text-primary-400 transition-colors duration-200">
            Conference Home
          </router-link>
          <router-link to="/archive" class="text-sm hover:text-primary-600 dark:hover:text-primary-400 transition-colors duration-200">
            Conference Archive
          </router-link>
        </nav>
      </div>
      <div v-else class="text-center text-surface-500 dark:text-surface-400">
        <div class="flex items-center justify-center md:justify-start mb-1">
            <img class="h-7 w-auto mr-2" src="/school-logo.png" alt="Logo" />
            <span class="font-semibold text-md">School Conference Platform</span>
          </div>
        <p>&copy; {{ new Date().getFullYear() }}. All rights reserved.</p>
      </div>

       <div v-if="!publicConferenceLoading && !currentPublicConference" class="mt-8 pt-8 border-t border-surface-200 dark:border-surface-700 text-center text-xs text-surface-400 dark:text-surface-500">
        <p>Empowering Academic Conferences Worldwide.</p>
      </div>
      <div v-else-if="currentPublicConference" class="mt-6 pt-6 border-t border-surface-200 dark:border-surface-700 text-center text-xs text-surface-500 dark:text-surface-400">
        <p>Organized by: {{ currentPublicConference.university?.full_name || 'The Organizers' }}</p>
      </div>
    </div>
  </footer>
</template>

<script lang="ts">
import { defineComponent, computed } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';
import { storeToRefs } from 'pinia';

export default defineComponent({
  name: 'SiteFooter',
  setup() {
    const conferenceStore = useConferenceStore();
    const { currentPublicConference, publicConferenceLoading, publicConferenceError } = storeToRefs(conferenceStore);

    const conferenceHomeLink = computed(() => {
      if (currentPublicConference.value?.slug) {
        return `/conferences/${currentPublicConference.value.slug}`;
      }
      return '/';
    });

    const formatDate = (dateStr: string | null | undefined): string => {
      if (!dateStr) return '';
      try {
        const date = new Date(dateStr);
        return date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
      } catch {
        return dateStr;
      }
    };

    const formatDateRange = (startDateStr: string | null | undefined, endDateStr: string | null | undefined): string => {
      const start = formatDate(startDateStr);
      const end = formatDate(endDateStr);
      if (start && end) {
        if (start === end) return start;
        return `${start} - ${end}`;
      }
      return start || end || 'Dates TBD';
    };

    return {
      currentPublicConference,
      publicConferenceLoading,
      publicConferenceError,
      conferenceHomeLink,
      formatDateRange,
    };
  }
});
</script>

<style scoped>
</style>
