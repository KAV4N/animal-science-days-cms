<!-- components/dashboard/ConferenceManagement/ConferenceTable.vue -->
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
              <div class="flex gap-2">
                <Dropdown v-model="sortBy" :options="sortOptions" optionLabel="label" optionValue="value" 
                  placeholder="Sort by" class="w-full md:w-auto" @change="applySorting" />
                <IconField class="w-full md:w-auto">
                  <InputIcon>
                    <i class="pi pi-search" />
                  </InputIcon>
                  <InputText v-model="searchQuery" placeholder="Search..." class="w-full" />
                </IconField>
                <Button icon="pi pi-search" @click="performSearch" label="Search" />
                <Button v-if="searchPerformed || searchQuery.trim().length > 0" 
                  icon="pi pi-times" @click="clearSearch" label="Clear" class="p-button-secondary" />
              </div>
            </div>
            <div v-if="searchPerformed" class="mt-2 text-sm text-blue-500">
              Results for "{{ lastSearchQuery }}"
            </div>
          </template>

          <!-- Fixed left column -->
          <Column v-if="authStore.hasAdminAccess" selectionMode="multiple" style="width: 3rem" frozen alignFrozen="left" class="checkbox-column" :exportable="false"></Column>
          
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
          <Column :exportable="false" style="min-width: 8rem" frozen alignFrozen="right" class="action-buttons-column">
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
import { defineComponent, ref, watch } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import { useConferenceStore } from '@/stores/conferenceStore';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from 'primevue/usetoast';
import type { Conference, ConferenceFilters } from '@/types/conference';
import ConferenceToolbar from './ConferenceToolbar.vue';

