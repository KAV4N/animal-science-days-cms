<template>
  <Dialog v-model:visible="visible" :style="{ width: '550px' }" header="User Details" :modal="true">
    <div class="flex flex-col gap-4 p-2">
      <div>
        <label for="name" class="block font-bold mb-2">Name</label>
        <InputText id="name" v-model.trim="userData.name" required="true" autofocus :invalid="submitted && !userData.name" class="w-full" />
        <small v-if="submitted && !userData.name" class="text-red-500">Name is required.</small>
      </div>
      
      <div>
        <label for="email" class="block font-bold mb-2">Email</label>
        <InputText id="email" v-model.trim="userData.email" required="true" :invalid="submitted && (emailError || !userData.email)" class="w-full" />
        <small v-if="submitted && !userData.email" class="text-red-500">Email is required.</small>
        <small v-if="submitted && userData.email && emailError" class="text-red-500">{{ emailError }}</small>
      </div>
      
      <div v-if="!userData.id">
        <label for="password" class="block font-bold mb-2">Password</label>
        <div class="flex gap-2">
          <Password id="password" v-model="userData.password" :feedback="false" toggleMask class="flex-1"
            :invalid="submitted && !userData.password && !autoGeneratePassword" />
          <div class="flex items-center gap-2">
            <Checkbox v-model="autoGeneratePassword" binary />
            <label>Auto-generate</label>
          </div>
        </div>
        <small v-if="submitted && !userData.password && !autoGeneratePassword && !userData.id" class="text-red-500">
          Password is required for new users unless auto-generate is selected.
        </small>
      </div>
      
      <div v-else>
        <label class="block font-bold mb-2">Password Options</label>
        <div class="flex flex-col gap-2 p-3 border rounded bg-gray-50">
          <div class="flex items-center gap-2">
            <RadioButton v-model="passwordOption" value="keep" />
            <label>Keep current password</label>
          </div>
          <div class="flex items-center gap-2">
            <RadioButton v-model="passwordOption" value="manual" />
            <label>Set new password manually</label>
          </div>
          <div class="flex items-center gap-2">
            <RadioButton v-model="passwordOption" value="generate" />
            <label>Generate new random password</label>
          </div>
          
          <div v-if="passwordOption === 'manual'" class="mt-2">
            <Password v-model="userData.password" :feedback="false" toggleMask class="w-full"
              placeholder="Enter new password" />
          </div>
        </div>
      </div>
      
      <div>
        <label for="role" class="block font-bold mb-2">Role</label>
        <Select id="role" v-model="userData.role" :options="availableRoles" optionLabel="label" optionValue="value" 
          placeholder="Select a Role" :invalid="submitted && !userData.role" class="w-full" />
        <small v-if="submitted && !userData.role" class="text-red-500">Role is required.</small>
      </div>
      
      <div>
        <label for="university" class="block font-bold mb-2">University</label>
        <Select id="university" v-model="userData.university_id" :options="universities" optionLabel="full_name" 
          optionValue="id" placeholder="Select a University" :invalid="submitted && !userData.university_id" class="w-full" />
        <small v-if="submitted && !userData.university_id" class="text-red-500">University is required.</small>
      </div>
    </div>
    
    <template #footer>
      <Button label="Cancel" icon="pi pi-times" outlined @click="hideDialog" />
      <Button label="Save" icon="pi pi-check" :loading="saving" @click="saveUser" />
    </template>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import apiService from '@/services/apiService';
import type { User, Role, Permission } from '@/types/user';
import type { University } from '@/types/university';

interface PasswordUser extends User {
  password?: string;
  role?: string;
  university_id?: number | null;
  generate_password?: boolean;
}

interface RoleOption {
  label: string;
  value: string;
}

