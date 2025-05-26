<template>
  <div class="min-h-screen p-2">
    <div class="max-w-7xl mx-auto">

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center min-h-[60vh] space-y-6">
        <Card class="w-full max-w-md mx-auto">
          <template #content>
            <div class="flex flex-col items-center space-y-4 p-4">
              <ProgressSpinner class="w-12 h-12" strokeWidth="3" />
              <p class="text-lg font-medium">Loading conference...</p>
            </div>
          </template>
        </Card>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="flex flex-col items-center justify-center min-h-[60vh] space-y-6">
        <Card class="w-full max-w-md text-center">
          <template #content>
            <div class="flex flex-col items-center space-y-4 p-4">
              <div class="w-16 h-16 rounded-full flex items-center justify-center">
                <i class="pi pi-exclamation-triangle text-2xl"></i>
              </div>
              <h2 class="text-2xl font-bold">Something went wrong</h2>
              <p>{{ error }}</p>
              <Button 
                label="Try Again" 
                @click="loadConference" 
                class="mt-2"
                icon="pi pi-refresh"
              />
            </div>
          </template>
        </Card>
      </div>

      <!-- Main Layout -->
      <div v-else-if="conference" class="space-y-2">
        
        <!-- Conference Header Card -->
        <Card class="w-full">
          <template #content>
            <div class="p-6">
              <h1 class="text-3xl font-bold mb-4 text-center">{{ conference.title }}</h1>
              <div class="flex flex-col sm:flex-row items-center justify-center space-y-2 sm:space-y-0 sm:space-x-8">
                <div class="flex items-center space-x-2">
                  <i class="pi pi-map-marker"></i>
                  <span>{{ conference.location }}</span>
                </div>
                <div class="flex items-center space-x-2">
                  <i class="pi pi-calendar"></i>
                  <span>
                    {{ formatDate(conference.start_date) }} - {{ formatDate(conference.end_date) }}
                  </span>
                </div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-2">
          
          <!-- Sidebar -->
          <div class="lg:col-span-1 space-y-2">
            <Card class="h-fit">
              <template #header>
                <div class="p-4 pb-2">
                  <h2 class="text-lg font-semibold flex items-center space-x-2">
                    <i class="pi pi-list"></i>
                    <span>Pages</span>
                  </h2>
                </div>
              </template>
              <template #content>
                <div class="p-4 pt-0">
                  <div v-if="pagesLoading" class="flex flex-col items-center space-y-3 py-8">
                    <ProgressSpinner class="w-6 h-6" strokeWidth="4" />
                    <p class="text-sm">Loading pages...</p>
                  </div>
                  <div v-else-if="conference.pages && conference.pages.length > 0" class="space-y-1">
                    <Button
                      v-for="page in conference.pages" 
                      :key="page.id"
                      @click="selectPage(page)"
                      :label="page.title"
                      :outlined="activePageId !== page.id"
                      :severity="activePageId === page.id ? 'primary' : 'secondary'"
                      class="w-full justify-start"
                      size="small"
                    >
                      <template #icon>
                        <i class="pi pi-file mr-2"></i>
                      </template>
                    </Button>
                  </div>
                  <div v-else class="text-center py-8">
                    <div class="flex flex-col items-center space-y-3">
                      <i class="pi pi-inbox text-3xl opacity-50"></i>
                      <p class="text-sm opacity-75">No pages available</p>
                    </div>
                  </div>
                </div>
              </template>
            </Card>
          </div>

          <!-- Page Content -->
          <div class="lg:col-span-3 space-y-2">

            <Card v-if="activePage" class="w-full">
              <template #content>
                <div class="p-6">
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <h2 class="text-2xl font-bold mb-2">{{ activePage.title }}</h2>
                      <div class="flex items-center space-x-4 text-sm opacity-75">
                        <span class="flex items-center space-x-1">
                          <i class="pi pi-clock"></i>
                          <span>Last updated {{ formatDate(activePage.updated_at || activePage.created_at) }}</span>
                        </span>
                      </div>
                    </div>
                    <Chip :label="`Page ${activePage.order || 1}`" />
                  </div>
                </div>
              </template>
            </Card>

            <Card v-else class="w-full">
              <template #content>
                <div class="text-center space-y-6 p-12">
                  <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto opacity-50">
                    <i class="pi pi-home text-3xl"></i>
                  </div>
                  <div>
                    <h2 class="text-2xl font-bold mb-2">
                      Welcome to {{ conference.title }}
                    </h2>
                    <p v-if="pagesLoading" class="opacity-75">Loading pages...</p>
                    <p v-else-if="conference.pages?.length" class="opacity-75">
                      Select a page from the sidebar to view its content.
                    </p>
                    <p v-else class="opacity-75">No pages are available for this conference yet.</p>
                  </div>
                  <div v-if="conference.pages?.length" class="flex flex-wrap gap-2 justify-center">
                    <Button 
                      v-for="page in conference.pages.slice(0, 3)" 
                      :key="page.id"
                      :label="page.title"
                      @click="selectPage(page)"
                      outlined
                      size="small"
                    />
                  </div>
                </div>
              </template>
            </Card>

            <template v-if="activePage?.page_data?.length">
              <Card 
                v-for="pageData in activePage.page_data" 
                :key="pageData.id"
                class="w-full"
              >
                <template #content>
                  <div class="p-4 pt-0">
                   <div 
                      v-if="pageData.component_type === 'Editor' && pageData.data?.content"
                      class="editor-content"
                      v-html="pageData.data.content"
                    ></div>

                    <div v-else-if="pageData.component_type === 'Text'" class="prose prose-lg max-w-none">
                      <p class="leading-relaxed">
                        {{ pageData.data?.text || 'No text content available' }}
                      </p>
                    </div>

                    <div v-else>
                      <div class="rounded-lg p-4 border">
                        <pre class="text-sm overflow-x-auto whitespace-pre-wrap opacity-75">
                          {{ JSON.stringify(pageData.data, null, 2) }}
                        </pre>
                      </div>
                    </div>
                  </div>
                </template>
              </Card>
            </template>

            <Card v-else-if="activePage" class="w-full">
              <template #content>
                <div class="text-center space-y-4 p-12">
                  <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto opacity-50">
                    <i class="pi pi-file-o text-2xl"></i>
                  </div>
                  <h3 class="text-xl font-semibold">No content available</h3>
                  <p class="opacity-75">This page doesn't have any content yet.</p>
                </div>
              </template>
            </Card>

          </div>
        </div>
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

        // Always load pages separately to ensure we get the most up-to-date data
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
        console.log('Loading pages for slug:', slug);
        const response = await apiService.get<{ payload: PageMenu[] }>(`/v1/public/conferences/${slug}/pages`);
        console.log('Pages response:', response.data);
        
        if (this.conference) {
          this.conference.pages = response.data.payload;
          console.log('Conference pages set to:', this.conference.pages);
          
          if (this.conference.pages && this.conference.pages.length > 0) {
            const sortedPages = [...this.conference.pages].sort((a, b) => (a.order || 0) - (b.order || 0));
            console.log('Sorted pages:', sortedPages);
            this.selectPage(sortedPages[0]);
          }
        }
      } catch (err: any) {
        console.error('Error loading pages:', err);
        // Only set error if we don't have a conference loaded
        if (!this.conference) {
          this.error = err.response?.data?.message || 'Failed to load pages';
        }
      } finally {
        this.pagesLoading = false;
      }
    },

    selectPage(page: PageMenu): void {
      console.log('Selecting page:', page);
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