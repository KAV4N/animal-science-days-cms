<template>
  <div class="min-h-screen bg-gradient-to-br from-surface-50 to-surface-100 dark:from-surface-950 dark:to-surface-900">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center min-h-[60vh] space-y-6">
        <div class="bg-surface-0 dark:bg-surface-900 rounded-xl p-8 shadow-lg border border-surface-200 dark:border-surface-700">
          <div class="flex flex-col items-center space-y-4">
            <p-progress-spinner class="w-12 h-12" strokeWidth="3" />
            <p class="text-lg font-medium text-surface-700 dark:text-surface-200">Loading conference...</p>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="flex flex-col items-center justify-center min-h-[60vh] space-y-6">
        <div class="bg-surface-0 dark:bg-surface-900 rounded-xl p-8 shadow-lg border border-red-200 dark:border-red-800 max-w-md w-full text-center">
          <div class="flex flex-col items-center space-y-4">
            <div class="w-16 h-16 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
              <i class="pi pi-exclamation-triangle text-red-600 dark:text-red-400 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-surface-900 dark:text-surface-50">Something went wrong</h2>
            <p class="text-surface-600 dark:text-surface-400">{{ error }}</p>
            <p-button 
              label="Try Again" 
              @click="loadConference" 
              severity="danger"
              class="mt-4"
              icon="pi pi-refresh"
            />
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div v-else-if="conference" class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar -->
        <aside class="lg:col-span-1">
          <div class="sticky top-6">
            <!-- Conference Header Card -->
            <div class="bg-surface-0 dark:bg-surface-900 rounded-xl shadow-lg border border-surface-200 dark:border-surface-700 overflow-hidden mb-6">
              <div class="bg-gradient-to-r from-primary-500 to-primary-600 p-6 text-white">
                <h2 class="text-xl font-bold mb-3">{{ conference.title }}</h2>
                <div class="space-y-2 text-primary-50">
                  <div class="flex items-center space-x-2">
                    <i class="pi pi-map-marker text-sm"></i>
                    <span class="text-sm">{{ conference.location }}</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <i class="pi pi-calendar text-sm"></i>
                    <span class="text-sm">
                      {{ formatDate(conference.start_date) }} - {{ formatDate(conference.end_date) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Navigation Card -->
            <div class="bg-surface-0 dark:bg-surface-900 rounded-xl shadow-lg border border-surface-200 dark:border-surface-700 overflow-hidden">
              <div class="p-4 border-b border-surface-200 dark:border-surface-700">
                <h3 class="font-semibold text-surface-900 dark:text-surface-50 flex items-center space-x-2">
                  <i class="pi pi-list text-primary-500"></i>
                  <span>Pages</span>
                </h3>
              </div>
              
              <nav class="p-2">
                <div v-if="pagesLoading" class="flex flex-col items-center space-y-3 py-8">
                  <p-progress-spinner class="w-6 h-6" strokeWidth="4" />
                  <p class="text-sm text-surface-600 dark:text-surface-400">Loading pages...</p>
                </div>
                
                <ul v-else-if="conference.pages && conference.pages.length > 0" class="space-y-1">
                  <li v-for="page in conference.pages" :key="page.id">
                    <button 
                      @click="selectPage(page)"
                      class="w-full text-left px-3 py-3 rounded-lg transition-all duration-200 flex items-center space-x-3 group"
                      :class="activePageId === page.id 
                        ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 border-l-4 border-primary-500' 
                        : 'hover:bg-surface-100 dark:hover:bg-surface-800 text-surface-700 dark:text-surface-300'"
                    >
                      <i class="pi pi-file text-sm transition-colors"
                         :class="activePageId === page.id ? 'text-primary-500' : 'text-surface-400 group-hover:text-surface-600'"></i>
                      <span class="font-medium">{{ page.title }}</span>
                    </button>
                  </li>
                </ul>
                
                <div v-else class="text-center py-8">
                  <div class="flex flex-col items-center space-y-3">
                    <i class="pi pi-inbox text-3xl text-surface-400"></i>
                    <p class="text-surface-600 dark:text-surface-400">No pages available</p>
                  </div>
                </div>
              </nav>
            </div>
          </div>
        </aside>

        <!-- Main Content Area -->
        <main class="lg:col-span-3">
          <div v-if="activePage" class="space-y-6">
            <!-- Page Header -->
            <div class="bg-surface-0 dark:bg-surface-900 rounded-xl shadow-lg border border-surface-200 dark:border-surface-700 p-6">
              <div class="flex items-start justify-between">
                <div>
                  <h1 class="text-3xl font-bold text-surface-900 dark:text-surface-50 mb-2">{{ activePage.title }}</h1>
                  <div class="flex items-center space-x-4 text-sm text-surface-600 dark:text-surface-400">
                    <span class="flex items-center space-x-1">
                      <i class="pi pi-clock"></i>
                      <span>Last updated {{ formatDate(activePage.updated_at || activePage.created_at) }}</span>
                    </span>
                  </div>
                </div>
                <p-chip 
                  :label="`Page ${activePage.order || 1}`" 
                  class="bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300"
                />
              </div>
            </div>

            <!-- Page Content -->
            <div class="space-y-6">
              <div 
                v-for="(pageData, index) in activePage.page_data || []" 
                :key="pageData.id"
                class="bg-surface-0 dark:bg-surface-900 rounded-xl shadow-lg border border-surface-200 dark:border-surface-700 overflow-hidden"
              >
                <!-- Editor Content -->
                <div 
                  v-if="pageData.component_type === 'Editor' && pageData.data?.content"
                  class="p-6"
                >
                  <div class="prose prose-lg max-w-none dark:prose-invert prose-headings:text-surface-900 dark:prose-headings:text-surface-50 prose-p:text-surface-700 dark:prose-p:text-surface-300 prose-a:text-primary-600 dark:prose-a:text-primary-400 prose-strong:text-surface-900 dark:prose-strong:text-surface-100">
                    <div v-html="pageData.data.content"></div>
                  </div>
                </div>
                
                <!-- Text Content -->
                <div v-else-if="pageData.component_type === 'Text'" class="p-6">
                  <div class="prose prose-lg max-w-none dark:prose-invert">
                    <p class="text-surface-700 dark:text-surface-300 leading-relaxed">
                      {{ pageData.data?.text || 'No text content available' }}
                    </p>
                  </div>
                </div>
                
                <!-- Other Component Types -->
                <div v-else class="p-6">
                  <div class="mb-4 pb-4 border-b border-surface-200 dark:border-surface-700">
                    <div class="flex items-center space-x-2">
                      <p-chip :label="pageData.component_type" severity="info" />
                      <span class="text-sm text-surface-600 dark:text-surface-400">Component</span>
                    </div>
                  </div>
                  <div class="bg-surface-50 dark:bg-surface-800 rounded-lg p-4">
                    <pre class="text-sm text-surface-700 dark:text-surface-300 overflow-x-auto whitespace-pre-wrap">{{ JSON.stringify(pageData.data, null, 2) }}</pre>
                  </div>
                </div>
              </div>
              
              <!-- Empty State for Page Content -->
              <div v-if="!activePage.page_data || activePage.page_data.length === 0" 
                   class="bg-surface-0 dark:bg-surface-900 rounded-xl shadow-lg border border-surface-200 dark:border-surface-700 p-12">
                <div class="text-center space-y-4">
                  <div class="w-16 h-16 bg-surface-100 dark:bg-surface-800 rounded-full flex items-center justify-center mx-auto">
                    <i class="pi pi-file-o text-2xl text-surface-400"></i>
                  </div>
                  <h3 class="text-xl font-semibold text-surface-900 dark:text-surface-50">No content available</h3>
                  <p class="text-surface-600 dark:text-surface-400">This page doesn't have any content yet.</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Welcome State -->
          <div v-else class="bg-surface-0 dark:bg-surface-900 rounded-xl shadow-lg border border-surface-200 dark:border-surface-700 p-12">
            <div class="text-center space-y-6">
              <div class="w-20 h-20 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto">
                <i class="pi pi-home text-3xl text-primary-600 dark:text-primary-400"></i>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-surface-50 mb-2">
                  Welcome to {{ conference.title }}
                </h2>
                <p class="text-surface-600 dark:text-surface-400" v-if="pagesLoading">
                  Loading pages...
                </p>
                <p class="text-surface-600 dark:text-surface-400" v-else-if="conference.pages && conference.pages.length > 0">
                  Select a page from the sidebar to view its content.
                </p>
                <p class="text-surface-600 dark:text-surface-400" v-else>
                  No pages are available for this conference yet.
                </p>
              </div>
              
              <div v-if="conference.pages && conference.pages.length > 0" class="flex flex-wrap gap-2 justify-center">
                <p-button 
                  v-for="page in conference.pages.slice(0, 3)" 
                  :key="page.id"
                  :label="page.title"
                  @click="selectPage(page)"
                  severity="secondary"
                  outlined
                  size="small"
                />
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import apiService from '@/services/apiService';
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
      error: null
    };
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
        if (this.slug) {
          const response = await apiService.get<{ payload: ConferenceWithPages }>(`/v1/public/conferences/${this.slug}`);
          this.conference = response.data.payload;
          if (this.conference?.pages && this.conference.pages.length > 0) {
            const sortedPages = [...this.conference.pages].sort((a, b) => (a.order || 0) - (b.order || 0));
            this.selectPage(sortedPages[0]);
          }
        } else {
          const latestResponse = await apiService.get<{ payload: Conference }>('/v1/public/conferences?latest=1');
          const latestConference = latestResponse.data.payload;
          if (!latestConference.slug) {
            throw new Error('Latest conference does not have a slug');
          }
          this.conference = latestConference as ConferenceWithPages;
          this.conference.pages = [];
          await this.loadPages(latestConference.slug);
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
          if (this.conference.pages.length > 0) {
            const sortedPages = [...this.conference.pages].sort((a, b) => (a.order || 0) - (b.order || 0));
            this.selectPage(sortedPages[0]);
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

<style scoped>

.editor-content :deep(h1) {
  font-size: 2rem;
  font-weight: 700;
  margin-top: 1.5rem;
  margin-bottom: 1rem;
  line-height: 1.2;
}

.editor-content :deep(h2) {
  font-size: 1.75rem;
  font-weight: 600;
  margin-top: 1.25rem;
  margin-bottom: 0.75rem;
  line-height: 1.3;
}

.editor-content :deep(h3) {
  font-size: 1.5rem;
  font-weight: 600;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
  line-height: 1.4;
}

.editor-content :deep(h4) {
  font-size: 1.25rem;
  font-weight: 600;
  margin-top: 0.75rem;
  margin-bottom: 0.5rem;
  line-height: 1.4;
}

.editor-content :deep(h5) {
  font-size: 1.125rem;
  font-weight: 600;
  margin-top: 0.75rem;
  margin-bottom: 0.25rem;
  line-height: 1.4;
}

.editor-content :deep(h6) {
  font-size: 1rem;
  font-weight: 600;
  margin-top: 0.5rem;
  margin-bottom: 0.25rem;
  line-height: 1.4;
}

.editor-content :deep(p) {
  line-height: 1.7;
  margin-bottom: 1rem;
}

.editor-content :deep(a) {
  text-decoration: underline;
}

.editor-content :deep(strong) {
  font-weight: 600;
}

.editor-content :deep(em) {
  font-style: italic;
}

.editor-content :deep(ul) {
  margin-bottom: 1rem;
  padding-left: 1.5rem;
  list-style-type: disc;
}

.editor-content :deep(ol) {
  margin-bottom: 1rem;
  padding-left: 1.5rem;
  list-style-type: decimal;
}

.editor-content :deep(li) {
  margin-bottom: 0.25rem;
  line-height: 1.6;
}

.editor-content :deep(blockquote) {
  border-left: 4px solid #3b82f6;
  padding-left: 1rem;
  font-style: italic;
  color: #6b7280;
  margin: 1rem 0;
}



.editor-content :deep(pre) {
  padding: 1rem;
  border-radius: 0.5rem;
  overflow-x: auto;
  margin: 1rem 0;
}

.editor-content :deep(pre code) {
  background-color: transparent;
  padding: 0;
}

.editor-content :deep(table) {
  border-collapse: collapse;
  width: 100%;
  margin-bottom: 1rem;
}

.editor-content :deep(th),
.editor-content :deep(td) {
  padding: 0.75rem 1rem;
  text-align: left;
}

.editor-content :deep(th) {
  font-weight: 600;
}

.editor-content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  margin: 1rem 0;
}

.editor-content :deep(hr) {
  border: none;
  margin: 2rem 0;
}

.editor-content :deep(> *:first-child) {
  margin-top: 0;
}

.editor-content :deep(> *:last-child) {
  margin-bottom: 0;
}

.editor-content :deep(table) {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1rem;
  border: 1px ;
}

.editor-content :deep(th),
.editor-content :deep(td) {
  border: 1px solid #ccc;
  padding: 0.75rem 1rem;
  text-align: left;
  vertical-align: top;
  font-size: 1rem;
  line-height: 1.5;
}

.editor-content :deep(th) {
  font-weight: bold;
}



.editor-content :deep(caption) {
  caption-side: bottom;
  text-align: center;
  font-size: 0.875rem;
  color: #666;
  margin-top: 0.5rem;
}
</style>