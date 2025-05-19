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
              <div class="flex items-center gap-2">
                <Tag :value="slotProps.data.is_published ? 'Published' : 'Draft'" :severity="getStatusSeverity(slotProps.data.is_published)" />
                <div v-if="slotProps.data.lock_status" class="inline-flex items-center">
                  <i class="pi pi-lock text-amber-600 text-sm" />
                  <span class="text-xs ml-1 text-amber-600">Locked</span>
                </div>
              </div>
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
                  :disabled="isLocked(slotProps.data)"
                  v-tooltip.top="isLocked(slotProps.data) ? 'Conference is locked by another user' : 'Edit conference pages'"
                />
                
                <div v-if="authStore.hasAdminAccess" class="flex justify-center gap-2">
                  <Divider layout="vertical" />
                  <Button 
                    icon="pi pi-cog" 
                    outlined 
                    rounded 
                    class="p-button-sm" 
                    @click="editConference(slotProps.data)" 
                    :disabled="isLocked(slotProps.data)"
                    v-tooltip.top="isLocked(slotProps.data) ? 'Conference is locked by another user' : 'Edit conference settings'"
                  />
                  <Button 
                    icon="pi pi-trash" 
                    outlined 
                    rounded 
                    severity="danger" 
                    class="p-button-sm" 
                    @click="confirmDeleteConference(slotProps.data)" 
                    :disabled="isLocked(slotProps.data)"
                    v-tooltip.top="isLocked(slotProps.data) ? 'Conference is locked by another user' : 'Delete conference'"
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
    
    isLocked(conference: Conference): boolean {
      // Conference is locked, but not by current user
      if (conference.lock_status && conference.lock_status.user_id !== this.authStore.user?.id) {
        return true;
      }
      // Otherwise, it's either not locked or locked by current user
      return false;
    },
    
    async editConference(conference: Conference): Promise<void> {
      // Allow editing if not locked or if locked by current user
      if (!conference.lock_status || conference.lock_status.user_id === this.authStore.user?.id) {
        this.$emit('edit-conference', conference);
      } else {
        // Conference is locked by someone else
        this.toast.add({
          severity: 'warn',
          summary: 'Conference Locked',
          detail: `This conference is currently being edited by ${conference.lock_status.user_name}. Please try again later.`,
          life: 5000
        });
      }
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
      // Check if conference is locked by different user before allowing access
      if (this.isLocked(conference)) {
        this.toast.add({
          severity: 'warn',
          summary: 'Conference Locked',
          detail: `This conference is currently being edited by ${conference.lock_status?.user_name || 'another user'}. Cannot access pages editor.`,
          life: 5000
        });
        return;
      }
      
      this.toast.add({
        severity: 'info',
        summary: 'Info',
        detail: `Conference pages editor for "${conference.name}" will be implemented in future releases.`,
        life: 3000
      });
    },
    
    async confirmDeleteConference(conference: Conference): Promise<void> {
      try {
        await this.conferenceStore.acquireLock(conference.id);
        if (confirm(`Are you sure you want to delete "${conference.name}"?`)) {
          await this.deleteConference(conference.id);
        } else {
          await this.conferenceStore.releaseLock(conference.id);
        }
      } catch (error: any) {
        if (error.response?.status === 423) {
          const lockInfo = error.response?.data?.lock_info;
          const userName = lockInfo?.user_name || 'another user';
          
          this.toast.add({
            severity: 'warn',
            summary: 'Cannot Delete',
            detail: error.response?.message || `This conference is currently being edited by ${userName}. Cannot delete.`,
            life: 5000
          });
        } else {
          this.toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.message || 'Failed to prepare conference for deletion',
            life: 3000
          });
        }
      }
    },
    
    async deleteConference(id: number): Promise<void> {
      try {
        // Since we already acquired the lock in confirmDeleteConference, we can proceed with deletion
        const response = await this.conferenceStore.deleteConference(id);
        this.loadConferences();
        this.toast.add({
          severity: 'success',
          summary: 'Success',
          detail: response?.message || 'Conference deleted successfully',
          life: 3000
        });
      } catch (error: any) {
        // Show the exact error message from the response if available
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.message || (error instanceof Error ? error.message : 'Failed to delete conference'),
          life: 3000
        });
        
        // If deletion failed, try to release the lock that was acquired during confirmation
        try {
          await this.conferenceStore.releaseLock(id);
        } catch (lockError) {
          // Silently handle lock release error
          console.warn('Failed to release lock after deletion error');
        }
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
      
      // Try to acquire locks for all selected conferences first
      const lockPromises = this.selectedConferences.map(async (conf) => {
        try {
          await this.conferenceStore.acquireLock(conf.id);
          return { conference: conf, locked: true, error: null };
        } catch (error: any) {
          return { 
            conference: conf, 
            locked: false, 
            error: error
          };
        }
      });
      
      const lockResults = await Promise.all(lockPromises);
      const lockedConferences = lockResults.filter(result => result.locked).map(result => result.conference);
      const failedToLock = lockResults.filter(result => !result.locked);
      
      // If some conferences couldn't be locked, show a warning
      if (failedToLock.length > 0) {
        const lockedByOthers = failedToLock
          .filter(item => item.error.response?.status === 423)
          .map(item => item.conference.name);
          
        if (lockedByOthers.length > 0) {
          this.toast.add({
            severity: 'warn',
            summary: 'Cannot Delete Some Conferences',
            detail: `Some selected conferences are being edited by others and cannot be deleted: ${lockedByOthers.join(', ')}`,
            life: 5000
          });
        }
        
        // If there are other errors (not lock-related)
        const otherErrors = failedToLock.filter(item => item.error.response?.status !== 423);
        if (otherErrors.length > 0) {
          this.toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to prepare some conferences for deletion',
            life: 3000
          });
        }
        
        // If we couldn't lock any conferences, return early
        if (lockedConferences.length === 0) {
          return;
        }
      }
      
      // Prompt for deletion confirmation with only the conferences we could lock
      if (confirm(`Are you sure you want to delete ${lockedConferences.length} selected conferences?`)) {
        await this.deleteSelectedConferences(lockedConferences);
      } else {
        // If user cancels, release all acquired locks
        await Promise.all(lockedConferences.map(conf => 
          this.conferenceStore.releaseLock(conf.id).catch(err => console.warn(`Failed to release lock for conference ${conf.id}`, err))
        ));
      }
    },
    
    async deleteSelectedConferences(conferencesToDelete = this.selectedConferences): Promise<void> {
      if (conferencesToDelete.length === 0) {
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
        for (const conference of conferencesToDelete) {
          try {
            const response = await this.conferenceStore.deleteConference(conference.id);
            successCount++;
            
            // Lock is automatically released by the deleteConference API
          } catch (error: any) {
            console.error(`Failed to delete conference ${conference.id}:`, error);
            errors.push({
              id: conference.id,
              name: conference.name,
              message: error.response?.message || 'Unknown error'
            });
            
            // If deletion failed, try to release the lock
            try {
              await this.conferenceStore.releaseLock(conference.id);
            } catch (lockError) {
              // Silently handle lock release error
              console.warn(`Failed to release lock for conference ${conference.id} after deletion error`);
            }
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
            detail: `Deleted ${successCount} of ${conferencesToDelete.length} conferences. Some deletions failed.`,
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