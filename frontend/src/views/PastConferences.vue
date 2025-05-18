<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { usePastConferencesStore } from '@/stores/pastConferencesStore';
import { useRouter, useRoute } from 'vue-router';

import Divider from 'primevue/divider';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Skeleton from 'primevue/skeleton';
import Tag from 'primevue/tag';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';

const pastConferencesStore = usePastConferencesStore();
const router = useRouter();
const route = useRoute();

// State
const activeDecadeFilter = ref<string | null>(null);
const activeUniversityFilter = ref<string | null>(null);
const searchQuery = ref('');

onMounted(async () => {
  // Load conferences
  if (pastConferencesStore.conferences.length === 0) {
    await pastConferencesStore.fetchConferences();
  }
  
  // Check for URL query parameters
  const decade = route.query.decade as string;
  const university = route.query.university as string;
  const search = route.query.search as string;
  
  if (decade) {
    activeDecadeFilter.value = decade;
    pastConferencesStore.setDecadeFilter(decade);
  }
  
  if (university) {
    activeUniversityFilter.value = university;
    pastConferencesStore.setUniversityFilter(university);
  }
  
  if (search) {
    searchQuery.value = search;
    pastConferencesStore.setSearchFilter(search);
  }
});

// Computed prop.
const decades = computed(() => {
  return pastConferencesStore.decades.map(decade => ({
    label: decade,
    value: decade
  }));
});

const universities = computed(() => {
  return pastConferencesStore.availableUniversities.map(uni => ({
    label: uni,
    value: uni
  }));
});

const conferencesByDecade = computed(() => {
  return pastConferencesStore.conferencesByDecade;
});

const filteredConferences = computed(() => {
  return pastConferencesStore.filteredConferences;
});

const isLoading = computed(() => {
  return pastConferencesStore.loading;
});

const hasActiveFilters = computed(() => {
  return activeDecadeFilter.value || activeUniversityFilter.value || searchQuery.value;
});

function handleSearch() {
  pastConferencesStore.setSearchFilter(searchQuery.value);
  
  // Update URL
  updateQueryParams();
}

function handleDecadeChange(decade: string | null) {
  activeDecadeFilter.value = decade;
  pastConferencesStore.setDecadeFilter(decade);
  
  updateQueryParams();
}

function handleUniversityChange(university: string | null) {
  activeUniversityFilter.value = university;
  pastConferencesStore.setUniversityFilter(university);
  
  updateQueryParams();
}

function resetFilters() {
  searchQuery.value = '';
  activeDecadeFilter.value = null;
  activeUniversityFilter.value = null;
  pastConferencesStore.resetFilters();
  
  updateQueryParams();
}

function updateQueryParams() {
  const query: Record<string, string> = {};
  
  if (searchQuery.value) {
    query.search = searchQuery.value;
  }
  
  if (activeDecadeFilter.value) {
    query.decade = activeDecadeFilter.value;
  }
  
  if (activeUniversityFilter.value) {
    query.university = activeUniversityFilter.value;
  }
  
  router.replace({ query });
}

function goToConferenceDetail(slug: string) {
  router.push(`/conference/${slug}`);
}

