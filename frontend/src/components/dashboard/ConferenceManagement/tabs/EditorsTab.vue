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
            placeholder="Search editors by name/email..."
            class="w-full"
            @input="debouncedSearchEditors"
          />
        </span>

        <Select
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
        :value="sortedEditors"
        :loading="loading"
        dataKey="id"
        :paginator="true"
        :rows="filters.per_page"
        :totalRecords="totalEditors"
        :lazy="true"
        :rowsPerPageOptions="[5, 10, 25, 50]"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        :sortField="frontendSortField"
        :sortOrder="frontendSortOrder"
        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} editors"
        responsiveLayout="scroll"
        class="p-datatable-sm"
        @page="onPage"
        @sort="onFrontendSort"
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
        <Column header="University" sortable sortField="university.full_name">
          <template #body="slotProps">
            {{ slotProps.data.university?.full_name || '-' }}
          </template>
        </Column>
        <Column header="Assigned At" sortable sortField="pivot.created_at">
          <template #body="slotProps">
            {{ formatDate(slotProps.data.pivot?.created_at) }}
          </template>
        </Column>
        <Column header="Assigned By" sortable sortField="pivot.assigned_by_user.name">
          <template #body="slotProps">
            {{ slotProps.data.pivot?.assigned_by_user?.name || '-' }}
          </template>
        </Column>
        <Column headerStyle="width: 100px">
          <template #body="slotProps">
            <Button
              icon="pi pi-trash"
              class="p-button-text p-button-rounded p-button-danger"
              @click="detachEditor(slotProps.data)"
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
              placeholder="Search editors by name/email"
              class="w-full"
              @input="debouncedSearchUnattached"
            />
          </span>

          <Select
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
        :value="sortedUnattachedEditors"
        :loading="loadingUnattached"
        dataKey="id"
        :paginator="true"
        :rows="unattachedFilters.per_page"
        :totalRecords="totalUnattached"
        :lazy="true"
        :rowsPerPageOptions="[5, 10, 25, 50]"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        :sortField="frontendSortFieldUnattached"
        :sortOrder="frontendSortOrderUnattached"
        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} available editors"
        responsiveLayout="scroll"
        class="p-datatable-sm"
        @sort="onFrontendSortUnattached"
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
        <Column header="University" sortable sortField="university.full_name">
          <template #body="slotProps">
            {{ slotProps.data.university?.full_name || '-' }}
          </template>
        </Column>
        <Column headerStyle="width: 100px">
          <template #body="slotProps">
            <Button
              icon="pi pi-plus"
              class="p-button-text p-button-rounded p-button-success"
              @click="attachEditor(slotProps.data)"
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
  </div>
</template>

<script lang="ts">
import { defineComponent, computed } from 'vue';
import { useToast } from 'primevue/usetoast';
import type { User } from '@/types/user';
import type { University } from '@/types/university';
import apiService from '@/services/apiService';
import { useUniversityStore } from '@/stores/universityStore';
import { useConferenceStore } from '@/stores/conferenceStore';
import debounce from 'lodash/debounce';
import { format, parseISO } from 'date-fns';
import type { Editor } from '@/types';

