<template>
    <div>
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="New User" icon="pi pi-plus" class="mr-2" @click="openNew" />
                    <Button label="Delete" icon="pi pi-trash" severity="danger" outlined @click="confirmDeleteSelected" :disabled="!selectedUsers || !selectedUsers.length" />
                </template>

                <template #end>
                    <FileUpload mode="basic" accept=".json" :maxFileSize="1000000" label="Import" customUpload chooseLabel="Import" class="mr-2" auto :chooseButtonProps="{ severity: 'secondary' }" />
                    <Button label="Export" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
                </template>
            </Toolbar>

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

                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
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
                <Column :exportable="false" style="min-width: 8rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editUser(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteUser(slotProps.data)" 
                            :disabled="isCurrentUser(slotProps.data.id) || slotProps.data.role === 'super_admin'" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="userDialog" :style="{ width: '550px' }" header="User Details" :modal="true">
            <div class="flex flex-col gap-4 p-2">
                <div>
                    <label for="name" class="block font-bold mb-2">Name</label>
                    <InputText id="name" v-model.trim="user.name" required="true" autofocus :invalid="submitted && !user.name" class="w-full" />
                    <small v-if="submitted && !user.name" class="text-red-500">Name is required.</small>
                </div>
                
                <div>
                    <label for="email" class="block font-bold mb-2">Email</label>
                    <InputText id="email" v-model.trim="user.email" required="true" :invalid="submitted && !user.email" class="w-full" />
                    <small v-if="submitted && !user.email" class="text-red-500">Email is required.</small>
                </div>
                
                <div>
                    <label for="password" class="block font-bold mb-2">Password</label>
                    <Password id="password" v-model="user.password" :feedback="false" toggleMask class="w-full"
                        :invalid="submitted && !user.password && !user.id" />
                    <small v-if="submitted && !user.password && !user.id" class="text-red-500">Password is required for new users.</small>
                    <small v-if="user.id" class="text-gray-500">Leave blank to keep current password.</small>
                </div>
                
                <div>
                    <label for="role" class="block font-bold mb-2">Role</label>
                    <Dropdown id="role" v-model="user.role" :options="availableRoles" optionLabel="label" optionValue="value" 
                        placeholder="Select a Role" :invalid="submitted && !user.role" class="w-full" />
                    <small v-if="submitted && !user.role" class="text-red-500">Role is required.</small>
                </div>
            </div>
            
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" outlined @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveUser" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteUserDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="user">
                    Are you sure you want to delete <b>{{ user.name }}</b>?
                </span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" outlined @click="deleteUserDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteUser" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteUsersDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span>Are you sure you want to delete the selected users?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" outlined @click="deleteUsersDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedUsers" />
            </template>
        </Dialog>
        
        <Toast />
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import { useUserStore, type User } from '@/stores/user';

interface PasswordUser extends Omit<User, 'createdAt' | 'updatedAt'> {
    password?: string;
    createdAt?: Date;
    updatedAt?: Date;
}