export default defineComponent({
  name: 'UserDialog',
  
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    user: {
      type: Object as PropType<User | null>,
      default: null
    }
  },
  
  emits: ['update:modelValue', 'save'],
  
  data() {
    return {
      userData: {} as PasswordUser,
      submitted: false,
      saving: false,
      universities: [] as University[],
      availableRoles: [] as RoleOption[],
      autoGeneratePassword: true,
      passwordOption: 'keep',
      rolesFetched: false,
      emailError: ''
    };
  },
  
  computed: {
    visible: {
      get() {
        return this.modelValue;
      },
      set(value: boolean) {
        this.$emit('update:modelValue', value);
      }
    },
    
    authStore() {
      return useAuthStore();
    },
    
    currentUserRole(): string {
      const roleNames = this.authStore.getRoleNames;
      if (roleNames.includes('super_admin')) return 'super_admin';
      if (roleNames.includes('admin')) return 'admin';
      return 'editor';
    },
    
    currentUserPermissions(): string[] {
      return this.authStore.getPermissionNames;
    },
    
    canManageAllRoles(): boolean {
      return this.authStore.hasSuperAdminAccess;
    },
    
    canManageEditors(): boolean {
      return this.authStore.hasAdminAccess;
    }
  },
  
  watch: {
    async visible(newValue) {
      if (newValue) {
        if (!this.rolesFetched) {
          await Promise.all([
            this.fetchUniversities(),
            this.fetchAvailableRoles()
          ]);
          this.rolesFetched = true;
        }
        
        if (!this.user) {
          this.resetForm();
        }
      }
    },
    
    user(newUser) {
      if (newUser) {
        // Extract the primary role from the user's roles array
        const primaryRole = newUser.roles && newUser.roles.length > 0 
          ? newUser.roles[0].name 
          : 'editor';
          
        // Extract university ID from either direct property or nested university object
        const universityId = newUser.university_id || (newUser.university ? newUser.university.id : null);
        
        this.userData = { 
          ...newUser,
          password: '',
          role: primaryRole,
          university_id: universityId
        };
        this.passwordOption = 'keep';
        this.emailError = ''; // Reset email error when user changes
      } else {
        this.resetForm();
      }
    },
    
    'userData.email'() {
      // Clear email error when user edits the email
      if (this.emailError) {
        this.emailError = '';
      }
    }
  },
  
  methods: {
    async fetchUniversities() {
      try {
        const response = await apiService.get('/v1/universities');
        this.universities = response.data.payload;
      } catch (error) {
        console.error('Failed to fetch universities:', error);
      }
    },
    
    async fetchAvailableRoles() {
      try {
        const response = await apiService.get('/v1/roles/available');
        const roles = response.data.payload;
        
        this.availableRoles = roles.map((role: { id: number, name: string }) => ({
          label: this.formatRoleName(role.name),
          value: role.name
        }));
      } catch (error) {
        console.error('Failed to fetch roles:', error);
        
        // Fallback role options based on current user's role
        if (this.authStore.hasSuperAdminAccess) {
          this.availableRoles = [
            { label: 'Super Admin', value: 'super_admin' },
            { label: 'Admin', value: 'admin' },
            { label: 'Editor', value: 'editor' }
          ];
        } else if (this.authStore.hasAdminAccess) {
          this.availableRoles = [
            { label: 'Admin', value: 'admin' },
            { label: 'Editor', value: 'editor' }
          ];
        } else {
          this.availableRoles = [
            { label: 'Editor', value: 'editor' }
          ];
        }
      }
    },
    
    formatRoleName(name: string): string {
      return name.charAt(0).toUpperCase() + name.slice(1).replace(/_/g, ' ');
    },
    
    resetForm() {
      this.userData = {
        id: 0,
        name: '',
        email: '',
        password: '',
        role: 'editor',
        university_id: null,
        roles: [],
        permissions: [],
        must_change_password: true
      };
      this.submitted = false;
      this.autoGeneratePassword = true;
      this.passwordOption = 'keep';
      this.emailError = '';
    },
    
    hideDialog() {
      this.visible = false;
      this.submitted = false;
      this.emailError = '';
    },
    
    validateEmail(email: string): boolean {
      // RFC 5322 compliant email regex
      const emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      
      if (!email) {
        this.emailError = 'Email is required.';
        return false;
      }
      
      if (!emailRegex.test(email)) {
        this.emailError = 'Please enter a valid email address.';
        return false;
      }
      
      return true;
    },
    
    async saveUser() {
      this.submitted = true;
      this.emailError = '';
      
      // Validate email format
      if (!this.validateEmail(this.userData.email || '')) {
        return;
      }

      if (this.userData.name?.trim() && 
          this.userData.email?.trim() && 
          this.userData.role && 
          this.userData.university_id) {
        this.saving = true;
        
        try {
          const payload: PasswordUser = { 
            ...this.userData,
            // Convert role string to proper Role[] format
            roles: [{ id: 0, name: this.userData.role }] 
          };
          
          // Handle password logic for new users
          if (!this.userData.id) {
            if (!this.userData.password && !this.autoGeneratePassword) {
              this.saving = false;
              return; // Stop if no password provided for new user
            }
            
            if (this.autoGeneratePassword) {
              payload.password = undefined;
              payload.generate_password = true;
            }
          } else {
            if (this.passwordOption === 'keep') {
              payload.password = undefined;
            } else if (this.passwordOption === 'generate') {
              payload.generate_password = true;
              payload.password = undefined;
            }
            // For manual, the password field is already set correctly
          }
          
          // Emit the save event with the prepared payload
          this.$emit('save', payload);
          this.hideDialog();
        } catch (error) {
          console.error('Error saving user:', error);
        } finally {
          this.saving = false;
        }
      }
    }
  }
});
</script>