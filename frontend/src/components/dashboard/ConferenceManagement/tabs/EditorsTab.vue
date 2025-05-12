<template>
  <div class="editors-tab">
    <!-- Editors management section -->
    <div class="mb-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Conference Editors</h2>
        <Button 
          label="Add Editor" 
          icon="pi pi-plus" 
          @click="openAddEditorsDialog"
          v-if="conferenceId"
        />
      </div>

      <!-- Editors DataTable -->
      <div class="flex flex-wrap gap-3 mb-4">
        <span class="p-input-icon-left w-full">
          <i class="pi pi-search" />
          <InputText
            v-model="filters.search"
            placeholder="Search editors..."
            class="w-full"
            @input="debouncedSearchEditors"
          />
        </span>

        <Dropdown
          v-model="filters.university_id"
          :options="universities"
          optionLabel="full_name"
          optionValue="id"
          placeholder="Filter by university"
          class="w-full"
          :showClear="true"
          @change="loadEditors"
        />
      </div>
      
      <DataTable
        :value="editors"
        :loading="loading"
        dataKey="id"
        :paginator="true"
        :rows="filters.per_page"
        :rowsPerPageOptions="[5, 10, 25, 50]"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        :sortField="filters.sort_field"
        :sortOrder="filters.sort_order === 'desc' ? -1 : 1"
        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} editors"
        responsiveLayout="scroll"
        class="p-datatable-sm"
        @page="onPage"
        @sort="onSort"
      >
        <template #empty>
          <div class="text-center p-4">
            No editors found for this conference.
          </div>
        </template>
        <template #loading>
          <div class="text-center p-4">
            Loading editors data...
          </div>
        </template>
        <Column field="name" header="Name" sortable />
        <Column field="email" header="Email" sortable />
        <Column field="university.full_name" header="University" sortable>
          <template #body="slotProps">
            {{ slotProps.data.university?.full_name || '-' }}
          </template>
        </Column>
        <Column field="pivot.created_at" header="Assigned At" sortable>
          <template #body="slotProps">
            {{ formatDate(slotProps.data.pivot?.created_at) }}
          </template>
        </Column>
        <Column field="pivot.assigned_by_user.name" header="Assigned By" sortable>
          <template #body="slotProps">
            {{ slotProps.data.pivot?.assigned_by_user?.name || '-' }}
          </template>
        </Column>
        <Column headerStyle="width: 100px">
          <template #body="slotProps">
            <Button 
              icon="pi pi-trash" 
              class="p-button-text p-button-rounded p-button-danger" 
              @click="confirmDetachEditor(slotProps.data)"
              v-tooltip.top="'Remove editor'"
            />
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Add Editors Dialog -->
    <Dialog 
      v-model:visible="addEditorsDialogVisible" 
      header="Add Conference Editors" 
      :style="{ width: '70vw' }"
      modal
    >
      <div class="mb-4">
        <div class="flex flex-wrap gap-3 mb-2">
          <span class="p-input-icon-left w-full">
            <i class="pi pi-search" />
            <InputText
              v-model="unattachedFilters.search"
              placeholder="Search editors"
              class="w-full"
              @input="debouncedSearchUnattached"
            />
          </span>

          <Dropdown
            v-model="unattachedFilters.university_id"
            :options="universities"
            optionLabel="full_name"
            optionValue="id"
            placeholder="Filter by university"
            class="w-full"
            :showClear="true"
            @change="loadUnattachedEditors"
          />
        </div>
      </div>

      <DataTable
        :value="unattachedEditors"
        :loading="loadingUnattached"
        dataKey="id"
        :paginator="true"
        :rows="unattachedFilters.per_page"
        :rowsPerPageOptions="[5, 10, 25, 50]"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        :sortField="unattachedFilters.sort_field"
        :sortOrder="unattachedFilters.sort_order === 'desc' ? -1 : 1"
        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} available editors"
        responsiveLayout="scroll"
        class="p-datatable-sm"
        @sort="onSortUnattached"
        @page="onPageUnattached"
      >
        <template #empty>
          <div class="text-center p-4">
            No available editors found.
          </div>
        </template>
        <template #loading>
          <div class="text-center p-4">
            Loading available editors...
          </div>
        </template>
        <Column field="name" header="Name" sortable />
        <Column field="email" header="Email" sortable />
        <Column field="university.full_name" header="University" sortable>
          <template #body="slotProps">
            {{ slotProps.data.university?.full_name || '-' }}
          </template>
        </Column>
        <Column headerStyle="width: 100px">
          <template #body="slotProps">
            <Button 
              icon="pi pi-plus" 
              class="p-button-text p-button-rounded p-button-success" 
              @click="confirmAttachEditor(slotProps.data)"
              v-tooltip.top="'Add editor'"
            />
          </template>
        </Column>
      </DataTable>

      <template #footer>
        <Button 
          label="Close" 
          icon="pi pi-times" 
          @click="addEditorsDialogVisible = false" 
          class="p-button-text"
        />
      </template>
    </Dialog>

    <ConfirmDialog />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import type { User } from '@/types/user';
