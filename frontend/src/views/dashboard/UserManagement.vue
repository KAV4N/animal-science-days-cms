// components/dashboard/UserManagement/UserManagementView.vue
<template>
  <div>
    <UserToolbar 
      :has-selected-users="!!selectedUsers && selectedUsers.length > 0"
      @new-user="openNewUser"
      @delete-selected="confirmDeleteSelected"
      @export="exportTable"
    />

    <UserTable 
      :users="store.users"
      @edit-user="editUser"
      @delete-user="confirmDeleteUser"
      @selection-change="handleSelectionChange"
      ref="userTable"
    />

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
    
    <Toast />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useUserManagementStore, type User } from '@/stores/userManagement';
import UserTable from '@/components/dashboard/UserManagement/UserTable.vue';
import UserDialog from '@/components/dashboard/UserManagement/UserDialog.vue';
import DeleteConfirmDialog from '@/components/dashboard/UserManagement/DeleteConfirmDialog.vue';
import UserToolbar from '@/components/dashboard/UserManagement/UserToolbar.vue';

export default defineComponent({
  name: 'UserManagementView',
  
  components: {
    UserTable,
    UserDialog,
    DeleteConfirmDialog,
    UserToolbar
  },
  
  data() {
    return {
      store: useUserManagementStore(),
      userDialog: false,
      deleteUserDialog: false,
      deleteUsersDialog: false,
      selectedUser: null as User | null,
      selectedUsers: null as User[] | null,
      currentUserRole: 'super_admin'
    };
  },
  
  async mounted() {
    try {
      await this.store.fetchUsers();
    } catch (error: any) {
      this.$toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Failed to load users',
        life: 3000
      });
    }
  },
  
  methods: {
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
        if (userData.id) {
          // Update existing user
          const updateData = {
            name: userData.name,
            email: userData.email,
            role: userData.role
          };
          
          if (userData.password) {
            Object.assign(updateData, { password: userData.password });
          }
          
          await this.store.updateUser(userData.id, updateData);
          this.$toast.add({
            severity: 'success',
            summary: 'Successful',
            detail: 'User Updated',
            life: 3000
          });
        } else {
          // Create new user
          await this.store.createUser({
            name: userData.name,
            email: userData.email,
            role: userData.role,
            password: userData.password as string
          });
          
          this.$toast.add({
            severity: 'success',
            summary: 'Successful',
            detail: 'User Created',
            life: 3000
          });
        }
      } catch (error: any) {
        this.$toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.message || 'An error occurred',
          life: 3000
        });
      }
    },
    
    async deleteUser() {
      if (!this.selectedUser) return;
      
      try {
        await this.store.deleteUser(this.selectedUser.id);
        
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
          detail: error.message || 'An error occurred while deleting user',
          life: 3000
        });
      }
    },
    
    async deleteSelectedUsers() {
      if (!this.selectedUsers || this.selectedUsers.length === 0) {
        return;
      }
      
      try {
        // Filter out current user and super_admin users
        const usersToDelete = this.selectedUsers.filter(
          user => user.id !== '1' && user.role !== 'super_admin'
        );
        
        if (usersToDelete.length === 0) {
          this.$toast.add({
            severity: 'warn',
            summary: 'Warning',
            detail: 'No eligible users to delete',
            life: 3000
          });
          return;
        }
        
        const userIds = usersToDelete.map(user => user.id);
        await this.store.deleteMultipleUsers(userIds);
        
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
          detail: error.message || 'An error occurred while deleting users',
          life: 3000
        });
      }
    },
    
    exportTable() {
      if (this.$refs.userTable) {
        (this.$refs.userTable as any).exportCSV();
      }
    }
  }
});
</script>