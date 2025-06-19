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
          :lazy="true"
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
                <Select v-model="filters.roles" :options="getRoleFilterOptions()" optionLabel="label"
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
          <Column selectionMode="multiple" style="width: 3rem" frozen alignFrozen="left" class="checkbox-column border-e shadow" :exportable="false"></Column>

          <Column field="name" header="Name" sortable style="min-width: 16rem">
            <template #body="slotProps">
              <span v-tooltip.top="slotProps.data.name" class="truncate block">
                {{ truncateText(slotProps.data.name, 30) }}
              </span>
            </template>
          </Column>
          <Column field="email" header="Email" sortable style="min-width: 16rem">
            <template #body="slotProps">
              <span v-tooltip.top="slotProps.data.email" class="truncate block">
                {{ truncateText(slotProps.data.email, 25) }}
              </span>
            </template>
          </Column>
          <Column header="Role" :sortField="'roles'" style="min-width: 10rem">
            <template #body="slotProps">
              <Tag v-for="role in slotProps.data.roles" :key="role.id"
                  :value="getRoleLabel(role.name)" :severity="getRoleSeverity(role.name)"
                  class="mr-1" />
            </template>
            <template #csv="slotProps: any">
              {{ slotProps.data.roles ? slotProps.data.roles.map((role: Role) => role.name).join(', ') : '' }}
            </template>
          </Column>
          <Column header="University" style="min-width: 16rem">
            <template #body="slotProps">
              <span v-tooltip.top="slotProps.data.university ? slotProps.data.university.full_name : 'Not Assigned'" class="truncate block">
                {{ truncateText(slotProps.data.university ? slotProps.data.university.full_name : 'Not Assigned', 25) }}
              </span>
            </template>
            <template #csv="slotProps: any">
              {{ slotProps.data.university ? slotProps.data.university.full_name : 'Not Assigned' }}
            </template>
          </Column>
          <Column field="must_change_password" header="Password Status" style="min-width: 10rem">
            <template #body="slotProps">
              <Tag :value="slotProps.data.must_change_password ? 'Needs Change' : 'Changed'"
                    :severity="slotProps.data.must_change_password ? 'warning' : 'success'" />
            </template>
            <template #csv="slotProps: any">
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
          <Column :exportable="false" style="min-width: 8rem" frozen alignFrozen="right" class="action-buttons-column border-s shadow">
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
      :manageable-roles="getManageableRoles()"
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
import { defineComponent } from 'vue';
import UserDialog from '@/components/dashboard/UserManagement/UserDialog.vue';
import DeleteConfirmDialog from '@/components/dashboard/UserManagement/DeleteConfirmDialog.vue';
import UserToolbar from '@/components/dashboard/UserManagement/UserToolbar.vue';
import { useAuthStore } from '@/stores/authStore';
import apiService from '@/services/apiService';
import debounce from 'lodash/debounce';
import type { User, Role } from '@/types/user';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Tooltip from 'primevue/tooltip';

interface UserFilters {
  search: string;
  roles: string | null;
  university_id: number | null;
  sort_field: string;
  sort_order: string;
  page: number;
  per_page: number;
}

interface RoleOption {
  label: string;
  value: string | null;
}

interface Metadata {
  total: number;
  current_page: number;
  last_page: number;
}