import type { University } from '@/types/university';
import apiService from '@/services/apiService';
import { useUniversityStore } from '@/stores/universityStore';
import debounce from 'lodash/debounce';
import { format, parseISO } from 'date-fns';
import type { Editor } from '@/types';

interface EditorFilters {
  search?: string;
  university_id?: number | null;
  sort_field?: string;
  sort_order?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}

export default defineComponent({
  name: 'EditorsTab',
  props: {
    conferenceId: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      editors: [] as Editor[],
      unattachedEditors: [] as User[],
      totalEditors: 0,
      totalUnattached: 0,
      loading: false,
      loadingUnattached: false,
      addEditorsDialogVisible: false,
      universities: [] as University[],
      universityStore: useUniversityStore(),
      filters: {
        search: '',
        university_id: null as number | null,
        sort_field: 'name',
        sort_order: 'asc' as 'asc' | 'desc',
        page: 1,
        per_page: 10
      } as EditorFilters,
      unattachedFilters: {
        search: '',
        university_id: null as number | null,
        sort_field: 'name',
        sort_order: 'asc' as 'asc' | 'desc',
        page: 1,
        per_page: 10
      } as EditorFilters,
      debouncedSearchEditors: null as unknown as () => void,
      debouncedSearchUnattached: null as unknown as () => void,
      toast: useToast(),
      confirm: useConfirm()
    };
  },
  watch: {
    conferenceId: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.loadEditors();
        }
      }
    }
  },
  mounted() {
    this.loadUniversities();
    this.debouncedSearchEditors = debounce(this.loadEditors, 300);
    this.debouncedSearchUnattached = debounce(this.loadUnattachedEditors, 300);
  },
  methods: {
    formatDate(dateString: string | undefined) {
      if (!dateString) return '-';
      try {
        return format(parseISO(dateString), 'MMM dd, yyyy HH:mm');
      } catch (e) {
        return dateString;
      }
    },
    
    async loadEditors() {
      if (!this.conferenceId) return;
      
      this.loading = true;
      try {
        const { search, university_id, sort_field, sort_order, page, per_page } = this.filters;
        const queryParams = new URLSearchParams();
        
        if (search) queryParams.append('search', search);
        if (university_id) queryParams.append('university_id', String(university_id));
        if (page) queryParams.append('page', String(page));
        if (per_page) queryParams.append('per_page', String(per_page));
        if (sort_field) queryParams.append('sort_field', sort_field);
        if (sort_order) queryParams.append('sort_order', sort_order);
        
        const url = `/v1/conferences/${this.conferenceId}/editors?${queryParams.toString()}`;
        const response = await apiService.get(url);
        
        if (response.data.success) {
          this.editors = response.data.payload;
          console.log('Editors loaded:', this.editors);
          this.editors.forEach(editor => {
            console.log(`Editor ${editor.name}:`, editor.pivot);
          });
          if (response.data.meta) {
            this.totalEditors = response.data.meta.total;
          }
        }
      } catch (error) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to load conference editors',
          life: 3000
        });
        console.error('Failed to load editors:', error);
      } finally {
        this.loading = false;
      }
    },
    
    async loadUnattachedEditors() {
      if (!this.conferenceId) return;
      
      this.loadingUnattached = true;
      try {
        const { search, university_id, sort_field, sort_order, page, per_page } = this.unattachedFilters;
        const queryParams = new URLSearchParams();
        
        if (search) queryParams.append('search', search);
        if (university_id) queryParams.append('university_id', String(university_id));
        if (page) queryParams.append('page', String(page));
        if (per_page) queryParams.append('per_page', String(per_page));
        if (sort_field) queryParams.append('sort_field', sort_field);
        if (sort_order) queryParams.append('sort_order', sort_order);
        
        const url = `/v1/conferences/${this.conferenceId}/editors/unattached?${queryParams.toString()}`;
        const response = await apiService.get(url);
        
        if (response.data.success) {
          this.unattachedEditors = response.data.payload;
          if (response.data.meta) {
            this.totalUnattached = response.data.meta.total;
          }
        }
      } catch (error) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to load available editors',
          life: 3000
        });
        console.error('Failed to load unattached editors:', error);
      } finally {
        this.loadingUnattached = false;
      }
    },
    
    async loadUniversities() {
      try {
        await this.universityStore.fetchUniversities();
        this.universities = this.universityStore.universities;
        console.log(this.universities);
      } catch (error) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to load universities',
          life: 3000
        });
        console.error('Failed to load universities:', error);
      }
    },
    
    openAddEditorsDialog() {
      this.addEditorsDialogVisible = true;
      this.unattachedFilters = {
        search: '',
        university_id: null,
        sort_field: 'name',
        sort_order: 'asc',
        page: 1,
        per_page: 10
      };
      this.loadUnattachedEditors();
    },
    
    confirmAttachEditor(editor: User) {
      this.confirm.require({
        message: `Are you sure you want to add ${editor.name} as an editor to this conference?`,
        header: 'Confirm Addition',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-success',
        accept: () => {
          this.attachEditor(editor);
        }
      });
    },
    
    async attachEditor(editor: User) {
      if (!this.conferenceId) return;
      
      try {
        const payload = {
          user_id: editor.id
        };
        
        const response = await apiService.post(`/v1/conferences/${this.conferenceId}/editors`, payload);
        
        if (response.data.success) {
          this.toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Editor added successfully',
            life: 3000
          });
          this.loadEditors();
          this.loadUnattachedEditors();
        } else {
          this.toast.add({
            severity: 'error',
            summary: 'Error',
            detail: response.data.message || 'Failed to add editor',
            life: 3000
          });
        }
      } catch (error) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to add editor',
          life: 3000
        });
        console.error('Failed to attach editor:', error);
      }
    },
    
    confirmDetachEditor(editor: User) {
      this.confirm.require({
        message: `Are you sure you want to remove ${editor.name} as an editor from this conference?`,
        header: 'Confirm Removal',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
          this.detachEditor(editor);
        }
      });
    },
    
    async detachEditor(editor: User) {
      if (!this.conferenceId) return;
      
      try {
        const response = await apiService.delete(`/v1/conferences/${this.conferenceId}/editors/${editor.id}`);
        
        if (response.data.success) {
          this.toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Editor removed successfully',
            life: 3000
          });
          this.loadEditors();
          this.loadUnattachedEditors();
        }
      } catch (error) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to remove editor',
          life: 3000
        });
        console.error('Failed to detach editor:', error);
      }
    },
    
    onSort(event: any) {
      this.filters.sort_field = event.sortField;
      this.filters.sort_order = event.sortOrder === 1 ? 'asc' : 'desc';
      this.loadEditors();
    },
    
    onPage(event: any) {
      this.filters.page = event.page + 1; // PrimeVue uses 0-based indexing, API uses 1-based
      this.filters.per_page = event.rows;
      this.loadEditors();
    },
    
    onSortUnattached(event: any) {
      this.unattachedFilters.sort_field = event.sortField;
      this.unattachedFilters.sort_order = event.sortOrder === 1 ? 'asc' : 'desc';
      this.loadUnattachedEditors();
    },
    
    onPageUnattached(event: any) {
      this.unattachedFilters.page = event.page + 1; // PrimeVue uses 0-based indexing, API uses 1-based
      this.unattachedFilters.per_page = event.rows;
      this.loadUnattachedEditors();
    }
  }
});
</script>