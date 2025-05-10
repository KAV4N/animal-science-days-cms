<template>
  <div>
    <!-- Add the toolbar component -->
    <ConferenceToolbar 
      v-if="authStore.hasAdminAccess"
      :selectedConferences="selectedConferences"
      @new-conference="openCreateConferenceDialog" 
      @confirm-delete-selected="confirmDeleteSelected"
    />
    
    <Card class="card rounded-md">
      <template #content>
        <DataTable
          ref="dt"
          v-model:selection="selectedConferences"
          :value="displayedConferences"
          dataKey="id"
          :paginator="true"
          :rows="10"
          :filters="filters"
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
          :rowsPerPageOptions="[5, 10, 25]"
          currentPageReportTemplate="Showing {first} to {last} of {totalRecords} conferences"
          responsiveLayout="stack"
          breakpoint="960px"
          class="p-datatable-sm rounded fixed-columns-table"
          :loading="conferenceStore.loading"
          scrollable
          scrollHeight="flex"
          v-model:sortField="sortField"
          v-model:sortOrder="sortOrder"
          @sort="onSort"
        >
          <template #header>
            <div class="flex flex-column md:flex-row md:justify-between md:items-center gap-2">
              <h4 class="m-0">Manage Conferences</h4>
              <div class="flex flex-wrap gap-2">
                <Dropdown v-model="sortBy" :options="sortOptions" optionLabel="label" optionValue="value" 
                  placeholder="Sort by" class="w-full md:w-auto" @change="applySorting" />
                <InputGroup class="w-full md:w-auto">
                  <InputGroupAddon>
                    <i class="pi pi-search" />
                  </InputGroupAddon>
                  <InputText v-model="searchQuery" placeholder="Search..." class="w-full" @input="debouncedSearch" />
                  <InputGroupAddon v-if="searchQuery.trim().length > 0">
                    <Button icon="pi pi-times" @click="clearSearch" class="p-button-text p-button-plain" />
                  </InputGroupAddon>
                </InputGroup>
              </div>
            </div>
            <div v-if="searchPerformed" class="mt-2 text-sm text-blue-500">
              Results for "{{ lastSearchQuery }}"
            </div>
          </template>

          <!-- Fixed left column -->
          <Column v-if="authStore.hasAdminAccess" selectionMode="multiple" style="width: 3rem" frozen alignFrozen="left" class="checkbox-column border-e shadow" :exportable="false"></Column>
          
          <Column field="name" header="Name" sortable style="min-width: 16rem"></Column>
          <Column field="university" header="University" sortable style="min-width: 12rem">
            <template #body="slotProps">
              {{ slotProps.data.university?.full_name }}
            </template>
          </Column>
          <Column field="start_date" header="Start Date" sortable style="min-width: 10rem">
            <template #body="slotProps">
              {{ formatDate(slotProps.data.start_date) }}
            </template>
          </Column>
          <Column field="end_date" header="End Date" sortable style="min-width: 10rem">
            <template #body="slotProps">
              {{ formatDate(slotProps.data.end_date) }}
            </template>
          </Column>
          
          <Column field="is_published" header="Status" sortable style="min-width: 10rem">
            <template #body="slotProps">
              <Tag :value="slotProps.data.is_published ? 'Published' : 'Draft'" :severity="getStatusSeverity(slotProps.data.is_published)" />
            </template>
          </Column>
          
          <Column field="created_at" header="Created" sortable style="min-width: 10rem">
            <template #body="slotProps">
              {{ formatDateTime(slotProps.data.created_at) }}
            </template>
          </Column>
          
          <Column field="updated_at" header="Last Updated" sortable style="min-width: 10rem">
            <template #body="slotProps">
              {{ formatDateTime(slotProps.data.updated_at) }}
            </template>
          </Column>
          
          <!-- Fixed right column -->
          <Column :exportable="false" style="min-width: 8rem" frozen alignFrozen="right" class="action-buttons-column border-s shadow">
            <template #header>
              <div class="text-center">Actions</div>
            </template>
            <template #body="slotProps">
              <div class="flex justify-center gap-2">
                <Button icon="pi pi-pencil" outlined rounded class="p-button-sm" @click="onEditPagesClick(slotProps.data)" />
                
                <div v-if="authStore.hasAdminAccess" class="flex justify-center gap-2">
                  <Divider layout="vertical" />
                  <Button icon="pi pi-cog" outlined rounded class="p-button-sm" @click="editConference(slotProps.data)" />
                  <Button icon="pi pi-trash" outlined rounded severity="danger" class="p-button-sm" @click="confirmDeleteConference(slotProps.data)" />
                </div>
              </div>
            </template>
          </Column>

          <template #empty>
            <div class="p-4 text-center">
              <p>No conferences found. {{ searchPerformed ? 'Try a different search term.' : 'Create a new conference to get started.' }}</p>
            </div>
          </template>
        </DataTable>
      </template>
    </Card>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, watch, onMounted } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import { useConferenceStore } from '@/stores/conferenceStore';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from 'primevue/usetoast';