export default defineComponent({
  name: 'ConferenceTable',
  components: {
    ConferenceToolbar
  },
  emits: ['edit-conference'],
  setup() {
    const conferenceStore = useConferenceStore();
    const filteredConferences = ref<Conference[] | null>(null);
    const searchQuery = ref('');
    const lastSearchQuery = ref('');
    const searchPerformed = ref(false);
    const sortField = ref<string | null>(null);
    const sortOrder = ref<number>(1); // 1 for ascending, -1 for descending
    const sortBy = ref('');
    
    // Watch for changes in the conferenceStore.conferences
    watch(() => conferenceStore.conferences, (newValue) => {
      if (searchPerformed.value) {
        // Re-apply the search filter when the conferences are updated
        const query = lastSearchQuery.value.toLowerCase();
        filteredConferences.value = conferenceStore.conferences.filter(conference => {
          return (
            (conference.name && conference.name.toLowerCase().includes(query)) ||
            (conference.university?.full_name && conference.university.full_name.toLowerCase().includes(query)) ||
            (conference.start_date && new Date(conference.start_date).toLocaleDateString().includes(query)) ||
            (conference.end_date && new Date(conference.end_date).toLocaleDateString().includes(query))
          );
        });
      }
    }, { deep: true });
    
    return {
      filteredConferences,
      searchQuery,
      lastSearchQuery,
      searchPerformed,
      sortField,
      sortOrder,
      sortBy
    };
  },
  data() {
    return {
      conferenceStore: useConferenceStore(),
      authStore: useAuthStore(),
      toast: useToast(),
      selectedConferences: [] as Conference[],
      filters: {
        'global': { value: null, matchMode: FilterMatchMode.CONTAINS }
      },
      sortOptions: [
        { label: 'Newest First', value: 'newest' },
        { label: 'Oldest First', value: 'oldest' },
        { label: 'Recently Updated', value: 'recently_updated' },
        { label: 'Least Recently Updated', value: 'least_recently_updated' },
        { label: 'Start Date (Upcoming)', value: 'start_date_asc' },
        { label: 'Start Date (Recent)', value: 'start_date_desc' },
        { label: 'Name A-Z', value: 'name_asc' },
        { label: 'Name Z-A', value: 'name_desc' }
      ],
      currentFilters: {} as ConferenceFilters
    };
  },
  computed: {
    displayedConferences(): Conference[] {
      return this.filteredConferences !== null 
        ? this.filteredConferences 
        : this.conferenceStore.conferences;
    }
  },
  mounted() {
    this.refreshConferences();
  },
  methods: {
    editConference(conference: Conference): void {
      this.$emit('edit-conference', conference);
    },
    
    openCreateConferenceDialog(): void {
      this.$emit('edit-conference');
    },
    
    performSearch(): void {
      if (!this.searchQuery.trim()) {
        this.toast.add({
          severity: 'info',
          summary: 'Info',
          detail: 'Please enter a search term',
          life: 3000
        });
        return;
      }
      
      this.conferenceStore.loading = true;
      this.lastSearchQuery = this.searchQuery;
      this.searchPerformed = true;

      const query = this.searchQuery.toLowerCase();
      this.filteredConferences = this.conferenceStore.conferences.filter(conference => {
        return (
          (conference.name && conference.name.toLowerCase().includes(query)) ||
          (conference.university?.full_name && conference.university.full_name.toLowerCase().includes(query)) ||
          (conference.start_date && new Date(conference.start_date).toLocaleDateString().includes(query)) ||
          (conference.end_date && new Date(conference.end_date).toLocaleDateString().includes(query))
        );
      });
      
      this.conferenceStore.loading = false;
      
      this.toast.add({
        severity: 'info',
        summary: 'Search Results',
        detail: `Found ${this.filteredConferences.length} matching conferences`,
        life: 3000
      });
    },
    
    clearSearch(): void {
      this.searchQuery = '';
      this.lastSearchQuery = '';
      this.searchPerformed = false;
      this.filteredConferences = null;
      this.filters['global'].value = null;
      
      this.toast.add({
        severity: 'info',
        summary: 'Search Cleared',
        detail: 'Showing all conferences',
        life: 3000
      });
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
    
    applySorting() {
      const filters: ConferenceFilters = { ...this.currentFilters };
      
      switch (this.sortBy) {
        case 'newest':
          filters.sort_field = 'created_at';
          filters.sort_order = 'desc';
          this.sortField = 'created_at';
          this.sortOrder = -1;
          break;
        case 'oldest':
          filters.sort_field = 'created_at';
          filters.sort_order = 'asc';
          this.sortField = 'created_at';
          this.sortOrder = 1;
          break;
        case 'recently_updated':
          filters.sort_field = 'updated_at';
          filters.sort_order = 'desc';
          this.sortField = 'updated_at';
          this.sortOrder = -1;
          break;
        case 'least_recently_updated':
          filters.sort_field = 'updated_at';
          filters.sort_order = 'asc';
          this.sortField = 'updated_at';
          this.sortOrder = 1;
          break;
        case 'start_date_asc':
          filters.sort_field = 'start_date';
          filters.sort_order = 'asc';
          this.sortField = 'start_date';
          this.sortOrder = 1;
          break;
        case 'start_date_desc':
          filters.sort_field = 'start_date';
          filters.sort_order = 'desc';
          this.sortField = 'start_date';
          this.sortOrder = -1;
          break;
        case 'name_asc':
          filters.sort_field = 'name';
          filters.sort_order = 'asc';
          this.sortField = 'name';
          this.sortOrder = 1;
          break;
        case 'name_desc':
          filters.sort_field = 'name';
          filters.sort_order = 'desc';
          this.sortField = 'name';
          this.sortOrder = -1;
          break;
        default:
          break;
      }
      
      this.currentFilters = filters;
      this.refreshConferenceWithFilters(filters);
    },
    
    onSort(event: any) {
      // Handle the sort event from DataTable
      const sortField = event.sortField;
      const sortOrder = event.sortOrder === 1 ? 'asc' : 'desc';
      
      // Update filters and refresh data
      const filters: ConferenceFilters = {
        ...this.currentFilters,
        sort_field: sortField as 'name' | 'title' | 'start_date' | 'end_date' | 'created_at' | 'updated_at',
        sort_order: sortOrder as 'asc' | 'desc'
      };
      
      // Update dropdown selection to match the column sorting if applicable
      if (sortField === 'created_at') {
        this.sortBy = sortOrder === 'asc' ? 'oldest' : 'newest';
      } else if (sortField === 'updated_at') {
        this.sortBy = sortOrder === 'asc' ? 'least_recently_updated' : 'recently_updated';
      } else if (sortField === 'start_date') {
        this.sortBy = sortOrder === 'asc' ? 'start_date_asc' : 'start_date_desc';
      } else if (sortField === 'name') {
        this.sortBy = sortOrder === 'asc' ? 'name_asc' : 'name_desc';
      } else {
        this.sortBy = '';
      }
      
      this.currentFilters = filters;
      this.refreshConferenceWithFilters(filters);
    },
    
    async refreshConferenceWithFilters(filters: ConferenceFilters): Promise<void> {
      this.conferenceStore.loading = true;
      
      try {
        await this.conferenceStore.fetchConferences(filters);
        
        if (this.searchPerformed) {
          this.performSearch();
        }
      } catch (error) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error instanceof Error ? error.message : 'Failed to fetch conferences',
          life: 3000
        });
      } finally {
        this.conferenceStore.loading = false;
      }
    },
    
    refreshConferences(): void {
      this.conferenceStore.fetchConferences(this.currentFilters);
      if (this.searchPerformed) {
        this.performSearch();
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

  :deep(.p-datatable-tbody) tr td:first-child {
    position: sticky !important;
    border-right-width: 1px !important;
    left: 0 !important;
    z-index: 1 !important;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1) !important;
  }

  :deep(.p-datatable-tbody) tr td:last-child {
    position: sticky !important;
    border-left-width: 1px !important;
    right: 0 !important;
    z-index: 1 !important;
    box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1) !important;
  }
}
</style>