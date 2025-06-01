<template>
  <div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-1 py-1">

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center min-h-[60vh] space-y-1">
        <Card class="w-full max-w-md mx-auto">
          <template #content>
            <div class="flex flex-col items-center space-y-1 p-1">
              <ProgressSpinner class="w-12 h-12" strokeWidth="3" />
              <p class="text-lg font-medium">Loading conference...</p>
            </div>
          </template>
        </Card>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="flex flex-col items-center justify-center min-h-[60vh] space-y-1">
        <Card class="w-full max-w-md text-center">
          <template #content>
            <div class="flex flex-col items-center space-y-1 p-1">
              <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                <i class="pi pi-exclamation-triangle text-2xl text-red-600"></i>
              </div>
              <h2 class="text-2xl font-bold text-gray-900">Something went wrong</h2>
              <p class="text-gray-600">{{ error }}</p>
              <Button 
                label="Try Again" 
                @click="loadConference" 
                class="mt-1"
                icon="pi pi-refresh"
              />
            </div>
          </template>
        </Card>
      </div>

      <!-- Main Content -->
      <div v-else-if="conference" class="space-y-1">
        
        <!-- Conference Header -->
        <div class="bg-white rounded-lg shadow-sm p-2">
          <div class="px-1 py-1 text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-1">{{ conference.title }}</h1>
            <div class="flex flex-col sm:flex-row items-center justify-center space-y-1 sm:space-y-0 sm:space-x-8 text-gray-600">
              <div class="flex items-center space-x-1">
                <i class="pi pi-map-marker text-primary"></i>
                <span>{{ conference.location }}</span>
              </div>
              <div class="flex items-center space-x-1">
                <i class="pi pi-calendar text-primary"></i>
                <span>
                  {{ formatDate(conference.start_date) }} - {{ formatDate(conference.end_date) }}
                </span>
              </div>
            </div>
            <div v-if="conference.description" class="mt-1 text-gray-700 max-w-3xl mx-auto">
              <p>{{ conference.description }}</p>
            </div>
          </div>
        </div>

        <!-- Navigation & Content Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-1">
          
          <!-- Sidebar Navigation -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-1 sticky top-1">
              <h2 class="text-lg font-semibold text-gray-900 mb-1 flex items-center">
                <i class="pi pi-list mr-1 text-primary p-4"></i>
                Pages
              </h2>
              
              <div v-if="pagesLoading" class="flex flex-col items-center space-y-1 py-1">
                <ProgressSpinner class="w-6 h-6" strokeWidth="4" />
                <p class="text-sm text-gray-600">Loading pages...</p>
              </div>
              
              <nav v-else-if="sortedPages?.length > 0" class="space-y-1">
                <button
                  v-for="page in sortedPages" 
                  :key="page.id"
                  @click="selectPage(page)"
                  :class="[
                    'w-full text-left px-3 py-3  transition-colors duration-200 cursor-pointer block',
                    activePageId === page.id
                      ? 'bg-primary-50 text-primary-700 border-l-4 border-primary-600'
                      : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                  ]"
                >
                  <div class="flex items-center space-x-1">
                    <i class="pi pi-file text-xs"></i>
                    <span>{{ page.title }}</span>
                  </div>
                </button>
              </nav>
              
              <div v-else class="text-center py-1">
                <div class="flex flex-col items-center space-y-1">
                  <i class="pi pi-inbox text-3xl text-gray-400"></i>
                  <p class="text-sm text-gray-600">No pages available</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Main Content Area -->
          <div class="lg:col-span-3 space-y-1">

            <!-- Active Page Header -->
            <div v-if="activePage" class="p-4 bg-white rounded-lg shadow-sm">
              <div class="px-1 py-1">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h2 class="text-3xl font-bold text-gray-900 mb-1">{{ activePage.title }}</h2>
                    <div class="flex items-center space-x-1 text-sm text-gray-500">
                      <span class="flex items-center space-x-1">
                        <i class="pi pi-clock"></i>
                        <span>Last updated {{ formatDate(activePage.updated_at || activePage.created_at) }}</span>
                      </span>
                    </div>
                  </div>
                  <div class="flex items-center space-x-1">
                    <Chip 
                      :label="`Page ${activePage.order || 1}`" 
                      class="bg-primary-100 text-primary-800"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Welcome/Default State -->
            <div v-else class="bg-white rounded-lg shadow-sm">
              <div class="text-center space-y-1 p-1">
                <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center mx-auto">
                  <i class="pi pi-home text-3xl text-primary-600"></i>
                </div>
                <div>
                  <h2 class="text-3xl font-bold text-gray-900 mb-1">
                    Welcome to {{ conference.title }}
                  </h2>
                  <p v-if="pagesLoading" class="text-gray-600">Loading pages...</p>
                  <p v-else-if="sortedPages?.length" class="text-gray-600">
                    Select a page from the sidebar to view its content.
                  </p>
                  <p v-else class="text-gray-600">No pages are available for this conference yet.</p>
                </div>
                <div v-if="sortedPages?.length" class="flex flex-wrap gap-1 justify-center">
                  <Button 
                    v-for="page in sortedPages.slice(0, 3)" 
                    :key="page.id"
                    :label="page.title"
                    @click="selectPage(page)"
                    outlined
                    size="small"
                    class="rounded-full"
                  />
                </div>
              </div>
            </div>

            <!-- Page Components -->
            <template v-if="activePage?.page_data?.length">
              <div 
                v-for="(pageData, index) in publishedPageData" 
                :key="pageData.id"
                class="bg-white rounded-lg shadow-sm overflow-hidden"
              >
                <div class="p-1">
                  <!-- Dynamic Component Rendering -->
                  <component
                    :is="getPublicComponent(pageData.component_type)"
                    :data="pageData.data"
                    :component-name="pageData.name"
                    :conference-id="conference.id"
                    v-if="getPublicComponent(pageData.component_type)"
                  />
                  
                  <!-- Fallback for unknown component types -->
                  <div v-else class="bg-gray-50 rounded-lg p-1 border-2 border-dashed border-gray-300">
                    <div class="text-center">
                      <i class="pi pi-code text-2xl text-gray-400 mb-1"></i>
                      <h3 class="text-lg font-medium text-gray-900 mb-1">Unknown Component</h3>
                      <p class="text-gray-600 mb-1">Component type "{{ pageData.component_type }}" is not recognized.</p>
                      <details class="text-left">
                        <summary class="cursor-pointer text-sm text-gray-500 hover:text-gray-700">View Raw Data</summary>
                        <pre class="mt-1 p-1 bg-white rounded border text-xs overflow-x-auto">{{ JSON.stringify(pageData.data, null, 2) }}</pre>
                      </details>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <!-- Empty Page State -->
            <div v-else-if="activePage && !activePage.page_data?.length" class="bg-white rounded-lg shadow-sm">
              <div class="text-center space-y-1 p-1">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto">
                  <i class="pi pi-file-o text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900">No content available</h3>
                <p class="text-gray-600">This page doesn't have any content yet.</p>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, defineAsyncComponent } from 'vue';