import type { Conference, ConferenceFilters } from '@/types/conference';
import ConferenceToolbar from './ConferenceToolbar.vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Divider from 'primevue/divider';
import debounce from 'lodash/debounce';

export default defineComponent({
  name: 'ConferenceTable',
  components: {
    ConferenceToolbar,
    Card,
    DataTable,
    Column,
    Button,
    Dropdown,
    InputGroup,
    InputGroupAddon,
    InputText,
    Tag,
    Divider
  },
  emits: ['edit-conference'],
  setup() {
    const conferenceStore = useConferenceStore();
    const authStore = useAuthStore();
    const toast = useToast();
    
    // Reactive state
    const filteredConferences = ref<Conference[] | null>(null);
    const searchQuery = ref('');
    const lastSearchQuery = ref('');
    const searchPerformed = ref(false);
    const sortField = ref<string | null>(null);
    const sortOrder = ref<number>(1); // 1 for ascending, -1 for descending
    const sortBy = ref('');
    const selectedConferences = ref<Conference[]>([]);
    const dt = ref();
    
    const filters = ref({
      'global': { value: null, matchMode: FilterMatchMode.CONTAINS }
    });

    const sortOptions = [
      { label: 'Newest First', value: 'newest' },
      { label: 'Oldest First', value: 'oldest' },
      { label: 'Recently Updated', value: 'recently_updated' },
      { label: 'Least Recently Updated', value: 'least_recently_updated' },
      { label: 'Start Date (Upcoming)', value: 'start_date_asc' },
      { label: 'Start Date (Recent)', value: 'start_date_desc' },
      { label: 'Name A-Z', value: 'name_asc' },
      { label: 'Name Z-A', value: 'name_desc' }
    ];

    const currentFilters = ref<ConferenceFilters>({});
    
    // Create a debounced search function
    const debouncedSearch = debounce(() => {
      performSearch();
    }, 300);

    // Watch for changes in the conferenceStore.conferences
    watch(() => conferenceStore.conferences, (newValue) => {
      if (searchPerformed.value) {
        // Re-apply the search filter when the conferences are updated
        filterConferences();
      }
    }, { deep: true });

    // Methods
    const filterConferences = () => {
      const query = lastSearchQuery.value.toLowerCase();
      filteredConferences.value = conferenceStore.conferences.filter(conference => {
        return (
          (conference.name && conference.name.toLowerCase().includes(query)) ||
          (conference.university?.full_name && conference.university.full_name.toLowerCase().includes(query)) ||
          (conference.start_date && new Date(conference.start_date).toLocaleDateString().includes(query)) ||
          (conference.end_date && new Date(conference.end_date).toLocaleDateString().includes(query))
        );
      });
    };
    
    const performSearch = () => {
      if (!searchQuery.value.trim()) {
        if (searchPerformed.value) {
          clearSearch();
        }
        return;
      }
      
      conferenceStore.loading = true;
      lastSearchQuery.value = searchQuery.value;
      searchPerformed.value = true;

      filterConferences();
      
      conferenceStore.loading = false;
      
      toast.add({
        severity: 'info',
        summary: 'Search Results',
        detail: `Found ${filteredConferences.value?.length} matching conferences`,
        life: 3000
      });
    };
    
    const clearSearch = () => {
      searchQuery.value = '';
      lastSearchQuery.value = '';
      searchPerformed.value = false;
      filteredConferences.value = null;
      filters.value['global'].value = null;
      
      toast.add({
        severity: 'info',
        summary: 'Search Cleared',
        detail: 'Showing all conferences',
        life: 3000
      });
    };
    
    const applySorting = () => {
      const filters: ConferenceFilters = { ...currentFilters.value };
      
      switch (sortBy.value) {
        case 'newest':
          filters.sort_field = 'created_at';
          filters.sort_order = 'desc';
          sortField.value = 'created_at';
          sortOrder.value = -1;
          break;
        case 'oldest':
          filters.sort_field = 'created_at';
          filters.sort_order = 'asc';
          sortField.value = 'created_at';
          sortOrder.value = 1;
          break;
        case 'recently_updated':
          filters.sort_field = 'updated_at';
          filters.sort_order = 'desc';
          sortField.value = 'updated_at';
          sortOrder.value = -1;
          break;
        case 'least_recently_updated':
          filters.sort_field = 'updated_at';
          filters.sort_order = 'asc';
          sortField.value = 'updated_at';
          sortOrder.value = 1;
          break;
        case 'start_date_asc':
          filters.sort_field = 'start_date';
          filters.sort_order = 'asc';
          sortField.value = 'start_date';
          sortOrder.value = 1;
          break;
        case 'start_date_desc':
          filters.sort_field = 'start_date';
          filters.sort_order = 'desc';
          sortField.value = 'start_date';
          sortOrder.value = -1;
          break;
        case 'name_asc':
          filters.sort_field = 'name';
          filters.sort_order = 'asc';
          sortField.value = 'name';
          sortOrder.value = 1;
          break;
        case 'name_desc':
          filters.sort_field = 'name';
          filters.sort_order = 'desc';
          sortField.value = 'name';
          sortOrder.value = -1;
          break;
        default:
          break;
      }
      
      currentFilters.value = filters;
      refreshConferenceWithFilters(filters);
    };
    
    const onSort = (event: any) => {
      // Handle the sort event from DataTable
      const sortField = event.sortField;
      const sortOrder = event.sortOrder === 1 ? 'asc' : 'desc';
      
      // Update filters and refresh data
      const filters: ConferenceFilters = {
        ...currentFilters.value,
        sort_field: sortField as 'name' | 'title' | 'start_date' | 'end_date' | 'created_at' | 'updated_at',
        sort_order: sortOrder as 'asc' | 'desc'
      };
      
      // Update dropdown selection to match the column sorting if applicable
      if (sortField === 'created_at') {
        sortBy.value = sortOrder === 'asc' ? 'oldest' : 'newest';
      } else if (sortField === 'updated_at') {
        sortBy.value = sortOrder === 'asc' ? 'least_recently_updated' : 'recently_updated';
      } else if (sortField === 'start_date') {
        sortBy.value = sortOrder === 'asc' ? 'start_date_asc' : 'start_date_desc';
      } else if (sortField === 'name') {
        sortBy.value = sortOrder === 'asc' ? 'name_asc' : 'name_desc';
      } else {
        sortBy.value = '';
      }
      
      currentFilters.value = filters;
      refreshConferenceWithFilters(filters);
    };
    
    const refreshConferenceWithFilters = async (filters: ConferenceFilters) => {
      conferenceStore.loading = true;
      
      try {
        await conferenceStore.fetchConferences(filters);
        
        if (searchPerformed.value) {
          filterConferences();
        }
      } catch (error) {
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error instanceof Error ? error.message : 'Failed to fetch conferences',
          life: 3000
        });
      } finally {
        conferenceStore.loading = false;
      }
    };
    
    const refreshConferences = () => {
      conferenceStore.fetchConferences(currentFilters.value);
      if (searchPerformed.value) {
        filterConferences();
      }
    };

    // Initialize data on mount
    onMounted(() => {
      refreshConferences();
    });

    return {
      conferenceStore,
      authStore,
      toast,
      filteredConferences,
      searchQuery,
      lastSearchQuery,
      searchPerformed,
      sortField,
      sortOrder,
      sortBy,
      selectedConferences,
      filters,
      sortOptions,
      currentFilters,
      dt,
      debouncedSearch,
      performSearch,
      clearSearch,
      applySorting,
      onSort,
      refreshConferenceWithFilters,
      refreshConferences
    };
  },
  computed: {
    displayedConferences(): Conference[] {
      return this.filteredConferences !== null 
        ? this.filteredConferences 
        : this.conferenceStore.conferences;
    }
  },
  methods: {
    editConference(conference: Conference): void {
      this.$emit('edit-conference', conference);
    },
    
    openCreateConferenceDialog(): void {
      this.$emit('edit-conference');
    },
    
    formatDate(value: string): string {
      if (value) {
        return new Date(value).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      }
      return '';
    },
    
    formatDateTime(value: string): string {
      if (value) {
        return new Date(value).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      }
      return '';
    },
    
    getStatusSeverity(isPublished: boolean): string {
      return isPublished ? 'success' : 'warning';
    },
    
    onEditPagesClick(conference: Conference): void {
      this.toast.add({
        severity: 'info',
        summary: 'Info',
        detail: `Conference pages editor for "${conference.name}" will be implemented in future releases.`,
        life: 3000
      });
    },
    
    confirmDeleteConference(conference: Conference): void {
      if (confirm(`Are you sure you want to delete "${conference.name}"?`)) {
        this.deleteConference(conference.id);
      }
    },
    
    async deleteConference(id: number): Promise<void> {
      try {
        await this.conferenceStore.deleteConference(id);
        
        if (this.searchPerformed) {
          this.performSearch();
        }
        
        this.toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Conference deleted successfully',
          life: 3000
        });
      } catch (error) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error instanceof Error ? error.message : 'Failed to delete conference',
          life: 3000
        });
      }
    },
    
    confirmDeleteSelected(): void {
      if (this.selectedConferences.length === 0) {
        this.toast.add({
          severity: 'info',
          summary: 'Info',
          detail: 'No conferences selected',
          life: 3000
        });
        return;
      }
      
      if (confirm(`Are you sure you want to delete ${this.selectedConferences.length} selected conferences?`)) {
        this.deleteSelectedConferences();
      }
    },
    
    async deleteSelectedConferences(): Promise<void> {
      if (this.selectedConferences.length === 0) {
        this.toast.add({
          severity: 'info',
          summary: 'Info',
          detail: 'No conferences selected',
          life: 3000
        });
        return;
      }
      
      try {
        for (const conference of this.selectedConferences) {
          await this.conferenceStore.deleteConference(conference.id);
        }
        
        this.selectedConferences = [];
        
        // Refresh the list after deletion
        this.refreshConferences();
        
        this.toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Selected conferences deleted successfully',
          life: 3000
        });
      } catch (error) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error instanceof Error ? error.message : 'Failed to delete selected conferences',
          life: 3000
        });
      }
    }
  }
});
</script>

<style scoped>
.fixed-columns-table {
  overflow-x: auto;
}

.checkbox-column {
  z-index: 1;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
}

.action-buttons-column {
  z-index: 1; 
  box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
}

@media screen and (max-width: 960px) {
  :deep(.p-datatable-wrapper) {
    overflow-x: auto;
  }
}
</style>