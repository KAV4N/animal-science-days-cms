<template>
  <div class="min-h-screen  ">
    <div class="max-w-7xl mx-auto">

      <!-- Loading State -->
      <div v-if="publicConferenceLoading" class="flex items-center justify-center min-h-screen">
        <Card class="w-full max-w-sm border-0 shadow-xl bg-white/80 backdrop-blur-sm">
          <template #content>
            <div class="flex flex-col items-center space-y-4">
              <ProgressSpinner class="w-10 h-10 text-primary-600" strokeWidth="3" />
              <p class="text-lg font-semibold text-slate-700">Loading conference...</p>
            </div>
          </template>
        </Card>
      </div>

      <!-- Error State -->
      <div v-else-if="publicConferenceError" class="flex items-center justify-center min-h-screen px-4">
        <Card class="w-full max-w-md border-0 shadow-xl bg-white/90 backdrop-blur-sm">
          <template #content>
            <div class="flex flex-col items-center space-y-4 p-8 text-center">
              <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                <i class="pi pi-exclamation-triangle text-2xl text-red-500"></i>
              </div>
              <div>
                <h2 class="text-xl font-bold text-slate-800 mb-2">Something went wrong</h2>
                <p class="text-slate-600">{{ publicConferenceError }}</p>
              </div>
              <Button
                label="Try Again"
                @click="loadConferenceData"
                icon="pi pi-refresh"
                class="bg-primary-600 hover:bg-primary-700 border-primary-600 px-6"
              />
            </div>
          </template>
        </Card>
      </div>

      <!-- Main Content -->
      <div v-else-if="currentPublicConference" class="min-h-screen mb-10">

        <div class="relative overflow-hidden" :style="{ background: `linear-gradient(to right, ${currentPublicConference?.primary_color || '#1E3A8A'}, ${currentPublicConference?.secondary_color || '#6B7280'})` }">
          <div class="absolute inset-0 bg-black/20"></div>
          <div class="relative max-w-7xl mx-auto px-4 py-16 lg:py-24">
            <div class="text-center text-white">
              <h1 class="text-4xl lg:text-6xl font-bold mb-6 bg-gradient-to-r from-white to-primary-100 bg-clip-text text-transparent">
                {{ currentPublicConference.title }}
              </h1>
              <div class="flex flex-col lg:flex-row items-center justify-center space-y-4 lg:space-y-0 lg:space-x-12 mb-8">
                <div class="flex items-center space-x-3 text-lg">
                  <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="pi pi-map-marker text-white"></i>
                  </div>
                  <span class="font-medium">{{ currentPublicConference.location }}</span>
                </div>
                <div class="flex items-center space-x-3 text-lg">
                  <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="pi pi-calendar text-white"></i>
                  </div>
                  <span class="font-medium">
                    {{ formatDate(currentPublicConference.start_date) }} - {{ formatDate(currentPublicConference.end_date) }}
                  </span>
                </div>
              </div>
              <p v-if="currentPublicConference.description" 
                class="text-xl text-primary-100 max-w-4xl mx-auto leading-relaxed">
                {{ currentPublicConference.description }}
              </p>
            </div>
          </div>

          <!-- Decorative Elements -->
          <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/5 rounded-full"></div>
            <div class="absolute -bottom-32 -left-32 w-80 h-80 bg-white/3 rounded-full"></div>
          </div>
        </div>

        <!-- Navigation & Content -->
        <div class="flex min-h-[calc(100vh-280px)]">

          <!-- Sidebar -->
          <div class="hidden lg:block w-80 bg-white border-r border-slate-200">
            <div class="sticky top-0 h-screen overflow-y-auto">
              <div class="p-3 border-b border-slate-100 bg-slate-50">
                <h2 class="text-sm font-medium text-slate-600 flex items-center gap-2">
                  <i class="pi pi-list text-xs text-primary-500"></i>
                  Pages
                </h2>
              </div>

              <div v-if="pagesLoading" class="flex flex-col items-center justify-center py-12">
                <ProgressSpinner class="w-8 h-8 text-primary-600 mb-3" strokeWidth="4" />
                <p class="text-sm text-slate-500">Loading pages...</p>
              </div>

              <nav v-else-if="sortedPages?.length > 0" class="p-3">
                <button
                  v-for="page in sortedPages"
                  :key="page.id"
                  @click="selectPage(page)"
                  :class="[
                    'w-full text-left p-4 mb-1 transition-all duration-200 group',
                    activePageId === page.id
                      ? 'bg-primary-50 text-primary-700 shadow-sm border-l-4 border-primary-500'
                      : 'text-slate-700 hover:bg-slate-50 hover:shadow-sm'
                  ]"
                >
                  <div class="flex items-center gap-3">
                    <i class="pi pi-file-o text-sm opacity-70 group-hover:opacity-100"></i>
                    <span class="font-medium">{{ page.title }}</span>
                  </div>
                </button>
              </nav>

              <div v-else class="flex flex-col items-center justify-center py-16">
                <i class="pi pi-inbox text-4xl text-slate-300 mb-4"></i>
                <p class="text-slate-500">No pages available</p>
              </div>
            </div>
          </div>

          <!-- Mobile Menu Button -->
          <div class="lg:hidden fixed bottom-6 right-6 z-50">
            <Button
              :icon="isMobileMenuOpen ? 'pi pi-times' : 'pi pi-bars'"
              @click="toggleMobileMenu"
              class="w-14 h-14 rounded-full bg-primary-600 hover:bg-primary-700 border-primary-600 shadow-lg"
              aria-label="Toggle Menu"
            />
          </div>

          <!-- Mobile Sidebar Overlay -->
          <div
            v-if="isMobileMenuOpen"
            class="lg:hidden fixed inset-0 z-40 bg-black/50 backdrop-blur-sm"
            @click="closeMobileMenu"
          ></div>

          <!-- Mobile Sidebar -->
          <div
            :class="[
              'lg:hidden fixed top-0 left-0 h-full w-80 max-w-[90vw] z-50 bg-white shadow-2xl transform transition-transform duration-300',
              isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
          >
            <div class="h-full overflow-y-auto">
              <div class="p-4 border-b border-slate-100 bg-gradient-to-r from-primary-600 to-purple-600">
                <div class="flex items-center justify-between text-white">
                  <h2 class="text-lg font-semibold">Navigation</h2>
                  <Button
                    icon="pi pi-times"
                    @click="closeMobileMenu"
                    text
                    class="text-white hover:bg-white/20 p-2"
                    aria-label="Close Menu"
                  />
                </div>
              </div>

              <div class="p-3">
                <h3 class="text-sm font-medium text-slate-600 mb-3 px-3 flex items-center gap-2">
                  <i class="pi pi-list text-xs text-primary-500"></i>
                  Pages
                </h3>

                <div v-if="pagesLoading" class="flex flex-col items-center justify-center py-8">
                  <ProgressSpinner class="w-8 h-8 text-primary-600 mb-3" strokeWidth="4" />
                  <p class="text-sm text-slate-500">Loading pages...</p>
                </div>

                <nav v-else-if="sortedPages?.length > 0">
                  <button
                    v-for="page in sortedPages"
                    :key="page.id"
                    @click="selectPage(page)"
                    :class="[
                      'w-full text-left p-4 mb-1 transition-all duration-200',
                      activePageId === page.id
                        ? 'bg-primary-50 text-primary-700 shadow-sm border-l-4 border-primary-500'
                        : 'text-slate-700 hover:bg-slate-50'
                    ]"
                  >
                    <div class="flex items-center gap-3">
                      <i class="pi pi-file-o text-sm opacity-70"></i>
                      <span class="font-medium">{{ page.title }}</span>
                    </div>
                  </button>
                </nav>

                <div v-else class="flex flex-col items-center justify-center py-12">
                  <i class="pi pi-inbox text-4xl text-slate-300 mb-3"></i>
                  <p class="text-slate-500 text-sm">No pages available</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Main Content -->
          <div class="flex-1 bg-slate-50">
            <div class="">

              <!-- Page Loading State -->
              <div v-if="pageDataLoading" class="flex items-center justify-center py-20">
                <div class="text-center">
                  <ProgressSpinner class="w-10 h-10 text-primary-600 mb-4" strokeWidth="3" />
                  <p class="text-lg font-medium text-slate-700">Loading page content...</p>
                </div>
              </div>


              <!-- Page Components -->
              <div v-if="activePage?.page_data?.length" class="space-y-1">
                <div
                  v-for="(pageData, index) in publishedPageData"
                  :key="pageData.id"
                  class="border-0 shadow-sm bg-white overflow-hidden"
                >

                    <div class="">
                      <!-- Dynamic Component Rendering -->
                      <component
                        :is="getPublicComponent(pageData.component_type)"
                        :data="pageData.data"
                        :component-name="pageData.tag"
                        :conference-id="currentPublicConference.id"
                        v-if="getPublicComponent(pageData.component_type)"
                      />

                      <!-- Fallback for unknown component types -->
                      <div v-else class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-8 border-2 border-dashed border-slate-300">
                        <div class="text-center">
                          <i class="pi pi-code text-3xl text-slate-400 mb-4"></i>
                          <h3 class="text-xl font-semibold text-slate-800 mb-2">Unknown Component</h3>
                          <p class="text-slate-600 mb-4">Component type "{{ pageData.component_type }}" is not recognized.</p>
                          <details class="text-left max-w-2xl mx-auto">
                            <summary class="cursor-pointer text-sm text-slate-500 hover:text-slate-700 font-medium">View Raw Data</summary>
                            <pre class="mt-3 p-4 bg-white rounded-lg border text-xs overflow-x-auto text-slate-700">{{ JSON.stringify(pageData.data, null, 2) }}</pre>
                          </details>
                        </div>
                      </div>
                    </div>
                  </div>
              </div>

              <!-- Empty Page State -->
              <div v-else-if="activePage && !activePage.page_data?.length && !pageDataLoading" class="flex items-center justify-center py-20">
                <Card class="w-full max-w-md border-0 shadow-sm bg-white">
                  <template #content>
                    <div class="text-center p-12">
                      <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="pi pi-file-o text-2xl text-slate-400"></i>
                      </div>
                      <h3 class="text-xl font-semibold text-slate-800 mb-2">No content available</h3>
                      <p class="text-slate-600">This page doesn't have any content yet.</p>
                    </div>
                  </template>
                </Card>
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
import type { Conference } from '@/types/conference';
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
    window.addEventListener('resize', this.handleResize);
  },
  beforeUnmount() {
    this.conferenceStore.clearCurrentPublicConference();
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
      immediate: true
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
      if (window.innerWidth >= 1024) {
        this.isMobileMenuOpen = false;
      }
    },

    getPageIndex(pageId: number): number {
      const index = this.sortedPages.findIndex(page => page.id === pageId);
      return index !== -1 ? index + 1 : 1;
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
      if (this.loadedComponents.has(componentType)) {
        return this.loadedComponents.get(componentType);
      }

      const definition = getComponentDefinition(componentType);
      if (!definition) {
        return null;
      }

      const asyncComponent = defineAsyncComponent({
        loader: definition.public,
        loadingComponent: {
          template: `
            <div class="flex items-center justify-center p-8">
              <div class="flex items-center space-x-3">
                <i class="pi pi-spin pi-spinner text-primary-600 text-lg"></i>
                <span class="text-slate-600 font-medium">Loading component...</span>
              </div>
            </div>
          `
        },
        errorComponent: {
          template: `
            <div class="bg-red-50 border border-red-200 rounded-xl p-6">
              <div class="flex items-center space-x-3 text-red-800 mb-2">
                <i class="pi pi-exclamation-triangle text-lg"></i>
                <span class="font-semibold">Failed to load component</span>
              </div>
              <p class="text-red-700 text-sm">Component type: {{ componentType }}</p>
            </div>
          `,
          props: ['componentType']
        },
        delay: 200,
        timeout: 10000
      });

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
