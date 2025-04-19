// components/dashboard/UserManagement/UserTable.vue
<template>
    <Card class="card rounded-md">
        <template #content>
            <DataTable
            ref="dt"
            v-model:selection="selectedUsers"
            :value="filteredUsers"
            dataKey="id"
            :paginator="true"
            :rows="10"
            :filters="filters"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
            :rowsPerPageOptions="[5, 10, 25]"
            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} users"
            class="fixed-columns-table"
            scrollable
            scrollHeight="flex"
            >
            <template #header>
                <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">Manage Users</h4>
                <div class="flex items-center gap-2">
                    <Dropdown v-model="selectedRole" :options="roleFilterOptions" optionLabel="label" 
                    placeholder="Filter by Role" class="w-48" />
                    <IconField>
                    <InputIcon>
                        <i class="pi pi-search" />
                    </InputIcon>
                    <InputText v-model="filters['global'].value" placeholder="Search..." />
                    </IconField>
                </div>
                </div>
            </template>

            <!-- Fixed left column -->
            <Column selectionMode="multiple" style="width: 3rem" frozen alignFrozen="left" class="checkbox-column" :exportable="false"></Column>
            
            <Column field="name" header="Name" sortable style="min-width: 16rem"></Column>
            <Column field="email" header="Email" sortable style="min-width: 16rem"></Column>
            <Column field="role" header="Role" sortable style="min-width: 10rem">
                <template #body="slotProps">
                <Tag :value="getRoleLabel(slotProps.data.role)" :severity="getRoleSeverity(slotProps.data.role)" />
                </template>
            </Column>
            <Column field="createdAt" header="Created" sortable style="min-width: 10rem">
                <template #body="slotProps">
                {{ formatDate(slotProps.data.createdAt) }}
                </template>
            </Column>
            <Column field="updatedAt" header="Updated" sortable style="min-width: 10rem">
                <template #body="slotProps">
                {{ formatDate(slotProps.data.updatedAt) }}
                </template>
            </Column>
            
            <!-- Fixed right column -->
            <Column :exportable="false" style="min-width: 8rem" frozen alignFrozen="right" class="action-buttons-column">
                <template #header>
                  <div class="text-center">Actions</div>
                </template>
                <template #body="slotProps">
                  <div class="flex justify-center gap-2">
                    <Button icon="pi pi-pencil" outlined rounded class="p-button-sm" @click="editUser(slotProps.data)" />
                    <Button icon="pi pi-trash" outlined rounded severity="danger" class="p-button-sm" 
                      @click="confirmDeleteUser(slotProps.data)" 
                      :disabled="isCurrentUser(slotProps.data.id) || slotProps.data.role === 'super_admin'" />
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
import { useUserManagementStore, type User } from '@/stores/userManagement';

export default defineComponent({
  name: 'UserTable',
  
  props: {
    users: {
      type: Array as () => User[],
      required: true
    }
  },
  
  emits: ['edit-user', 'delete-user', 'selection-change'],
  
  data() {
    return {
      store: useUserManagementStore(),
      selectedUsers: null as User[] | null,
      filters: {},
      selectedRole: null as { label: string, value: string | null } | null,
      roleFilterOptions: [
        { label: 'All Roles', value: null },
        { label: 'Super Admin', value: 'super_admin' },
        { label: 'Admin', value: 'admin' },
        { label: 'Editor', value: 'editor' }
      ]
    };
  },
  
  computed: {
    filteredUsers() {
      if (!this.selectedRole || !this.selectedRole.value) {
        return this.users;
      }
      
      return this.users.filter(user => user.role === this.selectedRole.value);
    }
  },
  
  created() {
    this.initFilters();
  },
  
  watch: {
    selectedUsers(newValue) {
      this.$emit('selection-change', newValue);
    }
  },
  
  methods: {
    isCurrentUser(userId: string): boolean {
      // For demo, assume user ID 1 is the current user (super_admin)
      return userId === '1';
    },
    
    formatDate(value: Date): string {
      if (value) {
        return new Date(value).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: '2-digit'
        });
      }
      return '';
    },
    
    editUser(user: User) {
      this.$emit('edit-user', user);
    },
    
    confirmDeleteUser(user: User) {
      this.$emit('delete-user', user);
    },
    
    initFilters() {
      this.filters = {
        'global': { value: null, matchMode: FilterMatchMode.CONTAINS }
      };
    },
    
    getRoleLabel(role: string): string {
      switch (role) {
        case 'super_admin':
          return 'Super Admin';
        case 'admin':
          return 'Admin';
        case 'editor':
          return 'Editor';
        default:
          return role;
      }
    },
    
    getRoleSeverity(role: string): string {
      switch (role) {
        case 'super_admin':
          return 'danger';
        case 'admin':
          return 'warning';
        case 'editor':
          return 'info';
        default:
          return 'secondary';
      }
    },
    
    exportCSV() {
      if (this.$refs.dt) {
        (this.$refs.dt as any).exportCSV();
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
  background-color: white;
  z-index: 1;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
}

.action-buttons-column {
  background-color: white;
  z-index: 1;
  box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
}

@media screen and (max-width: 960px) {
  :deep(.p-datatable-wrapper) {
    overflow-x: auto;
  }
  
  :deep(.p-datatable-tbody) tr td:first-child {
    position: sticky !important;
    left: 0 !important;
    background-color: white !important;
    z-index: 1 !important;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1) !important;
  }
  
  :deep(.p-datatable-tbody) tr td:last-child {
    position: sticky !important;
    right: 0 !important;
    background-color: white !important;
    z-index: 1 !important;
    box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1) !important;
  }
}
</style>