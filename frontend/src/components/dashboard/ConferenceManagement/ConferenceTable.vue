<!-- components/dashboard/ConferenceManagement/ConferenceTable.vue -->
<template>
  <Card class="card rounded-md">
    <template #content>
      <DataTable
        ref="dt"
        v-model:selection="selectedConferences"
        :value="store.conferences"
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
        :loading="store.loading"
        scrollable
        scrollHeight="flex"
      >
        <template #header>
          <div class="flex flex-column md:flex-row md:justify-between md:items-center gap-2">
            <h4 class="m-0">Manage Conferences</h4>
            <div class="flex gap-2">
              <IconField class="w-full md:w-auto">
                <InputIcon>
                  <i class="pi pi-search" />
                </InputIcon>
                <InputText v-model="searchQuery" placeholder="Search..." class="w-full" />
              </IconField>
              <Button icon="pi pi-search" @click="performSearch" label="Search" />
              <Button icon="pi pi-times" @click="clearSearch" label="Clear" class="p-button-secondary" />
            </div>
          </div>
          <div v-if="searchPerformed" class="mt-2 text-sm text-blue-500">
            Results for "{{ lastSearchQuery }}"
          </div>
        </template>

        <!-- Fixed left column -->
        <Column selectionMode="multiple" style="width: 3rem" frozen alignFrozen="left" class="checkbox-column" :exportable="false"></Column>
        
        <Column field="name" header="Name" sortable style="min-width: 16rem"></Column>
        <Column field="university" header="University" sortable style="min-width: 12rem">
          <template #body="slotProps">
            {{ slotProps.data.university.full_name}}
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
        
        <!-- Fixed right column -->
        <Column :exportable="false" style="min-width: 8rem" frozen alignFrozen="right" class="action-buttons-column">
          <template #header>
            <div class="text-center">Actions</div>
          </template>
          <template #body="slotProps">
            <div class="flex justify-center gap-2">
              <Button icon="pi pi-pencil" outlined rounded class="p-button-sm" @click="onEditPagesClick(slotProps.data)" />

              <div v-if="authStore.hasAdminAccess">
                <Button icon="pi pi-cog" outlined rounded class="p-button-sm" @click="editConference(slotProps.data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger" class="p-button-sm" @click="confirmDeleteConference(slotProps.data)" />
              </div>

             
            </div>
          </template>
        </Column>
      </DataTable>
    </template>
  </Card>
</template>

<script>
import { FilterMatchMode } from '@primevue/core/api';
import { useConferenceStore } from '@/stores/conferenceStore';
import { useAuthStore } from '@/stores/authStore';

export default {
  name: 'ConferenceTable',
  props: {
    toast: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      store: null,
      authStore: null,
      selectedConferences: [],
      searchQuery: '',
      lastSearchQuery: '',
      searchPerformed: false,
      filters: {
        'global': { value: null, matchMode: FilterMatchMode.CONTAINS }
      },
      originalConferences: []
    };
  },
  created() {
    this.store = useConferenceStore();
    this.authStore = useAuthStore();
  },

  mounted() {
    this.fetchConferences();
  },
  methods: {
    fetchConferences() {
      this.store.fetchConferences();
      this.originalConferences = [...this.store.conferences];
    },
    
    performSearch() {
      if (!this.searchQuery.trim()) {
        this.toast.add({
          severity: 'info',
          summary: 'Info',
          detail: 'Please enter a search term',
          life: 3000
        });
        return;
      }
      
      this.store.loading = true;
      this.lastSearchQuery = this.searchQuery;
      this.searchPerformed = true;

      if (this.originalConferences.length === 0) {
        this.originalConferences = [...this.store.conferences];
      }

      const query = this.searchQuery.toLowerCase();
      this.store.conferences = this.originalConferences.filter(conference => {
        return (
          (conference.name && conference.name.toLowerCase().includes(query)) ||
          (conference.university?.name && conference.university.name.toLowerCase().includes(query)) ||
          (conference.start_date && new Date(conference.start_date).toLocaleDateString().includes(query)) ||
          (conference.end_date && new Date(conference.end_date).toLocaleDateString().includes(query))
        );
      });
      
      this.store.loading = false;
      
      this.toast.add({
        severity: 'info',
        summary: 'Search Results',
        detail: `Found ${this.store.conferences.length} matching conferences`,
        life: 3000
      });
    },
    
    clearSearch() {
      this.searchQuery = '';
      this.lastSearchQuery = '';
      this.searchPerformed = false;
      
      if (this.originalConferences.length > 0) {
        this.store.conferences = [...this.originalConferences];
      } else {
        this.fetchConferences();
      }
      
      this.filters['global'].value = null;
      
      this.toast.add({
        severity: 'info',
        summary: 'Search Cleared',
        detail: 'Showing all conferences',
        life: 3000
      });
    },
    
    formatDate(value) {
      if (value) {
        return new Date(value).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      }
      return '';
    },
    
    getStatusSeverity(isPublished) {
      return isPublished ? 'success' : 'warning';
    },
    
    onEditPagesClick(conference) {
      this.toast.add({
        severity: 'info',
        summary: 'Info',
        detail: `Conference pages editor for "${conference.name}" will be implemented in future releases.`,
        life: 3000
      });
    },
    
    editConference(conference) {
      this.store.currentConference = conference;
      
      this.$router.push({
        name: 'conference-edit',
        params: { id: conference.id }
      });
    },
    
    confirmDeleteConference(conference) {
      if (confirm(`Are you sure you want to delete "${conference.name}"?`)) {
        this.deleteConference(conference.id);
      }
    },
    
    async deleteConference(id) {
      try {
        await this.store.deleteConference(id);

        this.originalConferences = this.originalConferences.filter(c => c.id !== id);
        
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
          detail: error || 'Failed to delete conference',
          life: 3000
        });
      }
    },
    
    deleteSelectedConferences() {
      // Implement logic to delete selected conferences
      this.toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Selected conferences deleted successfully',
        life: 3000
      });
    }
  }
};
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