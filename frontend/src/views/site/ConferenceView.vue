<template>
  <div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-1 py-1">

      <!-- Loading State -->
      <div v-if="publicConferenceLoading" class="flex flex-col items-center justify-center min-h-[60vh] space-y-1">
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
      <div v-else-if="publicConferenceError" class="flex flex-col items-center justify-center min-h-[60vh] space-y-1">
        <Card class="w-full max-w-md text-center">
          <template #content>
            <div class="flex flex-col items-center space-y-1 p-1">
              <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                <i class="pi pi-exclamation-triangle text-2xl text-red-600"></i>
              </div>
              <h2 class="text-2xl font-bold text-gray-900">Something went wrong</h2>
              <p class="text-gray-600">{{ publicConferenceError }}</p>
              <Button
                label="Try Again"
                @click="loadConferenceData"
                class="mt-1"
                icon="pi pi-refresh"
              />
            </div>
          </template>
        </Card>
      </div>

      <!-- Main Content -->
      <div v-else-if="currentPublicConference" class="space-y-1">

        <!-- Conference Header -->
        <div class="bg-white rounded-lg shadow-sm p-2">
          <div class="px-1 py-1 text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-1">{{ currentPublicConference.title }}</h1>
            <div class="flex flex-col sm:flex-row items-center justify-center space-y-1 sm:space-y-0 sm:space-x-8 text-gray-600">
              <div class="flex items-center space-x-1">
                <i class="pi pi-map-marker text-primary"></i>
                <span>{{ currentPublicConference.location }}</span>
              </div>
              <div class="flex items-center space-x-1">
                <i class="pi pi-calendar text-primary"></i>
                <span>
                  {{ formatDate(currentPublicConference.start_date) }} - {{ formatDate(currentPublicConference.end_date) }}
                </span>
              </div>
            </div>
            <div v-if="currentPublicConference.description" class="mt-1 text-gray-700 max-w-3xl mx-auto">
              <p>{{ currentPublicConference.description }}</p>
            </div>
          </div>
        </div>

        <!-- Mobile Menu Toggle -->
        <div class="lg:hidden bg-white rounded-lg shadow-sm p-4 mb-4">
          <Button
            :icon="isMobileMenuOpen ? 'pi pi-times' : 'pi pi-bars'"
            :label="isMobileMenuOpen ? 'Close Menu' : 'Open Menu'"
            @click="toggleMobileMenu"
            class="w-full"
            outlined
          />
        </div>

        <!-- Navigation & Content Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-1">


          <!-- Sidebar Navigation -->
          <div class="lg:col-span-1">
            <div
              :class="[
                'bg-white rounded-lg shadow-sm p-1 transition-transform duration-300 ease-in-out',
                'lg:sticky lg:top-1',
                'lg:transform-none lg:translate-x-0',
                isMobileMenuOpen
                  ? 'fixed top-0 left-0 h-full w-80 max-w-[90vw] z-50 transform translate-x-0 overflow-y-auto'
                  : 'fixed top-0 left-0 h-full w-80 max-w-[90vw] z-50 transform -translate-x-full overflow-y-auto lg:relative lg:w-auto lg:h-auto'
              ]"
            >
              <!-- Mobile Menu Header -->
              <div class="lg:hidden flex items-center justify-between p-4 border-b border-gray-200 mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Navigation</h2>
                <Button
                  icon="pi pi-times"
                  @click="closeMobileMenu"
                  text
                  class="p-2"
                  aria-label="Close Menu"
                />
              </div>

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
                    'w-full text-left px-3 py-3 transition-colors duration-200 cursor-pointer block',
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

            <!-- Page Loading State -->
            <div v-if="pageDataLoading" class="bg-white rounded-lg shadow-sm">
              <div class="flex flex-col items-center justify-center space-y-1 p-1">
                <ProgressSpinner class="w-8 h-8" strokeWidth="3" />
                <p class="text-lg font-medium">Loading page content...</p>
              </div>
            </div>

            <!-- Active Page Header -->
            <div v-else-if="activePage" class="p-4 bg-white rounded-lg shadow-sm">
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
                    Welcome to {{ currentPublicConference.title }}
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
                    :component-name="pageData.tag"
                    :conference-id="currentPublicConference.id"
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
            <div v-else-if="activePage && !activePage.page_data?.length && !pageDataLoading" class="bg-white rounded-lg shadow-sm">
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
import { defineComponent, defineAsyncComponent, computed } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';
import { storeToRefs } from 'pinia';
import apiService from '@/services/apiService';
import { getComponentDefinition } from '@/utils/componentRegistry';
import type { Conference } from '@/types/conference'; // Import Conference type for direct API call
import type { PageMenu } from '@/types/pageMenu';

