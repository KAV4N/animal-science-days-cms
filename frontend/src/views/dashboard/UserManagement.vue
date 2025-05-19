<template>
  <div>
    <UserToolbar 
      :has-selected-users="!!selectedUsers && selectedUsers.length > 0"
      @new-user="openNewUser"
      @delete-selected="confirmDeleteSelected"
      @export="exportTable"
    />

    <Card class="card rounded-md">
      <template #content>
        <DataTable
          ref="dt"
          v-model:selection="selectedUsers"
          :value="users"
          dataKey="id"
          :paginator="true"
          :rows="filters.per_page"
          :rowsPerPageOptions="[5, 10, 25]"
          :totalRecords="meta?.total || 0"
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
          :sortField="filters.sort_field"
          :sortOrder="filters.sort_order === 'desc' ? -1 : 1"
          currentPageReportTemplate="Showing {first} to {last} of {totalRecords} users"
          responsiveLayout="stack"
          breakpoint="960px"
          class="p-datatable-sm rounded fixed-columns-table"
          :loading="loading"
          scrollable
          scrollHeight="flex"
          @page="onPage"
          @sort="onSort"
        >
          <template #header>
            <div class="flex flex-column md:flex-row md:justify-between md:items-center gap-2">
              <h4 class="m-0">Manage Users</h4>
              <div class="flex items-center gap-2">
                <Dropdown v-model="filters.roles" :options="roleFilterOptions" optionLabel="label" 
                  optionValue="value" placeholder="Filter by Role" class="w-48" @change="onRoleFilterChange" />
                
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
            </div>
          </template>

          <!-- Fixed left column -->
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
          
          <!-- Fixed right column -->
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

          <template #empty>
            <div class="p-4 text-center">
              <p>No users found.</p>
            </div>
          </template>
        </DataTable>
      </template>
    </Card>

    <UserDialog 
      v-model="userDialog" 
      :user="selectedUser"
      :current-user-role="currentUserRole"
      @save="saveUser"
    />

    <DeleteConfirmDialog
      v-model="deleteUserDialog"
      :user="selectedUser"
      :multiple="false"
      @confirm="deleteUser"
    />

    <DeleteConfirmDialog
      v-model="deleteUsersDialog"
      :multiple="true"
      @confirm="deleteSelectedUsers"
    />
    
    <Dialog v-model:visible="showPasswordDialog" header="User Password" :style="{ width: '450px' }">
      <div class="flex flex-col gap-4">
        <div class="p-3 border rounded bg-gray-50">
          <p class="font-bold">Password has been generated for {{ passwordUserName }}</p>
          <p class="mt-2">Please save this password. It won't be displayed again.</p>
          <div class="mt-2 p-2 bg-white border rounded font-mono text-center">
            {{ generatedPassword }}
          </div>
        </div>
        <div class="flex gap-2 justify-between">
          <Button label="Export to Text File" icon="pi pi-download" @click="exportPassword" />
          <Button label="Close" icon="pi pi-times" outlined @click="closePasswordDialog" />
        </div>
      </div>
    </Dialog>
    
    <Toast />
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/authStore'; 
import UserTable from '@/components/dashboard/UserManagement/UserTable.vue';
import UserDialog from '@/components/dashboard/UserManagement/UserDialog.vue';
import DeleteConfirmDialog from '@/components/dashboard/UserManagement/DeleteConfirmDialog.vue';
import UserToolbar from '@/components/dashboard/UserManagement/UserToolbar.vue';
import apiService from '@/services/apiService';
import { type User } from '@/types/user';
import debounce from 'lodash/debounce';

interface UserFilters {
  search: string;
  roles: string | null;
  university_id: number | null;
  sort_field: string;
  sort_order: string;
  page: number;
  per_page: number;
}

interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export default defineComponent({
  name: 'UserManagementView',
  
  components: {
    UserTable,
    UserDialog,
    DeleteConfirmDialog,
    UserToolbar
  },
  
  setup() {
    const authStore = useAuthStore();
    const users = ref<User[]>([]);
    const meta = ref<PaginationMeta | null>(null);
    const loading = ref<boolean>(false);
    const selectedUsers = ref<User[] | null>(null);
    const userDialog = ref<boolean>(false);
    const deleteUserDialog = ref<boolean>(false);
    const deleteUsersDialog = ref<boolean>(false);
    const showPasswordDialog = ref<boolean>(false);
    const passwordUserName = ref<string>('');
    const passwordUserEmail = ref<string>('');
    const generatedPassword = ref<string>('');
    const selectedUser = ref<User | null>(null);
    const currentUserRole = ref<string>('');
    
    const filters = ref<UserFilters>({
      search: '',
      roles: null,
      university_id: null,
      sort_field: 'created_at',
      sort_order: 'desc',
      page: 1,
      per_page: 10
    });
    
    const roleFilterOptions = [
      { label: 'All Roles', value: null },
      { label: 'Super Admin', value: 'super_admin' },
      { label: 'Admin', value: 'admin' },
      { label: 'Editor', value: 'editor' }
    ];
    
    const debouncedSearch = debounce(() => {
      filters.value.page = 1; // Reset to first page on search
      fetchUsers();
    }, 300);
    
    const fetchUsers = async () => {
      loading.value = true;
      try {
        // Build query parameters
        const params = new URLSearchParams();
        
        if (filters.value.search) {
          params.append('search', filters.value.search);
        }
        
        if (filters.value.roles) {
          params.append('roles', filters.value.roles);
        }
        
        if (filters.value.university_id) {
          params.append('university_id', filters.value.university_id.toString());
        }
        
        params.append('sort_field', filters.value.sort_field);
        params.append('sort_order', filters.value.sort_order);
        params.append('page', filters.value.page.toString());
        params.append('per_page', filters.value.per_page.toString());
        
        const response = await apiService.get(`/v1/users?${params.toString()}`);
        users.value = response.data.payload;
        meta.value = response.data.meta;
      } catch (error: any) {
        console.error('Failed to fetch users:', error);
      } finally {
        loading.value = false;
      }
    };
    
    const onPage = (event: any) => {
      filters.value.page = event.page + 1; // PrimeVue uses 0-based indexing, API uses 1-based
      filters.value.per_page = event.rows;
      fetchUsers();
    };
    
    const onSort = (event: any) => {
      filters.value.sort_field = event.sortField;
      filters.value.sort_order = event.sortOrder === 1 ? 'asc' : 'desc';
      fetchUsers();
    };
    
    const onRoleFilterChange = () => {
      filters.value.page = 1; // Reset to first page when filter changes
      fetchUsers();
    };
    
    const clearSearch = () => {
      filters.value.search = '';
      filters.value.page = 1;
      fetchUsers();
    };
    
    onMounted(async () => {
      if (!authStore.currentUser) {
        await authStore.fetchCurrentUser();
      }
      
      if (authStore.currentUser?.roles?.length > 0) {
        currentUserRole.value = authStore.currentUser.roles[0];
      }
      
      fetchUsers();
    });
    
    return {
      users,
      meta,
      loading,
      selectedUsers,
      userDialog,
      deleteUserDialog,
      deleteUsersDialog,
      showPasswordDialog,
      passwordUserName,
      passwordUserEmail,
      generatedPassword,
      selectedUser,
      currentUserRole,
      filters,
      roleFilterOptions,
      authStore,
      debouncedSearch,
      fetchUsers,
      onPage,
      onSort,
      onRoleFilterChange,
      clearSearch
    };
  },
  
  methods: {
    isCurrentUser(userId: number): boolean {
      return userId === this.authStore.currentUser?.id;
    },
    
    canEditUser(user: User): boolean {
      // Super admin can edit anyone except other super admins
      if (this.authStore.hasRole('super_admin')) {
        return !user.roles.includes('super_admin') || this.isCurrentUser(user.id);
      }
      
      // Admin can only edit editors, not super admins or other admins
      if (this.authStore.hasRole('admin')) {
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
      if (this.authStore.hasRole('super_admin')) {
        return !user.roles.includes('super_admin');
      }
      
      // Admin can only delete editors, not super admins or other admins
      if (this.authStore.hasRole('admin')) {
        return !user.roles.includes('super_admin') && !user.roles.includes('admin');
      }
      
      // Others can't delete anyone
      return false;
    },
    
    openNewUser() {
      this.selectedUser = null;
      this.userDialog = true;
    },
    
    editUser(user: User) {
      this.selectedUser = { ...user };
      this.userDialog = true;
    },
    
    confirmDeleteUser(user: User) {
      this.selectedUser = user;
      this.deleteUserDialog = true;
    },
    
    confirmDeleteSelected() {
      this.deleteUsersDialog = true;
    },
    
    handleSelectionChange(users: User[] | null) {
      this.selectedUsers = users;
    },
    
    async saveUser(userData: any) {
      try {
        let response;
        
        if (userData.id) {
          response = await apiService.put(`/v1/users/${userData.id}`, userData);
        } else {
          response = await apiService.post('/v1/users', userData);
        }
        
        const responseData = response.data.payload;
        
        if (responseData.generated_password) {
          this.passwordUserName = userData.name;
          this.passwordUserEmail = userData.email;
          this.generatedPassword = responseData.generated_password;
          this.showPasswordDialog = true;
        }
        
        await this.fetchUsers();
        
        this.$toast.add({
          severity: 'success',
          summary: 'Successful',
          detail: userData.id ? 'User Updated' : 'User Created',
          life: 3000
        });
      } catch (error: any) {
        this.$toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.data?.message || 'An error occurred',
          life: 3000
        });
      }
    },
    
    async deleteUser() {
      if (!this.selectedUser) return;
      
      try {
        await apiService.delete(`/v1/users/${this.selectedUser.id}`);
        
        await this.fetchUsers();
        
        this.$toast.add({
          severity: 'success',
          summary: 'Successful',
          detail: 'User Deleted',
          life: 3000
        });
        
        this.selectedUser = null;
      } catch (error: any) {
        this.$toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.data?.message || 'An error occurred while deleting user',
          life: 3000
        });
      }
    },
    
    async deleteSelectedUsers() {
      if (!this.selectedUsers || this.selectedUsers.length === 0) {
        return;
      }
      
      try {
        const currentUserId = this.authStore.currentUser?.id;
        const isSuperAdmin = this.authStore.hasRole('super_admin');
        const isAdmin = this.authStore.hasRole('admin');
        
        // Filter out users that cannot be deleted based on roles and permissions
        const usersToDelete = this.selectedUsers.filter(user => {
          const isSelf = user.id === currentUserId;
          const isSuperAdminUser = user.roles.includes('super_admin');
          const isAdminUser = user.roles.includes('admin');
          
          // Current user can't delete themselves
          if (isSelf) {
            return false;
          }
          
          // Only super admin can delete admins
          if (isAdminUser && !isSuperAdmin) {
            return false;
          }
          
          // No one can delete super admins
          if (isSuperAdminUser) {
            return false;
          }
          
          return true;
        });
        
        if (usersToDelete.length === 0) {
          this.$toast.add({
            severity: 'warn',
            summary: 'Warning',
            detail: 'No eligible users to delete',
            life: 3000
          });
          return;
        }
        
        const deletePromises = usersToDelete.map(user => 
          apiService.delete(`/v1/users/${user.id}`)
        );
        
        await Promise.all(deletePromises);
        
        await this.fetchUsers();
        
        this.selectedUsers = null;
        
        this.$toast.add({
          severity: 'success',
          summary: 'Successful',
          detail: `${usersToDelete.length} users deleted`,
          life: 3000
        });
      } catch (error: any) {
        this.$toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.data?.message || 'An error occurred while deleting users',
          life: 3000
        });
      }
    },
    
    exportTable() {
      if (this.$refs.dt) {
        (this.$refs.dt as any).exportCSV();
      }
    },
    
    exportPassword() {
      if (!this.generatedPassword) return;
      
      const content = 
        `Username: ${this.passwordUserName}\n` +
        `Email: ${this.passwordUserEmail}\n` +
        `Password: ${this.generatedPassword}\n` +
        `Date: ${new Date().toLocaleString()}`;
      
      const blob = new Blob([content], { type: 'text/plain' });
      const url = URL.createObjectURL(blob);
      
      const link = document.createElement('a');
      link.href = url;
      link.download = `credentials_${this.passwordUserName.replace(/\s+/g, '_')}.txt`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    },
    
    closePasswordDialog() {
      this.showPasswordDialog = false;
      this.generatedPassword = '';
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