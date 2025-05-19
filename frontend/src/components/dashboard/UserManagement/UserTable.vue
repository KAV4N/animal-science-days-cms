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

          <Column selectionMode="multiple" style="width: 3rem" frozen alignFrozen="left" class="checkbox-column" :exportable="false"></Column>
          
          <Column field="name" header="Name" sortable style="min-width: 16rem"></Column>
          <Column field="email" header="Email" sortable style="min-width: 16rem"></Column>
          <Column header="Role" sortable :sortField="'roles'" style="min-width: 10rem">
              <template #body="slotProps">
              <Tag v-for="role in slotProps.data.roles" :key="role" 
                   :value="getRoleLabel(role)" :severity="getRoleSeverity(role)" 
                   class="mr-1" />
              </template>
              <template #csv="slotProps">
                  {{ slotProps.data.roles ? slotProps.data.roles.join(', ') : '' }}
              </template>
          </Column>
          <Column header="University" style="min-width: 16rem">
              <template #body="slotProps">
                {{ slotProps.data.university ? slotProps.data.university.full_name : 'Not Assigned' }}
              </template>
              <template #csv="slotProps">
                  {{ slotProps.data.university ? slotProps.data.university.full_name : 'Not Assigned' }}
              </template>
          </Column>
          <Column field="must_change_password" header="Password Status" style="min-width: 10rem">
              <template #body="slotProps">
                  <Tag :value="slotProps.data.must_change_password ? 'Needs Change' : 'Changed'" 
                       :severity="slotProps.data.must_change_password ? 'warning' : 'success'" />
              </template>
              <template #csv="slotProps">
                  {{ slotProps.data.must_change_password ? 'Needs Change' : 'Changed' }}
              </template>
          </Column>
          <Column field="created_at" header="Created" sortable style="min-width: 10rem">
              <template #body="slotProps">
              {{ formatDate(slotProps.data.created_at) }}
              </template>
          </Column>
          <Column field="updated_at" header="Updated" sortable style="min-width: 10rem">
              <template #body="slotProps">
              {{ formatDate(slotProps.data.updated_at) }}
              </template>
          </Column>
          
          <Column :exportable="false" style="min-width: 8rem" frozen alignFrozen="right" class="action-buttons-column">
              <template #header>
                <div class="text-center">Actions</div>
              </template>
              <template #body="slotProps">
                <div class="flex justify-center gap-2">
                  <Button icon="pi pi-pencil" outlined rounded class="p-button-sm" @click="editUser(slotProps.data)" 
                    :disabled="!canEditUser(slotProps.data)" />
                  <Button icon="pi pi-trash" outlined rounded severity="danger" class="p-button-sm" 
                    @click="confirmDeleteUser(slotProps.data)" 
                    :disabled="!canDeleteUser(slotProps.data)" />
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
import { useAuthStore } from '@/stores/authStore';
import type { User } from '@/types/user';

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
    authStore: useAuthStore(),
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
    
    return this.users.filter(user => user.roles && user.roles.includes(this.selectedRole!.value!));
  },
  
  isCurrentUserSuperAdmin() {
    return this.authStore.hasRole('super_admin');
  },
  
  isCurrentUserAdmin() {
    return this.authStore.hasRole('admin');
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
  isCurrentUser(userId: number): boolean {
    return userId === this.authStore.currentUser?.id;
  },
  
  canEditUser(user: User): boolean {
    // Super admin can edit anyone except other super admins
    if (this.isCurrentUserSuperAdmin) {
      return !user.roles.includes('super_admin') || this.isCurrentUser(user.id);
    }
    
    // Admin can only edit editors, not super admins or other admins
    if (this.isCurrentUserAdmin) {
      return !user.roles.includes('super_admin') && !user.roles.includes('admin');
    }
    
    // Others can't edit anyone
    return false;
  },
  
  canDeleteUser(user: User): boolean {
    // Can't delete yourself
    if (this.isCurrentUser(user.id)) {
      return false;
    }
    
    // Super admin can delete anyone except other super admins
    if (this.isCurrentUserSuperAdmin) {
      return !user.roles.includes('super_admin');
    }
    
    // Admin can only delete editors, not super admins or other admins
    if (this.isCurrentUserAdmin) {
      return !user.roles.includes('super_admin') && !user.roles.includes('admin');
    }
    
    // Others can't delete anyone
    return false;
  },
  
  formatDate(value: string): string {
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
    if (this.canEditUser(user)) {
      this.$emit('edit-user', user);
    }
  },
  
  confirmDeleteUser(user: User) {
    if (this.canDeleteUser(user)) {
      this.$emit('delete-user', user);
    }
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