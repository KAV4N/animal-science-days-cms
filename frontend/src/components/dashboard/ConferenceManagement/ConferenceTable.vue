<!-- components/dashboard/ConferenceTable.vue -->
<template>
  <Toolbar class="mb-6 flex flex-wrap gap-2 rounded-md shadow" style="border:none; margin-bottom: 1rem;">
    <template #start>
      <div class="flex flex-wrap gap-2 ">
        <Button label="New Conference" icon="pi pi-plus" class="mr-2" @click="store.openNewConference" />
        <Button 
          label="Delete" 
          icon="pi pi-trash" 
          severity="danger" 
          outlined 
          @click="store.confirmDeleteSelected" 
          :disabled="!store.selectedConferences || !store.selectedConferences.length" 
        />
      </div>
    </template>
  </Toolbar>
  <Card class="card rounded-md">
  <template #content>
    <DataTable
    ref="dt"
    v-model:selection="store.selectedConferences"
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
        <IconField class="w-full md:w-auto">
          <InputIcon>
            <i class="pi pi-search" />
          </InputIcon>
          <InputText v-model="filters['global'].value" placeholder="Search..." class="w-full" />
        </IconField>
      </div>
    </template>

    <!-- Fixed left column -->
    <Column selectionMode="multiple" style="width: 3rem" frozen alignFrozen="left" class="checkbox-column" :exportable="false"></Column>
    
    <Column field="name" header="Name" sortable style="min-width: 16rem"></Column>
    <Column field="university" header="University" sortable style="min-width: 12rem" class="hidden md:table-cell">
      <template #body="slotProps">
        {{ slotProps.data.university?.name }}
      </template>
    </Column>
    <Column field="startDate" header="Start Date" sortable style="min-width: 10rem">
      <template #body="slotProps">
        {{ formatDate(slotProps.data.startDate) }}
      </template>
    </Column>
    <Column field="endDate" header="End Date" sortable style="min-width: 10rem">
      <template #body="slotProps">
        {{ formatDate(slotProps.data.endDate) }}
      </template>
    </Column>
    
    <Column field="isPublished" header="Status" sortable style="min-width: 10rem">
      <template #body="slotProps">
        <Tag :value="slotProps.data.isPublished ? 'Published' : 'Draft'" :severity="getStatusSeverity(slotProps.data.isPublished)" />
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
          <Button icon="pi pi-cog" outlined rounded class="p-button-sm" @click="store.editConference(slotProps.data)" />
          <Button icon="pi pi-trash" outlined rounded severity="danger" class="p-button-sm" @click="store.confirmDeleteConference(slotProps.data)" />
        </div>
      </template>
    </Column>
  </DataTable>
  </template>
  
</Card>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import { useConferenceStore } from '@/stores/conferenceManagement';
import { useToast } from 'primevue/usetoast';
import { type Conference } from '@/types';

export default defineComponent({
name: 'ConferenceTable',
data() {
  return {
    store: useConferenceStore(),
    toast: useToast(),
    filters: {
      'global': { value: null, matchMode: FilterMatchMode.CONTAINS }
    }
  };
},
mounted() {
  this.store.fetchConferences();
},
methods: {
  formatDate(value: Date): string {
    if (value) {
      return new Date(value).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    }
    return '';
  },
  getStatusSeverity(isPublished: boolean): string {
    return isPublished ? 'success' : 'warning';
  },
  onEditPagesClick(conference: Conference) {
    this.toast.add({
      severity: 'info',
      summary: 'Info',
      detail: `Conference pages editor for "${conference.name}" will be implemented in future releases.`,
      life: 3000
    });
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