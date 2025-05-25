<template>
  <div>
    <!-- Toolbar component -->
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
          :value="conferenceStore.conferences"
          dataKey="id"
          :paginator="true"
          :rows="filters.per_page"
          :rowsPerPageOptions="[5, 10, 25]"
          :totalRecords="conferenceStore.meta?.total || 0"
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
          :sortField="filters.sort_field"
          :sortOrder="filters.sort_order === 'desc' ? -1 : 1"
          currentPageReportTemplate="Showing {first} to {last} of {totalRecords} conferences"
          responsiveLayout="stack"
          breakpoint="960px"
          class="p-datatable-sm rounded fixed-columns-table"
          :loading="conferenceStore.loading"
          scrollable
          scrollHeight="flex"
          @page="onPage"
          @sort="onSort"
        >
          <template #header>
            <div class="flex flex-column md:flex-row md:justify-between md:items-center gap-2">
              <h4 class="m-0">Manage Conferences</h4>
              <InputGroup class="w-full md:w-auto">
                <InputGroupAddon>
                  <i class="pi pi-search" />
                </InputGroupAddon>
                <InputText v-model="filters.search" placeholder="Search..." class="w-full" @input="debouncedSearch" />
                <InputGroupAddon v-if="filters.search">
                  <Button icon="pi pi-times" @click="clearSearch" class="p-button-text p-button-plain" />
                </InputGroupAddon>
              </InputGroup>
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
                <Button 
                  icon="pi pi-pencil" 
                  outlined 
                  rounded 
                  class="p-button-sm" 
                  @click="onEditPagesClick(slotProps.data)" 
                  v-tooltip.top="'Edit conference pages'"
                />
                
                <div v-if="authStore.hasAdminAccess" class="flex justify-center gap-2">
                  <Divider layout="vertical" />
                  <Button 
                    icon="pi pi-cog" 
                    outlined 
                    rounded 
                    class="p-button-sm" 
                    @click="editConference(slotProps.data)" 
                    v-tooltip.top="'Edit conference settings'"
                  />
                  <Button 
                    icon="pi pi-trash" 
                    outlined 
                    rounded 
                    severity="danger" 
                    class="p-button-sm" 
                    @click="confirmDeleteConference(slotProps.data)" 
                    v-tooltip.top="'Delete conference'"
                  />
                </div>
              </div>
            </template>
          </Column>

          <template #empty>
            <div class="p-4 text-center">
              <p>No conferences found.</p>
            </div>
          </template>
        </DataTable>
      </template>
    </Card>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from 'primevue/usetoast';
import type { Conference, ConferenceFilters } from '@/types/conference';
import ConferenceToolbar from './ConferenceToolbar.vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Divider from 'primevue/divider';
import Tooltip from 'primevue/tooltip';
import debounce from 'lodash/debounce';

export default defineComponent({
  name: 'ConferenceTable',
  components: {
    ConferenceToolbar,
    Card,
    DataTable,
    Column,
    Button,
    InputGroup,
    InputGroupAddon,
    InputText,
    Tag,
    Divider
  },
  directives: {
    tooltip: Tooltip
  },
  emits: ['edit-conference'],
  
  data() {
    return {
      conferenceStore: useConferenceStore(),
      authStore: useAuthStore(),
      toast: useToast(),
      filters: {
        search: '',
        sort_field: 'created_at',
        sort_order: 'desc',
        page: 1,
        per_page: 10
      } as ConferenceFilters,
      selectedConferences: [] as Conference[],
      dt: null as any,
      debouncedSearch: debounce(function(this: any) {
        this.filters.page = 1; // Reset to first page on search
        this.loadConferences();
      }, 300)
    };
  },
  
  mounted() {
    this.loadConferences();
  },
  methods: {
    async loadConferences() {
      this.conferenceStore.loading = true;
      try {
        const response = await this.conferenceStore.fetchConferences(this.filters);
        if (response?.message) {
          this.toast.add({
            severity: 'success',
            summary: 'Success',
            detail: response.message,
            life: 3000
          });
        }
      } catch (error: any) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.message || 'Failed to load conferences',
          life: 3000
        });
      } finally {
        this.conferenceStore.loading = false;
      }
    },
    
    onPage(event: any) {
      this.filters.page = event.page + 1; // PrimeVue uses 0-based indexing, API uses 1-based
      this.filters.per_page = event.rows;
      this.loadConferences();
    },

    onSort(event: any) {
      this.filters.sort_field = event.sortField;
      this.filters.sort_order = event.sortOrder === 1 ? 'asc' : 'desc';
      this.loadConferences();
    },

    clearSearch() {
      this.filters.search = '';
      this.filters.page = 1;
      this.loadConferences();
    },
    
    async editConference(conference: Conference): Promise<void> {
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
      // Navigate to the conference edit route directly without lock checks
      this.$router.push({ name: 'ConferenceEdit', params: { id: conference.id.toString() } });
    },
    
    async confirmDeleteConference(conference: Conference): Promise<void> {
      if (confirm(`Are you sure you want to delete "${conference.name}"?`)) {
        await this.deleteConference(conference.id);
      }
    },
    
    async deleteConference(id: number): Promise<void> {
      try {
        const response = await this.conferenceStore.deleteConference(id);
        this.loadConferences();
        this.toast.add({
          severity: 'success',
          summary: 'Success',
          detail: response?.message || 'Conference deleted successfully',
          life: 3000
        });
      } catch (error: any) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.message || (error instanceof Error ? error.message : 'Failed to delete conference'),
          life: 3000
        });
      }
    },
    
    async confirmDeleteSelected(): Promise<void> {
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
        await this.deleteSelectedConferences();
      }
    },
    
    async deleteSelectedConferences(): Promise<void> {
      if (this.selectedConferences.length === 0) {
        this.toast.add({
          severity: 'info',
          summary: 'Info',
          detail: 'No conferences selected for deletion',
          life: 3000
        });
        return;
      }
      
      try {
        let successCount = 0;
        const errors = [];
        
        // Delete each conference
        for (const conference of this.selectedConferences) {
          try {
            const response = await this.conferenceStore.deleteConference(conference.id);
            successCount++;
          } catch (error: any) {
            console.error(`Failed to delete conference ${conference.id}:`, error);
            errors.push({
              id: conference.id,
              name: conference.name,
              message: error.response?.message || 'Unknown error'
            });
          }
        }
        
        // Refresh the conference list
        this.selectedConferences = [];
        this.loadConferences();
        
        // Show appropriate success/failure messages
        if (successCount > 0) {
          this.toast.add({
            severity: 'success',
            summary: 'Success',
            detail: `Successfully deleted ${successCount} conferences`,
            life: 3000
          });
        }
        
        if (errors.length > 0) {
          this.toast.add({
            severity: 'warn',
            summary: 'Partial Success',
            detail: `Deleted ${successCount} of ${this.selectedConferences.length} conferences. Some deletions failed.`,
            life: 5000
          });
        }
      } catch (error: any) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.message || 'Failed to delete selected conferences',
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