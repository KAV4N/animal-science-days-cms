<template>
  <div class="max-w-7xl mx-auto">
    <div class="text-center mb-8">
      <h1 class="text-4xl font-bold text-gray-800 mb-2">Conference Archive</h1>
      <p class="text-gray-500">Browse our historical collection of academic conferences</p>
    </div>
    
    <div class="flex flex-col md:flex-row gap-2">
      <!-- Using h-full to match heights at the container level -->
      <div class="w-full md:w-72 flex flex-col">
        <Card class="h-full flex flex-col">
          <template #header>
            <div class="bg-primary text-white p-4 font-semibold text-lg rounded-t-lg flex items-center min-h-18">
              Browse by Decade
            </div>
          </template>
          <template #content>
            <!-- Flex-grow to make the content expand to fill available space -->
            <div class="flex-grow">
              <div v-if="loading" class="flex justify-center p-4">
                <ProgressSpinner strokeWidth="4" />
              </div>
              <div v-else-if="error" class="p-4 bg-red-50 text-red-700 rounded-lg">
                {{ error }}
                <Button label="Retry" icon="pi pi-refresh" class="p-button-sm mt-2" @click="fetchDecades" />
              </div>
              <div v-else class="h-full">
                <div v-for="decade in sortedDecades" :key="decade.decade"
                  @click="selectDecade(decade.decade)"
                  class="p-4 cursor-pointer border-b last:border-b-0 transition-all duration-200 flex justify-between items-center"
                  :class="{'bg-primary-50 text-primary-800': selectedDecade === decade.decade, 
                          'hover:bg-gray-50': selectedDecade !== decade.decade}">
                  <div class="flex items-center">
                    <i class="pi pi-calendar-plus mr-2" :class="{'text-primary': selectedDecade === decade.decade}"></i>
                    <span class="text-lg">{{ decade.decade }}s</span>
                  </div>
                  <Badge :value="decade.count" :severity="selectedDecade === decade.decade ? 'info' : 'secondary'" />
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>
      
      <!-- Using h-full to match heights at the container level -->
      <div class="flex-1 flex flex-col">
        <Card class="h-full flex flex-col">
          <template #header>
            <div v-if="selectedDecade" class="bg-primary text-white p-4 rounded-t-lg flex items-center min-h-16">
              <div class="flex justify-between items-center w-full">
                <h2 class="text-lg font-semibold">{{ selectedDecade }}s Conferences</h2>
                <Chip :label="`${conferences.length} conferences`" class="bg-primary-700" />
              </div>
            </div>
            <div v-else class="bg-primary text-white p-4 rounded-t-lg flex items-center min-h-16">
              <h2 class="text-lg font-semibold">Conference Archive</h2>
            </div>
          </template>
          
          <template #content>
            <!-- Flex-grow to make the content expand to fill available space -->
            <div class="flex-grow">
              <div v-if="conferencesLoading" class="flex justify-center items-center py-12">
                <ProgressSpinner strokeWidth="4" />
              </div>
              
              <div v-else-if="!selectedDecade" class="text-center py-12 px-4">
                <i class="pi pi-calendar text-primary-200 text-6xl mb-4"></i>
                <h3 class="text-xl text-gray-600 mb-2">Please select a decade</h3>
                <p class="text-gray-500">Choose a decade from the sidebar to browse conferences</p>
              </div>
              
              <div v-else-if="conferences.length === 0" class="p-6 rounded text-center">
                <i class="pi pi-search text-primary-200 text-6xl mb-4"></i>
                <h3 class="text-xl text-gray-600 mb-2">No conferences found</h3>
                <p class="text-gray-500">There are no conferences available for this decade</p>
              </div>
              
              <div v-else class="space-y-4 p-4">
                <div v-for="conference in conferences" :key="conference.id" 
                    class="border-l-4 shadow pl-4 p-3 transition-colors rounded"
                    :style="{borderLeftColor: conference.primary_color || 'var(--primary-color)'}">
                  <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                    <div class="flex-1">
                      <div class="flex justify-between items-start">
                        <h3 class="text-xl font-medium text-primary-700 truncate" :title="conference.name + ' ' + conference.title">
                          {{ truncateText(conference.name + ' ' + conference.title, 60) }}
                        </h3>
                        <Button 
                          icon="pi pi-external-link" 
                          label="View Details" 
                          class="p-button-sm ml-2 flex-shrink-0"
                          @click="goToConference(conference)"
                        />
                      </div>
                      
                      <div class="flex flex-wrap gap-2 mt-3">
                        <div class="flex items-center px-3 py-2 bg-gray-100 rounded-full">
                          <i class="pi pi-calendar mr-2 text-gray-600"></i>
                          <span class="text-sm">{{ formatDate(conference.start_date) }} - {{ formatDate(conference.end_date) }}</span>
                        </div>
                        
                        <div class="flex items-center px-3 py-2 bg-gray-100 rounded-full">
                          <i class="pi pi-map-marker mr-2 text-gray-600"></i>
                          <span class="text-sm truncate" :title="conference.location">
                            {{ truncateText(conference.location, 30) }}
                          </span>
                        </div>
                        
                        <div v-if="conference.university && conference.university.full_name" 
                          class="flex items-center px-3 py-2 bg-gray-100 rounded-full">
                          <i class="pi pi-building mr-2 text-gray-600"></i>
                          <span class="text-sm truncate" :title="conference.university.full_name">
                            {{ truncateText(conference.university.full_name, 30) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>
  </div>
</template>
<script lang="ts">
import { defineComponent } from 'vue';
import { useRouter } from 'vue-router';
import apiService from '@/services/apiService';
import type { Conference } from '@/types/conference';
import type { Decade } from '@/types/decade';
import type { ApiResponse, ApiPaginatedResponse } from '@/types/common';

export default defineComponent({
  name: 'ConferenceArchive',

  data() {
    return {
      loading: false,
      error: null as string | null,
      decades: [] as Decade[],
      selectedDecade: null as number | null,

      conferences: [] as Conference[],
      conferencesLoading: false,
    };
  },

  computed: {
    sortedDecades() {
      return [...this.decades].sort((a, b) => b.decade - a.decade);
    }
  },

  created() {
    this.fetchDecades();
  },

  methods: {
    truncateText(text: string, maxLength: number): string {
      if (!text) return '';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },

    async fetchDecades() {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.get<ApiResponse<Decade[]>>('/v1/public/conferences/decades');
        this.decades = response.data.payload.map(decade => ({
          ...decade,
          decade: typeof decade.decade === 'string' ? parseInt(decade.decade) : decade.decade
        }));

        if (this.decades.length > 0) {
          const latestDecade = this.decades.reduce((latest, current) =>
            latest.decade > current.decade ? latest : current
          ).decade;

          this.selectDecade(latestDecade);
        }
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Failed to load conference decades';
        console.error('Error fetching decades:', err);
      } finally {
        this.loading = false;
      }
    },

    selectDecade(decade: number) {
      this.selectedDecade = decade;
      this.fetchConferences();
    },

    async fetchConferences() {
      if (!this.selectedDecade) return;

      this.conferencesLoading = true;

      try {
        const response = await apiService.get<ApiPaginatedResponse<Conference[]>>(
          `/v1/public/conferences/decades/${this.selectedDecade}`,
          {
            params: {
              page: 1,
              per_page: 100,
              sort_field: 'start_date',
              sort_order: 'desc',
              include: 'university'
            }
          }
        );

        this.conferences = response.data.payload;
      } catch (err: any) {
        console.error('Error fetching conferences:', err);
        this.conferences = [];
      } finally {
        this.conferencesLoading = false;
      }
    },

    formatDate(dateString: string): string {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      }).format(date);
    },

    goToConference(conference: Conference) {
      const router = useRouter();
      router.push({
        name: 'conference-detail',
        params: { slug: conference.slug }
      });
    }
  }
});
</script>
