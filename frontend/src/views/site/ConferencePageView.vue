<template>
    <div class="conference-page">
      <!-- Header with page title and back button -->
      <div class="page-header p-4 bg-white shadow-sm mb-6">
        <div class="container mx-auto">
          <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold" :style="{ color: conference?.primary_color || '#333' }">
              {{ pageMenu?.title || 'Loading...' }}
            </h1>
            <Button 
              icon="pi pi-arrow-left" 
              label="Back to Conference" 
              @click="goBackToConference"
              class="p-button-secondary p-button-sm"
            />
          </div>
        </div>
      </div>
      
      <!-- Loading state -->
      <div v-if="loading" class="container mx-auto px-4 flex justify-center py-8">
        <i class="pi pi-spin pi-spinner text-3xl"></i>
      </div>
      
      <!-- Error message -->
      <div v-else-if="error" class="container mx-auto px-4">
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-md">
          {{ error }}
        </div>
      </div>
      
      <!-- Page not found -->
      <div v-else-if="!pageMenu" class="container mx-auto px-4">
        <div class="bg-yellow-100 border border-yellow-300 text-yellow-700 px-4 py-3 rounded-md">
          This page does not exist or has been removed.
        </div>
      </div>
      
      <!-- Page content -->
      <div v-else class="container mx-auto px-4 mb-8">
        <!-- Components -->
        <div v-for="component in publishedComponents" :key="component.id" class="mb-8">
          <!-- Editor Component -->
          <div v-if="component.component_type === 'Editor'" class="wysiwyg-content bg-white p-6 rounded-lg shadow-sm">
            <div v-html="component.data.content"></div>
          </div>
          
          <!-- Image Component -->
          <div v-else-if="component.component_type === 'Image'" class="image-component bg-white p-6 rounded-lg shadow-sm">
            <img :src="component.data.url" :alt="component.data.alt" class="w-full h-auto" />
            <p v-if="component.data.caption" class="text-center text-gray-600 mt-2">{{ component.data.caption }}</p>
          </div>
          
          <!-- Table Component -->
          <div v-else-if="component.component_type === 'Table'" class="table-component bg-white p-6 rounded-lg shadow-sm">
            <table class="w-full border-collapse">
              <tbody>
                <tr v-for="(row, rowIndex) in component.data.rows" :key="rowIndex">
                  <td 
                    v-for="(cell, cellIndex) in row" 
                    :key="cellIndex"
                    class="border border-gray-300 p-2"
                  >
                    {{ cell }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Video Component -->
          <div v-else-if="component.component_type === 'Video'" class="video-component bg-white p-6 rounded-lg shadow-sm">
            <div class="video-wrapper relative pt-[56.25%]">
              <iframe 
                class="absolute top-0 left-0 w-full h-full"
                :src="component.data.url"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              ></iframe>
            </div>
            <p v-if="component.data.caption" class="text-center text-gray-600 mt-2">{{ component.data.caption }}</p>
          </div>
          
          <!-- Gallery Component -->
          <div v-else-if="component.component_type === 'Gallery'" class="gallery-component bg-white p-6 rounded-lg shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div v-for="(image, imageIndex) in component.data.images" :key="imageIndex" class="gallery-item">
                <img :src="image.url" :alt="image.alt" class="w-full h-auto rounded" />
                <p v-if="image.caption" class="text-center text-gray-600 mt-1">{{ image.caption }}</p>
              </div>
            </div>
          </div>
          
          <!-- Unknown component type -->
          <div v-else class="unknown-component bg-white p-6 rounded-lg shadow-sm">
            <p class="text-yellow-600">Unknown component type: {{ component.component_type }}</p>
          </div>
        </div>
        
        <!-- No published components -->
        <div v-if="publishedComponents.length === 0" class="bg-gray-100 p-6 rounded-lg text-center">
          <p class="text-gray-500">This page has no published content yet.</p>
        </div>
      </div>
      
      <!-- Conference navigation footer -->
      <footer class="bg-gray-100 py-6 border-t border-gray-300">
        <div class="container mx-auto px-4">
          <h3 class="text-lg font-semibold mb-4" :style="{ color: conference?.primary_color || '#333' }">
            Conference Pages
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="menu in publishedMenus" 
              :key="menu.id"
              :class="['p-3 rounded-lg cursor-pointer transition-colors', 
                menu.id === pageMenu?.id ? 'bg-blue-100' : 'bg-white hover:bg-gray-50']"
              @click="navigateToPage(menu.slug)"
            >
              {{ menu.title }}
            </div>
          </div>
        </div>
      </footer>
    </div>
  </template>
  
  <script lang="ts">
  import { defineComponent } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import apiService from '@/services/apiService';
  import type { PageMenu, PageData } from '@/types/pageMenu';
  import type { Conference } from '@/types/conference';
  import type { ApiResponse } from '@/types/common';
  
  export default defineComponent({
    name: 'ConferencePageView',
    data() {
      return {
        conference: null as Conference | null,
        pageMenu: null as PageMenu | null,
        allMenus: [] as PageMenu[],
        loading: true,
        error: null as string | null,
      };
    },
    computed: {
      conferenceSlug(): string {
        return this.$route.params.conferenceSlug as string;
      },
      
      pageSlug(): string {
        return this.$route.params.pageSlug as string;
      },
      
      publishedComponents(): PageData[] {
        if (!this.pageMenu || !this.pageMenu.page_data) return [];
        
        return this.pageMenu.page_data
          .filter(component => component.is_published)
          .sort((a, b) => a.order - b.order);
      },
      
      publishedMenus(): PageMenu[] {
        return this.allMenus.filter(menu => menu.is_published);
      }
    },
    created() {
      this.loadData();
    },
    watch: {
      // Re-fetch data when route changes (i.e., navigating between pages)
      '$route.params': {
        handler() {
          this.loadData();
        },
        deep: true
      }
    },
    methods: {
      async loadData() {
        this.loading = true;
        this.error = null;
        
        try {
          // First, fetch the conference
          await this.fetchConference();
          
          // Then, fetch all published menus
          await this.fetchPublishedMenus();
          
          // Finally, fetch the specific page
          await this.fetchPageMenu();
        } catch (error: any) {
          this.error = error.message || 'An error occurred while loading the page.';
          console.error('Error loading page data:', error);
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
        } catch (error) {
          throw new Error('Conference not found or unavailable.');
        }
      },
      
      async fetchPublishedMenus() {
        if (!this.conference) return;
        
        try {
          const response = await apiService.get(
            `/v1/public/conferences/${this.conferenceSlug}/pages`
          );
          
          this.allMenus = response.data.payload;
        } catch (error) {
          console.error('Error fetching published menus:', error);
          this.allMenus = [];
        }
      },
      
      async fetchPageMenu() {
        if (!this.conference) return;
        
        try {
          const response = await apiService.get(
            `/v1/public/conferences/${this.conferenceSlug}/pages/${this.pageSlug}`
          );
          
          this.pageMenu = response.data.payload;
          
          // Apply conference styling
          if (this.conference.primary_color) {
            document.documentElement.style.setProperty('--primary-color', this.conference.primary_color);
          }
          if (this.conference.secondary_color) {
            document.documentElement.style.setProperty('--secondary-color', this.conference.secondary_color);
          }
        } catch (error) {
          // If the page doesn't exist, check if we have any published pages and redirect to the first one
          if (this.publishedMenus.length > 0) {
            const firstPage = this.publishedMenus[0];
            this.$router.replace(`/conferences/${this.conferenceSlug}/pages/${firstPage.slug}`);
          } else {
            throw new Error('The requested page does not exist or is not published.');
          }
        }
      },
      
      navigateToPage(slug: string) {
        if (slug === this.pageSlug) return;
        
        this.$router.push(`/conferences/${this.conferenceSlug}/pages/${slug}`);
      },
      
      goBackToConference() {
        this.$router.push(`/conferences/${this.conferenceSlug}`);
      }
    }
  });
  </script>
  
  <style scoped>
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
  
  /* Transitions */
  .page-list-enter-active,
  .page-list-leave-active,
  .component-list-enter-active,
  .component-list-leave-active {
    transition: all 0.3s ease;
  }
  
  .page-list-enter-from,
  .page-list-leave-to,
  .component-list-enter-from,
  .component-list-leave-to {
    opacity: 0;
    transform: translateY(20px);
  }
  </style>