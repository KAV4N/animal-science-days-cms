<template>
  <div class="conference-detail">
    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="container mx-auto px-4 py-16 text-center">
        <i class="pi pi-spin pi-spinner text-4xl text-gray-400 mb-4"></i>
        <p class="text-gray-500">Loading conference details...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <div class="container mx-auto px-4 py-16 text-center">
        <div class="max-w-md mx-auto">
          <i class="pi pi-exclamation-triangle text-4xl text-red-400 mb-4"></i>
          <h3 class="text-xl font-semibold text-gray-700 mb-2">
            Conference Not Found
          </h3>
          <p class="text-gray-500 mb-6">
            {{ error }}
          </p>
          <router-link 
            to="/conferences" 
            class="btn btn-primary px-6 py-2 rounded-lg font-semibold"
          >
            Browse All Conferences
          </router-link>
        </div>
      </div>
    </div>

    <!-- Conference Content -->
    <div v-else-if="conference" class="conference-content">
      <!-- Conference Header -->
      <section class="conference-header py-16 px-4 text-white" :style="headerStyles">
        <div class="container mx-auto">
          <div class="max-w-4xl mx-auto text-center">
            <div class="mb-6">
              <router-link 
                to="/conferences" 
                class="inline-flex items-center text-white/80 hover:text-white mb-4 transition-colors"
              >
                <i class="pi pi-arrow-left mr-2"></i>
                Back to Conferences
              </router-link>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-bold mb-4">
              {{ conference.name }}
            </h1>
            <p class="text-xl md:text-2xl mb-6 opacity-90">
              {{ conference.title }}
            </p>
            
            <div class="flex flex-wrap justify-center gap-6 text-lg">
              <div class="flex items-center gap-2">
                <i class="pi pi-calendar"></i>
                <span>{{ formatDateRange(conference.start_date, conference.end_date) }}</span>
              </div>
              <div class="flex items-center gap-2">
                <i class="pi pi-map-marker"></i>
                <span>{{ conference.location }}</span>
              </div>
              <div v-if="conference.university" class="flex items-center gap-2">
                <i class="pi pi-building"></i>
                <span>{{ conference.university.name }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Conference Details -->
      <section class="py-16 px-4 bg-gray-50">
        <div class="container mx-auto">
          <div class="max-w-4xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12">
              <!-- Description -->
              <div class="bg-white p-8 rounded-xl shadow-sm">
                <h2 class="text-2xl font-bold mb-4" :style="{ color: conference.primary_color }">
                  About This Conference
                </h2>
                <p class="text-gray-600 leading-relaxed mb-6">
                  {{ conference.description || 'Explore the proceedings and research from this academic conference.' }}
                </p>
                <div v-if="conference.venue_details" class="border-t pt-6">
                  <h3 class="font-semibold text-gray-800 mb-2">Venue Details</h3>
                  <p class="text-gray-600">{{ conference.venue_details }}</p>
                </div>
              </div>

              <!-- Conference Information -->
              <div class="bg-white p-8 rounded-xl shadow-sm">
                <h2 class="text-2xl font-bold mb-4" :style="{ color: conference.primary_color }">
                  Conference Information
                </h2>
                <div class="space-y-4">
                  <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Start Date</span>
                    <span class="text-gray-600">{{ formatDate(conference.start_date) }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">End Date</span>
                    <span class="text-gray-600">{{ formatDate(conference.end_date) }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Location</span>
                    <span class="text-gray-600">{{ conference.location }}</span>
                  </div>
                  <div v-if="conference.university" class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Organization</span>
                    <span class="text-gray-600">{{ conference.university.name }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2">
                    <span class="font-medium text-gray-700">Status</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      Published
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Conference Pages -->
      <section class="py-16 px-4 bg-white">
        <div class="container mx-auto">
          <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12" :style="{ color: conference.primary_color }">
              Conference Content
            </h2>

            <!-- Loading Pages -->
            <div v-if="pagesLoading" class="text-center py-8">
              <i class="pi pi-spin pi-spinner text-2xl text-gray-400 mb-2"></i>
              <p class="text-gray-500">Loading conference pages...</p>
            </div>

            <!-- Pages Grid -->
            <div v-else-if="publishedPages.length > 0" class="grid md:grid-cols-2 gap-6">
              <router-link
                v-for="page in publishedPages"
                :key="page.id"
                :to="`/conferences/${conference.slug}/pages/${page.slug}`"
                class="page-card group bg-white border border-gray-200 rounded-xl p-6 hover:border-gray-300 hover:shadow-md transition-all duration-200"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800 group-hover:text-gray-900 mb-2">
                      {{ page.title }}
                    </h3>
                    <p class="text-gray-500 text-sm">
                      Click to view content
                    </p>
                  </div>
                  <i class="pi pi-chevron-right text-gray-400 group-hover:text-gray-600 transition-colors"></i>
                </div>
              </router-link>
            </div>

            <!-- No Pages Available -->
            <div v-else class="text-center py-12">
              <div class="max-w-md mx-auto">
                <i class="pi pi-file text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">
                  No Content Available
                </h3>
                <p class="text-gray-500">
                  This conference doesn't have any published content pages yet.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import apiService from '@/services/apiService';
import type { Conference } from '@/types/conference';
import type { PageMenu } from '@/types/pageMenu';
import type { ApiResponse } from '@/types/common';

export default defineComponent({
  name: 'ConferenceDetail',
  props: {
    conferenceSlug: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      conference: null as Conference | null,
      pages: [] as PageMenu[],
      loading: false,
      pagesLoading: false,
      error: null as string | null
    };
  },
  computed: {
    headerStyles() {
      const primaryColor = this.conference?.primary_color || '#3490dc';
      const secondaryColor = this.conference?.secondary_color || '#2563eb';
      
      return {
        background: `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`
      };
    },
    
    publishedPages(): PageMenu[] {
      return this.pages.filter(page => page.is_published);
    }
  },
  created() {
    this.loadConferenceData();
  },
  watch: {
    '$route.params.conferenceSlug': {
      handler(newSlug) {
        if (newSlug && newSlug !== this.conferenceSlug) {
          this.loadConferenceData();
        }
      },
      immediate: false
    }
  },
  methods: {
    async loadConferenceData() {
      this.loading = true;
      this.error = null;
      
      try {
        await this.fetchConference();
        await this.fetchConferencePages();
      } catch (error: any) {
        this.error = error.message || 'Failed to load conference data';
        console.error('Error loading conference:', error);
      } finally {
        this.loading = false;
      }
    },
    
    async fetchConference() {
      try {
        const response = await apiService.get<ApiResponse<Conference>>(
          `/v1/public/conferences/${this.conferenceSlug}`
        );
        
        this.conference = response.data.payload;
        
        // Apply conference styling
        if (this.conference.primary_color) {
          document.documentElement.style.setProperty('--primary-color', this.conference.primary_color);
        }
        if (this.conference.secondary_color) {
          document.documentElement.style.setProperty('--secondary-color', this.conference.secondary_color);
        }
      } catch (error: any) {
        if (error.response?.status === 404) {
          throw new Error('Conference not found or is not published');
        }
        throw new Error('Failed to load conference details');
      }
    },
    
    async fetchConferencePages() {
      if (!this.conference) return;
      
      this.pagesLoading = true;
      
      try {
        const response = await apiService.get<ApiResponse<PageMenu[]>>(
          `/v1/public/conferences/${this.conferenceSlug}/pages`
        );
        
        this.pages = response.data.payload || [];
      } catch (error) {
        console.error('Error loading conference pages:', error);
        this.pages = [];
      } finally {
        this.pagesLoading = false;
      }
    },
    
    formatDate(dateString: string): string {
      const date = new Date(dateString);
      return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    
    formatDateRange(startDate: string, endDate: string): string {
      const start = new Date(startDate);
      const end = new Date(endDate);
      
      const options: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      };
      
      if (start.toDateString() === end.toDateString()) {
        return start.toLocaleDateString(undefined, options);
      } else if (start.getFullYear() === end.getFullYear() && start.getMonth() === end.getMonth()) {
        return `${start.toLocaleDateString(undefined, { month: 'long', day: 'numeric' })} - ${end.toLocaleDateString(undefined, options)}`;
      } else {
        return `${start.toLocaleDateString(undefined, options)} - ${end.toLocaleDateString(undefined, options)}`;
      }
    }
  }
});
</script>

<style scoped>
.btn {
  display: inline-block;
  text-decoration: none;
  transition: all 0.3s ease;
}

.btn-primary {
  background-color: var(--primary-color, #3490dc);
  color: white;
}

.btn-primary:hover {
  background-color: var(--secondary-color, #2563eb);
}

.page-card {
  text-decoration: none;
  color: inherit;
}

.page-card:hover {
  text-decoration: none;
  color: inherit;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>