function formatDateRange(startDate: Date | null, endDate: Date | null) {
  if (!startDate) return '';
  
  const start = new Date(startDate);
  const end = endDate ? new Date(endDate) : null;
  
  const options: Intl.DateTimeFormatOptions = {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  };
  
  if (end) {
    if (start.getFullYear() === end.getFullYear() &&
        start.getMonth() === end.getMonth()) {
      // Same month and year: "Jan 10-15, 2023"
      return `${start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { day: 'numeric', year: 'numeric' })}`;
    } else if (start.getFullYear() === end.getFullYear()) {
      // Same year: "Jan 10 - Feb 15, 2023"
      return `${start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
    } else {
      // Different years: "Jan 10, 2022 - Feb 15, 2023"
      return `${start.toLocaleDateString('en-US', options)} - ${end.toLocaleDateString('en-US', options)}`;
    }
  }
  
  // Only start date
  return start.toLocaleDateString('en-US', options);
}
</script>

<template>
  <div class="past-conferences-page pt-24 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="page-header text-center mb-12">
        <h1 class="text-4xl font-bold mb-4">Past Conferences</h1>
        <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
          Browse our archive of past academic conferences organized throughout the years.
        </p>
      </div>
      
      <!-- Filters Section -->
      <div class="filters-section mb-10 p-6 bg-surface-50 dark:bg-surface-800 rounded-xl shadow-sm">
        <h2 class="text-xl font-semibold mb-4">Find Past Conferences</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search Input -->
          <div class="search-field">
            <span class="p-input-icon-left w-full">
              <i class="pi pi-search" />
              <InputText 
                v-model="searchQuery" 
                placeholder="Search conferences..." 
                class="w-full"
                @keyup.enter="handleSearch"
              />
            </span>
          </div>
          
          <!-- Decade Filter -->
          <div class="decade-filter">
            <Dropdown
              v-model="activeDecadeFilter"
              :options="decades"
              optionLabel="label"
              optionValue="value"
              placeholder="Filter by Decade"
              class="w-full"
              @change="handleDecadeChange($event.value)"
              :showClear="true"
            />
          </div>
          
          <!-- University Filter -->
          <div class="university-filter">
            <Dropdown
              v-model="activeUniversityFilter"
              :options="universities"
              optionLabel="label"
              optionValue="value"
              placeholder="Filter by University"
              class="w-full"
              @change="handleUniversityChange($event.value)"
              :showClear="true"
            />
          </div>
        </div>
        
        <!-- Active Filters -->
        <div class="active-filters mt-4 flex items-center" v-if="hasActiveFilters">
          <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Active filters:</span>
          <div class="flex flex-wrap gap-2">
            <Tag v-if="searchQuery" severity="info" class="flex items-center">
              Search: {{ searchQuery }}
            </Tag>
            <Tag v-if="activeDecadeFilter" severity="info" class="flex items-center">
              Decade: {{ activeDecadeFilter }}
            </Tag>
            <Tag v-if="activeUniversityFilter" severity="info" class="flex items-center">
              University: {{ activeUniversityFilter }}
            </Tag>
            <Button 
              icon="pi pi-times" 
              label="Clear Filters" 
              text 
              size="small" 
              @click="resetFilters"
              class="!p-0 !ml-2"
            />
          </div>
        </div>
      </div>
      
      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 6" :key="i" class="conference-skeleton">
            <Skeleton height="220px" class="mb-2" />
            <Skeleton width="70%" height="24px" class="mb-2" />
            <Skeleton width="40%" height="20px" class="mb-2" />
            <Skeleton width="85%" height="16px" class="mb-1" />
            <Skeleton width="90%" height="16px" />
          </div>
        </div>
      </div>
      
      <!-- Filtered Results -->
      <div v-else-if="hasActiveFilters" class="filtered-results">
        <div class="mb-6 flex justify-between items-center">
          <h2 class="text-xl font-semibold">
            Search Results 
            <span class="text-base font-normal text-gray-500 dark:text-gray-400">
              ({{ filteredConferences.length }} conferences found)
            </span>
          </h2>
        </div>
        
        <div v-if="filteredConferences.length === 0" class="no-results text-center py-12">
          <i class="pi pi-search text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
          <h3 class="text-xl font-medium mb-2">No conferences found</h3>
          <p class="text-gray-500 dark:text-gray-400">
            Try adjusting your search criteria or clearing filters
          </p>
          <Button label="Clear Filters" icon="pi pi-filter-slash" text @click="resetFilters" class="mt-4" />
        </div>
        
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <Card 
            v-for="conference in filteredConferences" 
            :key="conference.id" 
            class="conference-card"
            @click="goToConferenceDetail(conference.slug)"
          >
            <template #header>
              <div 
                class="conference-card-header h-32 w-full flex items-end p-4" 
                :style="{
                  background: `linear-gradient(to right, ${conference.primaryColor}, ${conference.secondaryColor})`
                }"
              >
                <span class="text-white font-bold bg-black/30 px-2 py-1 rounded text-sm">
                  {{ new Date(conference.startDate).getFullYear() }}
                </span>
              </div>
            </template>
            
            <template #title>
              <div class="truncate">{{ conference.name }}</div>
            </template>
            
            <template #subtitle>
              <div class="flex items-center gap-2">
                <i class="pi pi-map-marker text-sm"></i>
                <span>{{ conference.location }}</span>
              </div>
            </template>
            
            <template #content>
              <div class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                <i class="pi pi-calendar mr-2"></i>
                {{ formatDateRange(conference.startDate, conference.endDate) }}
              </div>
              
              <p class="line-clamp-2 text-sm mb-4">
                {{ conference.description }}
              </p>
              
              <div>
                <Tag severity="secondary" class="text-xs">
                  {{ conference.university ? conference.university.name : 'Unknown University' }}
                </Tag>
              </div>
            </template>
            
            <template #footer>
              <Button 
                label="View Details" 
                icon="pi pi-arrow-right" 
                text 
                class="p-button-sm w-full justify-center"
              />
            </template>
          </Card>
        </div>
      </div>
      
      <!-- Browse by Decade (default) -->
      <div v-else class="browse-by-decade">
        <TabView>
          <TabPanel v-for="(decade, index) in decades" :key="decade.value" :header="decade.label">
            <div 
              v-if="conferencesByDecade[decade.value] && conferencesByDecade[decade.value].length > 0" 
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            >
              <Card 
                v-for="conference in conferencesByDecade[decade.value]" 
                :key="conference.id" 
                class="conference-card"
                @click="goToConferenceDetail(conference.slug)"
              >
                <template #header>
                  <div 
                    class="conference-card-header h-32 w-full flex items-end p-4" 
                    :style="{
                      background: `linear-gradient(to right, ${conference.primaryColor}, ${conference.secondaryColor})`
                    }"
                  >
                    <span class="text-white font-bold bg-black/30 px-2 py-1 rounded text-sm">
                      {{ new Date(conference.startDate).getFullYear() }}
                    </span>
                  </div>
                </template>
                
                <template #title>
                  <div class="truncate">{{ conference.name }}</div>
                </template>
                
                <template #subtitle>
                  <div class="flex items-center gap-2">
                    <i class="pi pi-map-marker text-sm"></i>
                    <span>{{ conference.location }}</span>
                  </div>
                </template>
                
                <template #content>
                  <div class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    <i class="pi pi-calendar mr-2"></i>
                    {{ formatDateRange(conference.startDate, conference.endDate) }}
                  </div>
                  
                  <p class="line-clamp-2 text-sm mb-4">
                    {{ conference.description }}
                  </p>
                  
                  <div>
                    <Tag severity="secondary" class="text-xs">
                      {{ conference.university ? conference.university.name : 'Unknown University' }}
                    </Tag>
                  </div>
                </template>
                
                <template #footer>
                  <Button 
                    label="View Details" 
                    icon="pi pi-arrow-right" 
                    text 
                    class="p-button-sm w-full justify-center"
                  />
                </template>
              </Card>
            </div>
            
            <div 
              v-else
              class="no-conferences-in-decade text-center py-12"
            >
              <i class="pi pi-calendar-times text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
              <h3 class="text-xl font-medium mb-2">No conferences found for {{ decade.label }}</h3>
              <p class="text-gray-500 dark:text-gray-400">
                There are no conferences available in our archive for this decade.
              </p>
            </div>
          </TabPanel>
        </TabView>
      </div>
    </div>
  </div>
</template>

<style scoped>
.conference-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  cursor: pointer;
}

.conference-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.conference-card-header {
  background-size: cover;
  background-position: center;
  position: relative;
}
</style>