interface EditorFilters {
  search?: string;
  university_id?: number | null;
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
  setup() {
    // Helper function to get nested property values
    const getNestedProperty = (obj: any, path: string): any => {
      return path.split('.').reduce((current, key) => current?.[key], obj);
    };

    return {
      getNestedProperty
    };
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
      conferenceStore: useConferenceStore(),
      
      // Frontend sorting state for editors
      frontendSortField: 'name',
      frontendSortOrder: 1, // 1 for asc, -1 for desc
      
      // Frontend sorting state for unattached editors
      frontendSortFieldUnattached: 'name',
      frontendSortOrderUnattached: 1,
      
      filters: {
        search: '',
        university_id: null as number | null,
        page: 1,
        per_page: 10
      } as EditorFilters,
      unattachedFilters: {
        search: '',
        university_id: null as number | null,
        page: 1,
        per_page: 10
      } as EditorFilters,
      debouncedSearchEditors: null as unknown as () => void,
      debouncedSearchUnattached: null as unknown as () => void,
      toast: useToast()
    };
  },
  computed: {
    sortedEditors() {
      if (!this.editors || this.editors.length === 0) {
        return [];
      }

      const sorted = [...this.editors].sort((a: any, b: any) => {
        let aVal = this.getNestedProperty(a, this.frontendSortField);
        let bVal = this.getNestedProperty(b, this.frontendSortField);

        // Handle special cases for nested properties
        if (this.frontendSortField === 'university.full_name') {
          aVal = a.university?.full_name || '';
          bVal = b.university?.full_name || '';
        } else if (this.frontendSortField === 'pivot.created_at') {
          aVal = a.pivot?.created_at || '';
          bVal = b.pivot?.created_at || '';
        } else if (this.frontendSortField === 'pivot.assigned_by_user.name') {
          aVal = a.pivot?.assigned_by_user?.name || '';
          bVal = b.pivot?.assigned_by_user?.name || '';
        }

        // Handle null/undefined values
        if (aVal == null && bVal == null) return 0;
        if (aVal == null) return 1;
        if (bVal == null) return -1;

        // Handle dates
        if (this.frontendSortField === 'pivot.created_at') {
          aVal = new Date(aVal).getTime();
          bVal = new Date(bVal).getTime();
        }

        // Handle string comparison
        if (typeof aVal === 'string' && typeof bVal === 'string') {
          return this.frontendSortOrder * aVal.localeCompare(bVal);
        }

        // Handle numeric comparison
        if (aVal < bVal) return -1 * this.frontendSortOrder;
        if (aVal > bVal) return 1 * this.frontendSortOrder;
        return 0;
      });

      return sorted;
    },

    sortedUnattachedEditors() {
      if (!this.unattachedEditors || this.unattachedEditors.length === 0) {
        return [];
      }

      const sorted = [...this.unattachedEditors].sort((a: any, b: any) => {
        let aVal = this.getNestedProperty(a, this.frontendSortFieldUnattached);
        let bVal = this.getNestedProperty(b, this.frontendSortFieldUnattached);

        // Handle special cases for nested properties
        if (this.frontendSortFieldUnattached === 'university.full_name') {
          aVal = a.university?.full_name || '';
          bVal = b.university?.full_name || '';
        }

        // Handle null/undefined values
        if (aVal == null && bVal == null) return 0;
        if (aVal == null) return 1;
        if (bVal == null) return -1;

        // Handle string comparison
        if (typeof aVal === 'string' && typeof bVal === 'string') {
          return this.frontendSortOrderUnattached * aVal.localeCompare(bVal);
        }

        // Handle numeric comparison
        if (aVal < bVal) return -1 * this.frontendSortOrderUnattached;
        if (aVal > bVal) return 1 * this.frontendSortOrderUnattached;
        return 0;
      });

      return sorted;
    }
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
        const { search, university_id, page, per_page } = this.filters;
        const queryParams = new URLSearchParams();

        if (search) queryParams.append('search', search);
        if (university_id) queryParams.append('university_id', String(university_id));
        if (page) queryParams.append('page', String(page));
        if (per_page) queryParams.append('per_page', String(per_page));

        const url = `/v1/conference-management/conferences/${this.conferenceId}/editors?${queryParams.toString()}`;
        const response = await apiService.get(url);

        if (response.data.success) {
          this.editors = response.data.payload;
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
        const { search, university_id, page, per_page } = this.unattachedFilters;
        const queryParams = new URLSearchParams();

        if (search) queryParams.append('search', search);
        if (university_id) queryParams.append('university_id', String(university_id));
        if (page) queryParams.append('page', String(page));
        if (per_page) queryParams.append('per_page', String(per_page));

        const url = `/v1/conference-management/conferences/${this.conferenceId}/editors/unattached?${queryParams.toString()}`;
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
        page: 1,
        per_page: 10
      };
      // Reset sorting for unattached editors
      this.frontendSortFieldUnattached = 'name';
      this.frontendSortOrderUnattached = 1;
      this.loadUnattachedEditors();
    },

    async attachEditor(editor: User) {
      if (!this.conferenceId) return;

      try {
        const payload = {
          user_id: editor.id
        };

        const response = await apiService.post(`/v1/conference-management/conferences/${this.conferenceId}/editors`, payload);

        if (response.data.success) {
          this.toast.add({
            severity: 'success',
            summary: 'Success',
            detail: `${editor.name} has been added as an editor successfully`,
            life: 3000
          });

          await this.loadEditors();
          await this.loadUnattachedEditors();
          await this.refreshConferenceInStore();
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

    async detachEditor(editor: User) {
      if (!this.conferenceId) return;

      try {
        const response = await apiService.delete(`/v1/conference-management/conferences/${this.conferenceId}/editors/${editor.id}`);

        if (response.data.success) {
          this.toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Editor removed successfully',
            life: 3000
          });

          await this.loadEditors();
          await this.loadUnattachedEditors();
          await this.refreshConferenceInStore();
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

    async refreshConferenceInStore() {
      if (!this.conferenceId) return;

      try {
        const conference = await this.conferenceStore.fetchConference(this.conferenceId);
        if (conference.is_latest) {
          await this.conferenceStore.fetchLatestConference();
        }
        const filters = this.conferenceStore.getPaginationMeta
          ? { page: this.conferenceStore.getPaginationMeta.current_page }
          : {};

        await this.conferenceStore.fetchConferences(filters);
      } catch (error) {
        console.error('Failed to refresh conference data in store:', error);
      }
    },

    onFrontendSort(event: any) {
      this.frontendSortField = event.sortField;
      this.frontendSortOrder = event.sortOrder;
      // No API call needed - sorting happens in computed property
    },

    onPage(event: any) {
      this.filters.page = event.page + 1; // PrimeVue uses 0-based indexing, API uses 1-based
      this.filters.per_page = event.rows;
      this.loadEditors();
    },

    onFrontendSortUnattached(event: any) {
      this.frontendSortFieldUnattached = event.sortField;
      this.frontendSortOrderUnattached = event.sortOrder;
      // No API call needed - sorting happens in computed property
    },

    onPageUnattached(event: any) {
      this.unattachedFilters.page = event.page + 1; // PrimeVue uses 0-based indexing, API uses 1-based
      this.unattachedFilters.per_page = event.rows;
      this.loadUnattachedEditors();
    }
  }
});
</script>