export default defineComponent({
  name: 'UserManagementView',

  components: {
    UserDialog,
    DeleteConfirmDialog,
    UserToolbar,
    Card,
    DataTable,
    Column,
    Button,
    InputGroup,
    InputGroupAddon,
    InputText,
    Tag,
    Select,
    Dialog,
    Toast
  },

  directives: {
    tooltip: Tooltip
  },

  data() {
    return {
      users: [] as User[],
      meta: null as Metadata | null,
      loading: false,
      selectedUsers: null as User[] | null,
      userDialog: false,
      deleteUserDialog: false,
      deleteUsersDialog: false,
      showPasswordDialog: false,
      passwordUserName: '',
      passwordUserEmail: '',
      generatedPassword: '',
      selectedUser: null as User | null,
      currentUserRole: '',

      filters: {
        search: '',
        roles: null,
        university_id: null,
        sort_field: 'created_at',
        sort_order: 'desc',
        page: 1,
        per_page: 10
      } as UserFilters,

      debouncedSearch: () => {}
    };
  },

  computed: {
    authStore() {
      return useAuthStore();
    },

    canManageEditors(): boolean {
      return this.authStore.hasPermission('manage.editor');
    },

    canManageAdmins(): boolean {
      return this.authStore.hasPermission('manage.admin');
    }
  },

  created() {
    this.debouncedSearch = debounce(() => {
      this.filters.page = 1; // Reset to first page on search
      this.fetchUsers();
    }, 300);
  },

  async mounted() {
    if (!this.authStore.getUser) {
      await this.authStore.fetchCurrentUser();
    }

    if (this.authStore.getRoleNames.length > 0) {
      this.currentUserRole = this.authStore.getRoleNames[0];
    }

    // Apply initial filters based on permissions
    this.applyPermissionBasedFilters();

    this.fetchUsers();
  },

  methods: {
    applyPermissionBasedFilters(): void {
      // If user can only manage editors, filter by editor role initially
      if (this.canManageEditors && !this.canManageAdmins) {
        this.filters.roles = 'editor';
      }

      // If user can only manage admins, filter by admin role initially
      if (this.canManageAdmins && !this.canManageEditors) {
        this.filters.roles = 'admin';
      }
    },

    isCurrentUser(userId: number): boolean {
      return userId === this.authStore.getUser?.id;
    },

    canEditUser(user: User): boolean {
      // Can't edit yourself through this interface
      if (this.isCurrentUser(user.id)) {
        return false;
      }

      // Super admin can edit anyone except other super admins
      if (this.authStore.hasRole('super_admin')) {
        return !user.roles.some(role => role.name === 'super_admin');
      }

      // Check if user has admin role
      const isAdminUser = user.roles.some(role => role.name === 'admin');

      // Check if user has editor role
      const isEditorUser = user.roles.some(role => role.name === 'editor');

      // User with manage.admin permission can edit admins
      if (this.canManageAdmins && isAdminUser) {
        return true;
      }

      // User with manage.editor permission can edit editors
      if (this.canManageEditors && isEditorUser) {
        return true;
      }

      return false;
    },

    canDeleteUser(user: User): boolean {
      // Can't delete yourself
      if (this.isCurrentUser(user.id)) {
        return false;
      }

      // Super admin can delete anyone except other super admins
      if (this.authStore.hasRole('super_admin')) {
        return !user.roles.some(role => role.name === 'super_admin');
      }

      // Check if user has admin role
      const isAdminUser = user.roles.some(role => role.name === 'admin');

      // Check if user has editor role
      const isEditorUser = user.roles.some(role => role.name === 'editor');

      // User with manage.admin permission can delete admins
      if (this.canManageAdmins && isAdminUser) {
        return true;
      }

      // User with manage.editor permission can delete editors
      if (this.canManageEditors && isEditorUser) {
        return true;
      }

      return false;
    },

    getManageableRoles(): string[] {
      const roles: string[] = [];

      if (this.authStore.hasRole('super_admin')) {
        roles.push('admin', 'editor');
      } else if (this.canManageAdmins) {
        roles.push('admin');
      } else if (this.canManageEditors) {
        roles.push('editor');
      }

      return roles;
    },

    getRoleFilterOptions(): RoleOption[] {
      const options: RoleOption[] = [
        { label: 'All Roles', value: null }
      ];

      // Super admin can see all roles
      if (this.authStore.hasRole('super_admin')) {
        options.push(
          { label: 'Super Admin', value: 'super_admin' },
          { label: 'Admin', value: 'admin' },
          { label: 'Editor', value: 'editor' }
        );
      } else {
        // Users with manage.admin permission can see admin role
        if (this.canManageAdmins) {
          options.push({ label: 'Admin', value: 'admin' });
        }

        // Users with manage.editor permission can see editor role
        if (this.canManageEditors) {
          options.push({ label: 'Editor', value: 'editor' });
        }
      }

      return options;
    },

    truncateText(text: string | undefined, maxLength: number): string {
      if (!text) return '';
      if (text.length <= maxLength) return text;
      return text.substring(0, maxLength - 3) + '...';
    },

    openNewUser(): void {
      this.selectedUser = null;
      this.userDialog = true;
    },

    editUser(user: User): void {
      this.selectedUser = { ...user };
      this.userDialog = true;
    },

    confirmDeleteUser(user: User): void {
      this.selectedUser = user;
      this.deleteUserDialog = true;
    },

    confirmDeleteSelected(): void {
      this.deleteUsersDialog = true;
    },

    async fetchUsers(): Promise<void> {
      this.loading = true;
      try {
        // Build query parameters
        const params = new URLSearchParams();

        if (this.filters.search) {
          params.append('search', this.filters.search);
        }

        if (this.filters.roles) {
          params.append('roles', this.filters.roles);
        }

        if (this.filters.university_id) {
          params.append('university_id', this.filters.university_id.toString());
        }

        params.append('sort_field', this.filters.sort_field);
        params.append('sort_order', this.filters.sort_order);
        params.append('page', this.filters.page.toString());
        params.append('per_page', this.filters.per_page.toString());

        const response = await apiService.get(`/v1/user-management/users?${params.toString()}`);
        this.users = response.data.payload;
        this.meta = response.data.meta;
      } catch (error: any) {
        console.error('Failed to fetch users:', error);
      } finally {
        this.loading = false;
      }
    },

    onPage(event: any): void {
      this.filters.page = event.page + 1; // PrimeVue uses 0-based indexing, API uses 1-based
      this.filters.per_page = event.rows;
      this.fetchUsers();
    },

    onSort(event: any): void {
      this.filters.sort_field = event.sortField;
      this.filters.sort_order = event.sortOrder === 1 ? 'asc' : 'desc';
      this.fetchUsers();
    },

    onRoleFilterChange(): void {
      this.filters.page = 1; // Reset to first page when filter changes
      this.fetchUsers();
    },

    clearSearch(): void {
      this.filters.search = '';
      this.filters.page = 1;
      this.fetchUsers();
    },

    async saveUser(userData: User): Promise<void> {
      try {
        let response;

        if (userData.id) {
          response = await apiService.put(`/v1/user-management/users/${userData.id}`, userData);
        } else {
          response = await apiService.post('/v1/user-management/users', userData);
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

    async deleteUser(): Promise<void> {
      if (!this.selectedUser) return;

      try {
        await apiService.delete(`/v1/user-management/users/${this.selectedUser.id}`);

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

    async deleteSelectedUsers(): Promise<void> {
      if (!this.selectedUsers || this.selectedUsers.length === 0) {
        return;
      }

      try {
        const currentUserId = this.authStore.getUser?.id;

        // Filter out users that cannot be deleted based on roles and permissions
        const usersToDelete = this.selectedUsers.filter(user => {
          const isSelf = user.id === currentUserId;
          const isSuperAdminUser = user.roles.some(role => role.name === 'super_admin');
          const isAdminUser = user.roles.some(role => role.name === 'admin');
          const isEditorUser = user.roles.some(role => role.name === 'editor');

          // Current user can't delete themselves
          if (isSelf) {
            return false;
          }

          // No one can delete super admins
          if (isSuperAdminUser) {
            return false;
          }

          // User with manage.admin permission can delete admins
          if (isAdminUser) {
            return this.canManageAdmins;
          }

          // User with manage.editor permission can delete editors
          if (isEditorUser) {
            return this.canManageEditors;
          }

          return false;
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
          apiService.delete(`/v1/user-management/users/${user.id}`)
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

    exportTable(): void {
      if (this.$refs.dt) {
        (this.$refs.dt as any).exportCSV();
      }
    },

    exportPassword(): void {
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

    closePasswordDialog(): void {
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

    getRoleLabel(roleName: string): string {
      switch (roleName) {
        case 'super_admin':
          return 'Super Admin';
        case 'admin':
          return 'Admin';
        case 'editor':
          return 'Editor';
        default:
          return roleName;
      }
    },

    getRoleSeverity(roleName: string): string {
      switch (roleName) {
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

:deep(.p-datatable .p-datatable-tbody > tr > td) {
  @apply truncate;
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