interface ComponentData {
  pages: PageMenu[];
  activePage: PageMenu | null;
  activePageId: number | null;
  pagesLoading: boolean;
  pageDataLoading: boolean;
  loadedComponents: Map<string, any>;
  isMobileMenuOpen: boolean;
}

export default defineComponent({
  name: 'ConferenceView',
  props: {
    slug: {
      type: String,
      required: false,
      default: ''
    },
    pageSlug: {
      type: String,
      required: false,
      default: ''
    }
  },
  setup() {
    const conferenceStore = useConferenceStore();
    const {
      currentPublicConference,
      publicConferenceLoading,
      publicConferenceError
    } = storeToRefs(conferenceStore);

    return {
      conferenceStore,
      currentPublicConference,
      publicConferenceLoading,
      publicConferenceError
    };
  },
  data(): ComponentData {
    return {
      pages: [],
      activePage: null,
      activePageId: null,
      pagesLoading: false,
      pageDataLoading: false,
      loadedComponents: new Map(),
      isMobileMenuOpen: false
    };
  },
  computed: {
    sortedPages(): PageMenu[] {
      return [...this.pages]
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
    await this.loadConferenceData();
    // Add event listener for window resize to close mobile menu
    window.addEventListener('resize', this.handleResize);
  },
  beforeUnmount() {
    this.conferenceStore.clearCurrentPublicConference();
    // Remove event listener
    window.removeEventListener('resize', this.handleResize);
  },
  watch: {
    slug: {
      async handler(newSlug?: string) {
        await this.loadConferenceData();
      },
      immediate: false
    },
    pageSlug: {
      handler: 'handlePageSlugChange',
      immediate: true // Ensure it runs on initial load and when pageSlug becomes empty
    },
    currentPublicConference: {
      async handler(newConference, oldConference) {
        if (newConference && newConference.slug && (!oldConference || newConference.slug !== oldConference.slug)) {
          await this.loadPages(newConference.slug);
        } else if (!newConference) {
          this.pages = [];
          this.activePage = null;
          this.activePageId = null;
        }
      },
    }
  },
  methods: {
    toggleMobileMenu() {
      this.isMobileMenuOpen = !this.isMobileMenuOpen;
    },

    closeMobileMenu() {
      this.isMobileMenuOpen = false;
    },

    handleResize() {
      // Close mobile menu on desktop breakpoint
      if (window.innerWidth >= 1024) {
        this.isMobileMenuOpen = false;
      }
    },

    async loadConferenceData(): Promise<void> {
      this.pages = [];
      this.activePage = null;
      this.activePageId = null;
      this.conferenceStore.clearCurrentPublicConference();

      let slugToLoad = this.slug;

      if (!slugToLoad) {
        this.conferenceStore.publicConferenceLoading = true;
        try {
          const latestResponse = await apiService.get<{ payload: Conference }>('/v1/conferences?latest=1');
          const latestConference = latestResponse.data.payload;

          if (latestConference?.slug) {
            slugToLoad = latestConference.slug;
            this.conferenceStore.currentPublicConference = latestConference;
            this.conferenceStore.publicConferenceError = null;
            this.conferenceStore.publicConferenceLoading = false;

            if (this.$route.name === 'HomePage' || (this.$route.name === 'conference' && this.$route.params.slug !== slugToLoad)) {
               this.$router.replace({ name: 'conference', params: { slug: slugToLoad } });
            }
          } else {
            throw new Error('Latest conference slug not found.');
          }
        } catch (error: any) {
          console.error('ConferenceView: Error fetching latest conference:', error);
          this.conferenceStore.publicConferenceError = error.response?.data?.message || error.message || 'Failed to fetch latest conference details.';
          this.conferenceStore.publicConferenceLoading = false;
          return;
        }
      }

      if (slugToLoad) {
        if (this.conferenceStore.currentPublicConference?.slug !== slugToLoad) {
          try {
            await this.conferenceStore.fetchPublicConferenceBySlug(slugToLoad);
          } catch (error) {
            // Error is handled in store
          }
        } else {
          if (this.conferenceStore.currentPublicConference && this.pages.length === 0) {
            await this.loadPages(this.conferenceStore.currentPublicConference.slug);
          }
        }
      } else if (!this.conferenceStore.publicConferenceError) {
          this.conferenceStore.publicConferenceError = "Could not determine which conference to load.";
      }
    },

    async loadPages(conferenceSlug: string): Promise<void> {
      if (!conferenceSlug) {
        this.pages = [];
        this.activePage = null;
        this.activePageId = null;
        return;
      }
      this.pagesLoading = true;
      this.activePage = null;
      this.activePageId = null;
      try {
        const response = await apiService.get<{ payload: PageMenu[] }>(`/v1/conferences/${conferenceSlug}/pages`);
        this.pages = response.data.payload;

        if (this.pageSlug) {
          const requestedPage = this.sortedPages.find(p => p.slug === this.pageSlug);
          if (requestedPage) {
            await this.selectPage(requestedPage, false);
          } else {
            this.redirectToDefaultPage(conferenceSlug);
          }
        } else if (this.sortedPages.length > 0) {
          this.redirectToDefaultPage(conferenceSlug);
        }
      } catch (err: any) {
        console.error(`Error loading pages for conference ${conferenceSlug}:`, err);
      } finally {
        this.pagesLoading = false;
      }
    },

    async handlePageSlugChange(): Promise<void> {
      if (this.pageSlug) {
        if (this.pages.length > 0) {
          const requestedPage = this.sortedPages.find(page => page.slug === this.pageSlug);
          if (requestedPage) {
            if (requestedPage.id !== this.activePageId) {
              await this.selectPage(requestedPage, false);
            }
          } else {
            if (this.currentPublicConference?.slug) {
              this.redirectToDefaultPage(this.currentPublicConference.slug);
            }
          }
        }
      } else {
        if (this.currentPublicConference?.slug) {
          if (this.sortedPages.length > 0) {
            this.redirectToDefaultPage(this.currentPublicConference.slug);
          } else if (!this.pagesLoading) {
            await this.loadPages(this.currentPublicConference.slug);
          }
        }
      }
    },

    redirectToDefaultPage(conferenceSlug: string) {
      const firstPage = this.sortedPages[0];
      if (firstPage) {
        this.$router.replace({
          name: 'conferencePage',
          params: { slug: conferenceSlug, pageSlug: firstPage.slug }
        });
      } else {
        if (this.$route.name !== 'conference' || this.$route.params.slug !== conferenceSlug) {
          this.$router.replace({
            name: 'conference',
            params: { slug: conferenceSlug }
          });
        }
      }
    },

    async selectPage(page: PageMenu, updateUrl: boolean = true): Promise<void> {
      if (!this.currentPublicConference?.slug) {
        console.error("Cannot select page, current conference or its slug is missing.");
        return;
      }

      this.activePageId = page.id;
      this.pageDataLoading = true;
      this.activePage = null;

      // Close mobile menu when page is selected
      this.closeMobileMenu();

      try {
        const response = await apiService.get<{ payload: PageMenu }>(
          `/v1/conferences/${this.currentPublicConference.slug}/pages/${page.slug}`
        );
        this.activePage = response.data.payload;

        if (updateUrl) {
          this.$router.push({
            name: 'conferencePage',
            params: {
              slug: this.currentPublicConference.slug,
              pageSlug: page.slug
            }
          });
        }
      } catch (err: any) {
        console.error(`Error loading page data for ${page.slug}:`, err);
        this.activePage = page;
      } finally {
        this.pageDataLoading = false;
      }
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
