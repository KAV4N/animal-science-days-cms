<template>
  <div class="home-page">
    <!-- Hero Section -->
    <section class="hero-section py-16 px-4" :style="heroStyles">
      <div class="container mx-auto text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6 text-white">
          Academic Conferences
        </h1>
        <p class="text-xl md:text-2xl mb-8 text-white/90">
          Discover and explore conference proceedings and research
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link 
            to="/conferences" 
            class="btn btn-primary px-8 py-3 rounded-lg font-semibold text-lg transition-all hover:shadow-lg"
          >
            Browse All Conferences
          </router-link>
        </div>
      </div>
    </section>

    <!-- Loading State -->
    <section v-if="loading" class="py-16 px-4">
      <div class="container mx-auto text-center">
        <i class="pi pi-spin pi-spinner text-4xl text-gray-400 mb-4"></i>
        <p class="text-gray-500">Loading latest conference...</p>
      </div>
    </section>

    <!-- Error State -->
    <section v-else-if="error" class="py-16 px-4">
      <div class="container mx-auto text-center">
        <div class="max-w-md mx-auto">
          <i class="pi pi-exclamation-triangle text-4xl text-red-400 mb-4"></i>
          <h3 class="text-xl font-semibold text-gray-700 mb-2">
            Unable to Load Conference
          </h3>
          <p class="text-gray-500 mb-6">
            {{ error }}
          </p>
          <button 
            @click="loadLatestConference" 
            class="btn btn-primary px-6 py-2 rounded-lg font-semibold"
          >
            Try Again
          </button>
        </div>
      </div>
    </section>

    <!-- Latest Conference Content -->
    <div v-else-if="latestConference" class="latest-conference-content">
      <!-- Conference Header -->
      <section class="conference-header py-12 px-4 bg-white border-b border-gray-200">
        <div class="container mx-auto">
          <div class="max-w-6xl mx-auto">
            <div class="text-center mb-8">
              <h2 class="text-3xl md:text-4xl font-bold mb-4" :style="{ color: latestConference.primary_color }">
                Latest Conference
              </h2>
              <div class="inline-flex items-center gap-2 text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                <i class="pi pi-star-fill text-yellow-500"></i>
                <span>Featured Content</span>
              </div>
            </div>

            <!-- Conference Info Card -->
            <div class="bg-gradient-to-r p-8 rounded-xl text-white mb-8" :style="conferenceHeaderStyles">
              <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="mb-4 lg:mb-0">
                  <h3 class="text-2xl md:text-3xl font-bold mb-2">
                    {{ latestConference.name }}
                  </h3>
                  <p class="text-lg opacity-90 mb-2">
                    {{ latestConference.title }}
                  </p>
                  <div class="flex flex-wrap gap-4 text-sm opacity-80">
                    <span class="flex items-center gap-1">
                      <i class="pi pi-calendar"></i>
                      {{ formatDateRange(latestConference.start_date, latestConference.end_date) }}
                    </span>
                    <span class="flex items-center gap-1">
                      <i class="pi pi-map-marker"></i>
                      {{ latestConference.location }}
                    </span>
                    <span v-if="latestConference.university" class="flex items-center gap-1">
                      <i class="pi pi-building"></i>
                      {{ latestConference.university.name }}
                    </span>
                  </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                  <router-link 
                    :to="`/conferences/${latestConference.slug}`"
                    class="btn bg-white text-gray-800 px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-colors text-center"
                  >
                    View Conference Details
                  </router-link>
                </div>
              </div>
            </div>

            <!-- Conference Description -->
            <div v-if="latestConference.description" class="bg-white p-6 rounded-lg shadow-sm mb-8">
              <h4 class="text-lg font-semibold mb-3" :style="{ color: latestConference.primary_color }">
                About This Conference
              </h4>
              <p class="text-gray-600 leading-relaxed">
                {{ latestConference.description }}
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Conference Pages Content -->
      <section v-if="conferencePages.length > 0" class="conference-pages py-8 px-4">
        <div class="container mx-auto">
          <div class="max-w-6xl mx-auto">
            <!-- Page Navigation -->
            <div class="mb-8">
              <div class="flex flex-wrap gap-2 justify-center">
                <button
                  v-for="(page, index) in conferencePages"
                  :key="page.id"
                  @click="setActivePage(index)"
                  :class="[
                    'px-4 py-2 rounded-lg font-medium transition-all',
                    activePageIndex === index
                      ? 'text-white shadow-md'
                      : 'text-gray-600 bg-gray-100 hover:bg-gray-200'
                  ]"
                  :style="activePageIndex === index ? { backgroundColor: latestConference.primary_color } : {}"
                >
                  {{ page.title }}
                </button>
              </div>
            </div>

            <!-- Active Page Content -->
            <div v-if="activePage" class="page-content">
              <div class="mb-6">
                <h3 class="text-2xl font-bold mb-2" :style="{ color: latestConference.primary_color }">
                  {{ activePage.title }}
                </h3>
                <div class="flex items-center gap-4 text-sm text-gray-500">
                  <span>{{ publishedComponents.length }} components</span>
                  <router-link 
                    :to="`/conferences/${latestConference.slug}/pages/${activePage.slug}`"
                    class="text-blue-600 hover:text-blue-700 transition-colors"
                  >
                    View full page →
                  </router-link>
                </div>
              </div>

              <!-- Page Components -->
              <div v-if="publishedComponents.length > 0" class="space-y-8">
                <div v-for="component in publishedComponents" :key="component.id" class="component-container">
                  <!-- Editor Component -->
                  <div v-if="component.component_type === 'Editor'" class="wysiwyg-content bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div v-html="component.data.content"></div>
                  </div>
                  
                  <!-- Image Component -->
                  <div v-else-if="component.component_type === 'Image'" class="image-component bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <img :src="component.data.url" :alt="component.data.alt" class="w-full h-auto rounded-lg" />
                    <p v-if="component.data.caption" class="text-center text-gray-600 mt-4 font-medium">{{ component.data.caption }}</p>
                  </div>
                  
                  <!-- Table Component -->
                  <div v-else-if="component.component_type === 'Table'" class="table-component bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="overflow-x-auto">
                      <table class="w-full border-collapse">
                        <tbody>
                          <tr v-for="(row, rowIndex) in component.data.rows" :key="rowIndex">
                            <td 
                              v-for="(cell, cellIndex) in row" 
                              :key="cellIndex"
                              class="border border-gray-300 p-3 text-sm"
                            >
                              {{ cell }}
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  
                  <!-- Video Component -->
                  <div v-else-if="component.component_type === 'Video'" class="video-component bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="video-wrapper relative pt-[56.25%] rounded-lg overflow-hidden">
                      <iframe 
                        class="absolute top-0 left-0 w-full h-full"
                        :src="component.data.url"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                      ></iframe>
                    </div>
                    <p v-if="component.data.caption" class="text-center text-gray-600 mt-4 font-medium">{{ component.data.caption }}</p>
                  </div>
                  
                  <!-- Gallery Component -->
                  <div v-else-if="component.component_type === 'Gallery'" class="gallery-component bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                      <div v-for="(image, imageIndex) in component.data.images" :key="imageIndex" class="gallery-item">
                        <img :src="image.url" :alt="image.alt" class="w-full h-auto rounded-lg shadow-sm" />
                        <p v-if="image.caption" class="text-center text-gray-600 mt-2 text-sm">{{ image.caption }}</p>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Unknown component type -->
                  <div v-else class="unknown-component bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center gap-3 text-yellow-600">
                      <i class="pi pi-exclamation-triangle"></i>
                      <span>Unknown component type: {{ component.component_type }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- No published components -->
              <div v-else class="bg-gray-100 p-8 rounded-lg text-center">
                <i class="pi pi-file text-2xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">This page has no published content yet.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- No Pages Available -->
      <section v-else class="py-16 px-4 bg-gray-50">
        <div class="container mx-auto text-center">
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
      </section>
    </div>

    <!-- No Latest Conference -->
    <section v-else class="py-16 px-4 bg-gray-50">
      <div class="container mx-auto text-center">
        <div class="max-w-md mx-auto">
          <i class="pi pi-info-circle text-4xl text-gray-400 mb-4"></i>
          <h3 class="text-xl font-semibold text-gray-700 mb-2">
            No Conferences Available
          </h3>
          <p class="text-gray-500 mb-6">
            There are currently no published conferences to display.
          </p>
          <router-link 
            to="/conferences" 
            class="btn btn-primary px-6 py-2 rounded-lg font-semibold"
          >
            Browse All Conferences
          </router-link>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 px-4 bg-white border-t border-gray-200">
      <div class="container mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-800">
            Explore Academic Research
          </h2>
          <p class="text-gray-600 text-lg max-w-2xl mx-auto">
            Access comprehensive conference proceedings, research papers, and academic content from leading institutions.
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <div class="text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="pi pi-book text-2xl text-blue-600"></i>
            </div>
            <h3 class="text-xl font-semibold mb-2 text-gray-800">
              Conference Proceedings
            </h3>
            <p class="text-gray-600">
              Browse through comprehensive proceedings from academic conferences across various disciplines.
            </p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="pi pi-search text-2xl text-green-600"></i>
            </div>
            <h3 class="text-xl font-semibold mb-2 text-gray-800">
              Easy Discovery
            </h3>
            <p class="text-gray-600">
              Find conferences by decade, institution, or topic with our intuitive browsing system.
            </p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="pi pi-users text-2xl text-purple-600"></i>
            </div>
            <h3 class="text-xl font-semibold mb-2 text-gray-800">
              Academic Community
            </h3>
            <p class="text-gray-600">
              Connect with research from leading universities and academic institutions worldwide.
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import apiService from '@/services/apiService';
import type { Conference } from '@/types/conference';
import type { PageMenu, PageData } from '@/types/pageMenu';
import type { ApiResponse } from '@/types/common';

export default defineComponent({
  name: 'Home',
  data() {
    return {
      latestConference: null as Conference | null,
      conferencePages: [] as PageMenu[],
      activePageIndex: 0,
      loading: false,
      error: null as string | null
    };
  },
  computed: {
    heroStyles() {
      const baseColor = this.latestConference?.primary_color || '#3490dc';
      const secondaryColor = this.latestConference?.secondary_color || '#2563eb';
      
      return {
        background: `linear-gradient(135deg, ${baseColor} 0%, ${secondaryColor} 100%)`,
        backgroundAttachment: 'fixed'
      };
    },
    
    conferenceHeaderStyles() {
      const primaryColor = this.latestConference?.primary_color || '#3490dc';
      const secondaryColor = this.latestConference?.secondary_color || '#2563eb';
      
      return {
        background: `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`
      };
    },
    
    activePage(): PageMenu | null {
      return this.conferencePages[this.activePageIndex] || null;
    },
    
    publishedComponents(): PageData[] {
      if (!this.activePage || !this.activePage.page_data) return [];
      
      return this.activePage.page_data
        .filter(component => component.is_published)
        .sort((a, b) => a.order - b.order);
    }
  },
  created() {
    this.loadLatestConference();
  },
  methods: {
    async loadLatestConference() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.get<ApiResponse<Conference & { pages?: PageMenu[] }>>(
          '/v1/public/conferences/latest/with-pages'
        );
        
        this.latestConference = response.data.payload;
        this.conferencePages = response.data.payload.pages || [];
        this.activePageIndex = 0; // Reset to first page
        
        // Apply dynamic styling
        if (this.latestConference?.primary_color) {
          document.documentElement.style.setProperty('--primary-color', this.latestConference.primary_color);
        }
        if (this.latestConference?.secondary_color) {
          document.documentElement.style.setProperty('--secondary-color', this.latestConference.secondary_color);
        }
      } catch (error: any) {
        console.error('Error loading latest conference:', error);
        this.error = error.response?.data?.message || 'Failed to load latest conference';
      } finally {
        this.loading = false;
      }
    },
    
    setActivePage(index: number) {
      this.activePageIndex = index;
      
      // Smooth scroll to page content
      this.$nextTick(() => {
        const pageContent = document.querySelector('.page-content');
        if (pageContent) {
          pageContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
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
  transform: translateY(-2px);
}

.hero-section {
  min-height: 60vh;
  display: flex;
  align-items: center;
}

/* Custom styling for the wysiwyg content */
.wysiwyg-content :deep(h1) {
  font-size: 1.8rem;
  font-weight: bold;
  margin-bottom: 1rem;
}

.wysiwyg-content :deep(h2) {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 0.75rem;
}

.wysiwyg-content :deep(h3) {
  font-size: 1.25rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.wysiwyg-content :deep(p) {
  margin-bottom: 1rem;
}

.wysiwyg-content :deep(ul), 
.wysiwyg-content :deep(ol) {
  margin-bottom: 1rem;
  padding-left: 1.5rem;
}

.wysiwyg-content :deep(li) {
  margin-bottom: 0.25rem;
}

.wysiwyg-content :deep(a) {
  color: var(--primary-color, #3490dc);
  text-decoration: underline;
}

.wysiwyg-content :deep(img) {
  max-width: 100%;
  height: auto;
  margin: 1rem 0;
}

.wysiwyg-content :deep(table) {
  border-collapse: collapse;
  width: 100%;
  margin-bottom: 1rem;
}

.wysiwyg-content :deep(table td),
.wysiwyg-content :deep(table th) {
  border: 1px solid #ddd;
  padding: 0.5rem;
}

.wysiwyg-content :deep(table tr:nth-child(even)) {
  background-color: #f2f2f2;
}

.wysiwyg-content :deep(blockquote) {
  border-left: 4px solid #ccc;
  margin-left: 0;
  padding-left: 1rem;
  color: #666;
  font-style: italic;
}

/* Component transitions */
.component-container {
  transition: all 0.3s ease;
}

.component-container:hover {
  transform: translateY(-2px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .hero-section {
    min-height: 50vh;
  }
}

/* Animation for loading and error states */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>