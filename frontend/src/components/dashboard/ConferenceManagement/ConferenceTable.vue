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
          :value="conferences"
          dataKey="id"
          :paginator="true"
          :rows="filters.per_page"
          :rowsPerPageOptions="[5, 10, 25]"
          :totalRecords="totalRecords"
          :lazy="true"
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
          :sortField="filters.sort_field"
          :sortOrder="filters.sort_order === 'desc' ? -1 : 1"
          currentPageReportTemplate="Showing {first} to {last} of {totalRecords} conferences"
          responsiveLayout="stack"
          breakpoint="960px"
          class="p-datatable-sm rounded fixed-columns-table"
          :loading="isLoading"
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
                <InputText v-model="filters.search" placeholder="Search by name..." class="w-full" @input="onSearchInput" />
                <InputGroupAddon v-if="filters.search">
                  <Button icon="pi pi-times" @click="clearSearch" class="p-button-text p-button-plain" />
                </InputGroupAddon>
              </InputGroup>
            </div>
          </template>

          <!-- Fixed left column -->
          <Column v-if="authStore.hasAdminAccess" selectionMode="multiple" style="width: 3rem" frozen alignFrozen="left" class="checkbox-column border-e shadow" :exportable="false"></Column>
          
          <Column field="name" header="Name" sortable style="min-width: 16rem">
            <template #body="slotProps">
              <span v-tooltip.top="slotProps.data.name" class="truncate block">
                {{ truncateText(slotProps.data.name, 30) }}
              </span>
            </template>
          </Column>
          <Column field="university" header="University" sortable style="min-width: 12rem">
            <template #body="slotProps">
              <span v-tooltip.top="slotProps.data.university?.full_name" class="truncate block">
                {{ truncateText(slotProps.data.university?.full_name, 25) }}
              </span>
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
import { defineComponent, ref, computed, onMounted, nextTick } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from 'primevue/usetoast';
import { useRouter } from 'vue-router';
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
  
  setup(props, { emit }) {
    const conferenceStore = useConferenceStore();
    const authStore = useAuthStore();
    const toast = useToast();
    const router = useRouter();
    
    // Reactive refs
    const selectedConferences = ref<Conference[]>([]);
    const dt = ref<any>(null);
    const isLoading = ref(false);
    
    const filters = ref<ConferenceFilters>({
      search: '',
      sort_field: 'created_at',
      sort_order: 'desc',
      page: 1,
      per_page: 10
    });

    // Computed properties
    const conferences = computed(() => {
      return conferenceStore.conferences || [];
    });

    const totalRecords = computed(() => {
      const metaTotal = conferenceStore.meta?.total || 0;
      const actualLength = conferences.value.length;
      
      // Use meta total if it's available and makes sense
      // Otherwise fall back to actual array length for non-lazy loading
      if (metaTotal > 0) {
        return metaTotal;
      } else if (actualLength > 0) {
        return actualLength;
      }
      return 0;
    });

    // Methods
    const loadConferences = async () => {
      isLoading.value = true;
      try {
        const response = await conferenceStore.fetchConferences(filters.value);
        
        // Force reactivity update
        await nextTick();
        if (dt.value) {
          dt.value.$forceUpdate();
        }
        
      } catch (error: any) {
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.data?.message || error.message || 'Failed to load conferences',
          life: 3000
        });
      } finally {
        isLoading.value = false;
      }
    };
    
    // Debounced search function with page reset
    const debouncedSearch = debounce(() => {
      // Reset to first page when searching
      filters.value.page = 1;
      loadConferences();
    }, 300);

    // Handle search input with immediate page reset
    const onSearchInput = () => {
      // Reset page immediately when user starts typing
      filters.value.page = 1;
      debouncedSearch();
    };
    
    const onPage = (event: any) => {
      filters.value.page = event.page + 1; // PrimeVue uses 0-based indexing, API uses 1-based
      filters.value.per_page = event.rows;
      loadConferences();
    };

    const onSort = (event: any) => {
      // Reset to first page when sorting
      filters.value.page = 1;
      filters.value.sort_field = event.sortField;
      filters.value.sort_order = event.sortOrder === 1 ? 'asc' : 'desc';
      loadConferences();
    };

    const clearSearch = () => {
      filters.value.search = '';
      // Reset to first page when clearing search
      filters.value.page = 1;
      loadConferences();
    };

    // Additional method to reset pagination and reload data
    const resetPaginationAndReload = () => {
      filters.value.page = 1;
      selectedConferences.value = []; // Clear selection
      loadConferences();
    };
    
    const editConference = async (conference: Conference): Promise<void> => {
      emit('edit-conference', conference);
    };
    
    const openCreateConferenceDialog = (): void => {
      emit('edit-conference');
    };
    
    const formatDate = (value: string): string => {
      if (value) {
        return new Date(value).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      }
      return '';
    };
    
    const formatDateTime = (value: string): string => {
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
    };
    
    const getStatusSeverity = (isPublished: boolean): string => {
      return isPublished ? 'success' : 'warning';
    };
    
    const truncateText = (text: string | undefined, maxLength: number): string => {
      if (!text) return '';
      if (text.length <= maxLength) return text;
      return text.substring(0, maxLength - 3) + '...';
    };
    
    const onEditPagesClick = (conference: Conference): void => {
      router.push({ name: 'ConferenceEdit', params: { id: conference.id.toString() } });
    };
    
    const confirmDeleteConference = async (conference: Conference): Promise<void> => {
      if (confirm(`Are you sure you want to delete "${conference.name}"?`)) {
        await deleteConference(conference.id);
      }
    };
    
    const deleteConference = async (id: number): Promise<void> => {
      try {
        const response = await conferenceStore.deleteConference(id);
        // Reset pagination and reload data after deletion
        await resetPaginationAndReload();
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: response?.message || 'Conference deleted successfully',
          life: 3000
        });
      } catch (error: any) {
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.data?.message || error.message || 'Failed to delete conference',
          life: 3000
        });
      }
    };
    
    const confirmDeleteSelected = async (): Promise<void> => {
      if (selectedConferences.value.length === 0) {
        toast.add({
          severity: 'info',
          summary: 'Info',
          detail: 'No conferences selected',
          life: 3000
        });
        return;
      }
      
      if (confirm(`Are you sure you want to delete ${selectedConferences.value.length} selected conferences?`)) {
        await deleteSelectedConferences();
      }
    };
    
    const deleteSelectedConferences = async (): Promise<void> => {
      if (selectedConferences.value.length === 0) {
        toast.add({
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
        
        for (const conference of selectedConferences.value) {
          try {
            await conferenceStore.deleteConference(conference.id);
            successCount++;
          } catch (error: any) {
            errors.push({
              id: conference.id,
              name: conference.name,
              message: error.response?.data?.message || error.message || 'Unknown error'
            });
          }
        }
        
        // Reset pagination and reload data after deletion
        await resetPaginationAndReload();
        
        if (successCount > 0) {
          toast.add({
            severity: 'success',
            summary: 'Success',
            detail: `Successfully deleted ${successCount} conferences`,
            life: 3000
          });
        }
        
        if (errors.length > 0) {
          toast.add({
            severity: 'warn',
            summary: 'Partial Success',
            detail: `Deleted ${successCount} conferences. ${errors.length} deletions failed.`,
            life: 5000
          });
        }
      } catch (error: any) {
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.data?.message || error.message || 'Failed to delete selected conferences',
          life: 3000
        });
      }
    };

    // Lifecycle
    onMounted(() => {
      loadConferences();
    });

    return {
      // Stores
      conferenceStore,
      authStore,
      toast,
      router,
      
      // Refs
      selectedConferences,
      dt,
      filters,
      isLoading,
      
      // Computed
      conferences,
      totalRecords,
      
      // Methods
      loadConferences,
      onPage,
      onSort,
      clearSearch,
      onSearchInput,
      resetPaginationAndReload,
      editConference,
      openCreateConferenceDialog,
      formatDate,
      formatDateTime,
      getStatusSeverity,
      truncateText,
      onEditPagesClick,
      confirmDeleteConference,
      deleteConference,
      confirmDeleteSelected,
      deleteSelectedConferences,
      debouncedSearch
    };
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

:deep(.p-datatable .p-datatable-tbody > tr > td) {
  @apply truncate;
}

@media screen and (max-width: 960px) {
  :deep(.p-datatable-wrapper) {
    overflow-x: auto;
  }
}
</style>