export default defineComponent({
    name: 'AdminUserManagement',
    
    data() {
        return {
            userStore: useUserStore(),
            users: [] as User[],
            userDialog: false,
            deleteUserDialog: false,
            deleteUsersDialog: false,
            user: {} as PasswordUser,
            selectedUsers: null as User[] | null,
            submitted: false,
            filters: {},
            selectedRole: null as { label: string, value: string | null } | null,
            roleFilterOptions: [
                { label: 'All Roles', value: null },
                { label: 'Super Admin', value: 'super_admin' },
                { label: 'Admin', value: 'admin' },
                { label: 'Editor', value: 'editor' }
            ],
            currentUserRole: 'super_admin'
        };
    },
    
    computed: {
        // Available roles based on current user's role
        availableRoles() {
            if (this.currentUserRole === 'super_admin') {
                return [
                    { label: 'Admin', value: 'admin' },
                    { label: 'Editor', value: 'editor' }
                ];
            } else if (this.currentUserRole === 'admin') {
                return [
                    { label: 'Editor', value: 'editor' }
                ];
            }
            
            // Default (should not happen)
            return [];
        },
        
        // Filter users based on role selection
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
    
    async mounted() {
        // Load users
        try {
            await this.userStore.fetchUsers();
            this.users = this.userStore.users;
        } catch (error) {
            this.$toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to load users',
                life: 3000
            });
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
        
        openNew() {
            this.user = {
                id: '',
                name: '',
                email: '',
                password: '',
                role: this.availableRoles[0]?.value // Default to first available role
            };
            this.submitted = false;
            this.userDialog = true;
        },
        
        hideDialog() {
            this.userDialog = false;
            this.submitted = false;
        },
        
        async saveUser() {
            this.submitted = true;

            if (this.user.name?.trim() && this.user.email?.trim() && this.user.role) {
                try {
                    if (this.user.id) {
                        // Update existing user
                        const updateData = {
                            name: this.user.name,
                            email: this.user.email,
                            role: this.user.role
                        };
                        
                        if (this.user.password) {
                            Object.assign(updateData, { password: this.user.password });
                        }
                        
                        await this.userStore.updateUser(this.user.id, updateData);
                        this.$toast.add({
                            severity: 'success',
                            summary: 'Successful',
                            detail: 'User Updated',
                            life: 3000
                        });
                    } else {
                        // Create new user
                        if (!this.user.password) {
                            // Don't proceed if password is missing for new user
                            return;
                        }
                        
                        await this.userStore.createUser({
                            name: this.user.name,
                            email: this.user.email,
                            role: this.user.role
                        });
                        
                        this.$toast.add({
                            severity: 'success',
                            summary: 'Successful',
                            detail: 'User Created',
                            life: 3000
                        });
                    }
                    
                    // Refresh user list
                    this.users = this.userStore.users;
                    this.userDialog = false;
                    this.user = {} as PasswordUser;
                } catch (error: any) {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Error',
                        detail: error.message || 'An error occurred',
                        life: 3000
                    });
                }
            }
        },
        
        editUser(user: User) {
            this.user = { ...user, password: '' };
            this.userDialog = true;
        },
        
        confirmDeleteUser(user: User) {
            this.user = user;
            this.deleteUserDialog = true;
        },
        
        async deleteUser() {
            try {
                if (this.user.id) {
                    await this.userStore.deleteUser(this.user.id);
                    this.users = this.userStore.users;
                    
                    this.$toast.add({
                        severity: 'success',
                        summary: 'Successful',
                        detail: 'User Deleted',
                        life: 3000
                    });
                }
                
                this.deleteUserDialog = false;
                this.user = {} as PasswordUser;
            } catch (error: any) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: error.message || 'An error occurred while deleting user',
                    life: 3000
                });
            }
        },
        
        confirmDeleteSelected() {
            this.deleteUsersDialog = true;
        },
        
        async deleteSelectedUsers() {
            if (!this.selectedUsers || this.selectedUsers.length === 0) {
                return;
            }
            
            try {
                // Filter out current user and super_admin users
                const usersToDelete = this.selectedUsers.filter(
                    user => !this.isCurrentUser(user.id) && user.role !== 'super_admin'
                );
                
                if (usersToDelete.length === 0) {
                    this.$toast.add({
                        severity: 'warn',
                        summary: 'Warning',
                        detail: 'No eligible users to delete',
                        life: 3000
                    });
                    this.deleteUsersDialog = false;
                    return;
                }
                
                // Delete each user one by one
                for (const user of usersToDelete) {
                    await this.userStore.deleteUser(user.id);
                }
                
                // Refresh user list
                this.users = this.userStore.users;
                this.selectedUsers = null;
                
                this.$toast.add({
                    severity: 'success',
                    summary: 'Successful',
                    detail: `${usersToDelete.length} users deleted`,
                    life: 3000
                });
                
                this.deleteUsersDialog = false;
            } catch (error: any) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: error.message || 'An error occurred while deleting users',
                    life: 3000
                });
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