import apiService from '@/services/apiService';
import { getComponentDefinition } from '@/utils/componentRegistry';
import type { Conference } from '@/types/conference';
import type { PageMenu } from '@/types/pageMenu';

interface ConferenceWithPages extends Conference {
  pages?: PageMenu[];
}

interface ComponentData {
  conference: ConferenceWithPages | null;
  activePage: PageMenu | null;
  activePageId: number | null;
  loading: boolean;
  pagesLoading: boolean;
  error: string | null;
  loadedComponents: Map<string, any>;
}

export default defineComponent({
  name: 'ConferenceView',
  props: {
    slug: {
      type: String,
      required: false,
      default: ''
    }
  },
  data(): ComponentData {
    return {
      conference: null,
      activePage: null,
      activePageId: null,
      loading: false,
      pagesLoading: false,
      error: null,
      loadedComponents: new Map()
    };
  },
  computed: {
    sortedPages(): PageMenu[] {
      if (!this.conference?.pages) {
        return [];
      }
      return [...this.conference.pages]
        .filter(page => page.is_published)
        .sort((a, b) => (a.order || 0) - (b.order || 0));
    },
    
    publishedPageData() {
      if (!this.activePage?.page_data) {
        return [];
      }
      return this.activePage.page_data.filter(component => component.is_published);
    }
  },
  async mounted() {
    await this.loadConference();
  },
  watch: {
    slug: {
      handler: 'loadConference',
      immediate: false
    }
  },
  methods: {
    async loadConference(): Promise<void> {
      this.loading = true;
      this.error = null;
      this.conference = null;
      this.activePage = null;
      this.activePageId = null;

      try {
        let conferenceSlug: string;

        if (this.slug) {
          // Load specific conference by slug
          const response = await apiService.get<{ payload: ConferenceWithPages }>(`/v1/public/conferences/${this.slug}`);
          this.conference = response.data.payload;
          conferenceSlug = this.slug;
        } else {
          // Load latest conference
          const latestResponse = await apiService.get<{ payload: Conference }>('/v1/public/conferences?latest=1');
          const latestConference = latestResponse.data.payload;
          if (!latestConference.slug) {
            throw new Error('Latest conference does not have a slug');
          }
          this.conference = latestConference as ConferenceWithPages;
          this.conference.pages = [];
          conferenceSlug = latestConference.slug;
        }

        // Load pages separately to ensure fresh data
        if (conferenceSlug) {
          await this.loadPages(conferenceSlug);
        }

      } catch (err: any) {
        console.error('Error loading conference:', err);
        this.error = err.response?.data?.message || err.message || 'Failed to load conference';
      } finally {
        this.loading = false;
      }
    },

    async loadPages(slug: string): Promise<void> {
      this.pagesLoading = true;
      try {
        const response = await apiService.get<{ payload: PageMenu[] }>(`/v1/public/conferences/${slug}/pages`);
        
        if (this.conference) {
          this.conference.pages = response.data.payload;
          
          // Auto-select first published page
          const firstPublishedPage = this.sortedPages[0];
          if (firstPublishedPage) {
            this.selectPage(firstPublishedPage);
          }
        }
      } catch (err: any) {
        console.error('Error loading pages:', err);
        if (!this.conference) {
          this.error = err.response?.data?.message || 'Failed to load pages';
        }
      } finally {
        this.pagesLoading = false;
      }
    },

    selectPage(page: PageMenu): void {
      this.activePage = page;
      this.activePageId = page.id;
    },

    getPublicComponent(componentType: string) {
      // Check if component is already loaded
      if (this.loadedComponents.has(componentType)) {
        return this.loadedComponents.get(componentType);
      }

      // Get component definition
      const definition = getComponentDefinition(componentType);
      if (!definition) {
        return null;
      }

      // Create async component
      const asyncComponent = defineAsyncComponent({
        loader: definition.public,
        loadingComponent: {
          template: `
            <div class="flex items-center justify-center p-1">
              <div class="flex items-center space-x-1">
                <i class="pi pi-spin pi-spinner text-primary-600"></i>
                <span class="text-gray-600">Loading component...</span>
              </div>
            </div>
          `
        },
        errorComponent: {
          template: `
            <div class="bg-red-50 border border-red-200 rounded-lg p-1">
              <div class="flex items-center space-x-1 text-red-800">
                <i class="pi pi-exclamation-triangle"></i>
                <span class="font-medium">Failed to load component</span>
              </div>
              <p class="text-red-700 text-sm mt-1">Component type: {{ componentType }}</p>
            </div>
          `,
          props: ['componentType']
        },
        delay: 200,
        timeout: 10000
      });

      // Cache the component
      this.loadedComponents.set(componentType, asyncComponent);
      return asyncComponent;
    },

    formatDate(dateStr: string): string {
      try {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      } catch {
        return dateStr;
      }
    }
  }
});
</script>