<template>
  <div class="min-h-screen bg-gray-100 flex items-start justify-center p-4">
    <div class="w-full max-w-7xl">
      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center space-y-4">
        <p-progress-spinner style="width: 50px; height: 50px" />
        <p class="text-gray-600">Loading conference...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="flex flex-col items-center space-y-4">
        <h2 class="text-2xl font-bold text-red-600">Error</h2>
        <p class="text-gray-700">{{ error }}</p>
        <p-button label="Retry" @click="loadConference" severity="primary" />
      </div>

      <!-- Main Content -->
      <div v-else-if="conference" class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
        <!-- Sidebar -->
        <aside class="w-full md:w-64 bg-white shadow-md rounded-lg">
          <div class="p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">{{ conference.title }}</h2>
            <div class="mt-2 space-y-1">
              <p class="text-sm text-gray-600">📍 {{ conference.location }}</p>
              <p class="text-sm text-gray-600">
                📅 {{ formatDate(conference.start_date) }} - {{ formatDate(conference.end_date) }}
              </p>
            </div>
          </div>
          <nav class="p-4">
            <div v-if="pagesLoading" class="flex flex-col items-center space-y-2">
              <p-progress-spinner style="width: 24px; height: 24px" />
              <p class="text-sm text-gray-500">Loading pages...</p>
            </div>
            <ul v-else-if="conference.pages && conference.pages.length > 0" class="space-y-2">
              <li 
                v-for="page in conference.pages" 
                :key="page.id"
                class="w-full"
              >
                <button 
                  @click="selectPage(page)"
                  class="w-full text-left px-4 py-2 rounded-md hover:bg-gray-100 transition-colors"
                  :class="{ 'bg-gray-100 text-blue-600': activePageId === page.id }"
                >
                  {{ page.title }}
                </button>
              </li>
            </ul>
            <div v-else class="text-center text-gray-500">
              <p>No pages available for this conference.</p>
            </div>
          </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 bg-white shadow-md rounded-lg p-4">
          <div v-if="activePage" class="space-y-6">
            <header>
              <h1 class="text-3xl font-bold text-blue-600">{{ activePage.title }}</h1>
            </header>
            <div class="space-y-4">
              <div 
                v-for="pageData in activePage.page_data || []" 
                :key="pageData.id"
                class="bg-white p-4 rounded-lg shadow"
              >
                <div 
                  v-if="pageData.component_type === 'Editor' && pageData.data?.content"
                  class="prose"
                  v-html="pageData.data.content"
                ></div>
                <div v-else-if="pageData.component_type === 'Text'">
                  <p>{{ pageData.data?.text || 'No text content available' }}</p>
                </div>
                <div v-else>
                  <h4>{{ pageData.component_type }}</h4>
                  <pre class="bg-gray-100 p-2 rounded">{{ JSON.stringify(pageData.data, null, 2) }}</pre>
                </div>
              </div>
              <div v-if="!activePage.page_data || activePage.page_data.length === 0" class="text-center text-gray-500">
                <p>No content available for this page.</p>
              </div>
            </div>
          </div>
          <div v-else class="text-center space-y-4">
            <h2 class="text-2xl font-bold text-gray-800">Welcome to {{ conference.title }}</h2>
            <p v-if="pagesLoading" class="text-gray-600">Loading pages...</p>
            <p v-else-if="conference.pages && conference.pages.length > 0" class="text-gray-600">Select a page from the menu to view its content.</p>
            <p v-else class="text-gray-600">No pages are available for this conference.</p>
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