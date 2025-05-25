<template>
  <div class="min-h-screen flex items-start justify-center p-4">
    <div class="w-full max-w-7xl">
      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center space-y-4">
        <p-progress-spinner style="width: 50px; height: 50px" />
        <p>Loading conference...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="flex flex-col items-center space-y-4">
        <h2 class="text-2xl font-bold">Error</h2>
        <p>{{ error }}</p>
        <p-button label="Retry" @click="loadConference" severity="primary" />
      </div>

      <!-- Main Content -->
      <div v-else-if="conference" class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
        <!-- Sidebar -->
        <aside class="w-full md:w-64 rounded-lg">
          <div class="p-4">
            <h2 class="text-lg font-semibold">{{ conference.title }}</h2>
            <div class="mt-2 space-y-1">
              <p class="text-sm">📍 {{ conference.location }}</p>
              <p class="text-sm">
                📅 {{ formatDate(conference.start_date) }} - {{ formatDate(conference.end_date) }}
              </p>
            </div>
          </div>
          <nav class="p-4">
            <div v-if="pagesLoading" class="flex flex-col items-center space-y-2">
              <p-progress-spinner style="width: 24px; height: 24px" />
              <p class="text-sm">Loading pages...</p>
            </div>
            <ul v-else-if="conference.pages && conference.pages.length > 0" class="space-y-2">
              <li 
                v-for="page in conference.pages" 
                :key="page.id"
                class="w-full"
              >
                <button 
                  @click="selectPage(page)"
                  class="w-full text-left px-4 py-2 rounded-md transition-colors"
                  :class="{ 'text-blue-600': activePageId === page.id }"
                >
                  {{ page.title }}
                </button>
              </li>
            </ul>
            <div v-else class="text-center">
              <p>No pages available for this conference.</p>
            </div>
          </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 rounded-lg p-4">
          <div v-if="activePage" class="space-y-6">
            <header>
              <h1 class="text-3xl font-bold">{{ activePage.title }}</h1>
            </header>
            <div class="space-y-4">
              <div 
                v-for="pageData in activePage.page_data || []" 
                :key="pageData.id"
                class="p-4 rounded-lg"
              >
                <div 
                  v-if="pageData.component_type === 'Editor' && pageData.data?.content"
                  class="editor-content"
                  v-html="pageData.data.content"
                ></div>
                <div v-else-if="pageData.component_type === 'Text'" class="text-content">
                  <p class="leading-relaxed">{{ pageData.data?.text || 'No text content available' }}</p>
                </div>
                <div v-else class="p-4 rounded-lg">
                  <h4 class="text-lg font-semibold mb-2">{{ pageData.component_type }}</h4>
                  <pre class="p-3 rounded text-sm overflow-x-auto">{{ JSON.stringify(pageData.data, null, 2) }}</pre>
                </div>
              </div>
              <div v-if="!activePage.page_data || activePage.page_data.length === 0" class="text-center py-8">
                <p class="text-lg">No content available for this page.</p>
              </div>
            </div>
          </div>
          <div v-else class="text-center space-y-4 py-8">
            <h2 class="text-2xl font-bold">Welcome to {{ conference.title }}</h2>
            <p v-if="pagesLoading">Loading pages...</p>
            <p v-else-if="conference.pages && conference.pages.length > 0">Select a page from the menu to view its content.</p>
            <p v-else>No pages are available for this conference.</p>
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

