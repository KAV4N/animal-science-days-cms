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
        <InputText id="email" v-model.trim="userData.email" required="true" :invalid="submitted && !userData.email" class="w-full" />
        <small v-if="submitted && !userData.email" class="text-red-500">Email is required.</small>
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
        <Dropdown id="role" v-model="userData.role" :options="availableRoles" optionLabel="label" optionValue="value" 
          placeholder="Select a Role" :invalid="submitted && !userData.role" class="w-full" />
        <small v-if="submitted && !userData.role" class="text-red-500">Role is required.</small>
      </div>
      
      <div>
        <label for="university" class="block font-bold mb-2">University</label>
        <Dropdown id="university" v-model="userData.university_id" :options="universities" optionLabel="full_name" 
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
import { type User } from '@/types/user';
import { type University } from '@/types/university';
import apiService from '@/services/apiService';

interface PasswordUser extends User {
  password?: string;
  role?: string;
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
    },
    currentUserRole: {
      type: String,
      default: 'super_admin'
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
      rolesFetched: false
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
        this.userData = { 
          ...newUser, 
          password: '',
          role: newUser.roles ? newUser.roles[0] : 'editor'
        };
        this.passwordOption = 'keep';
      } else {
        this.resetForm();
      }
    }
  },
  
  methods: {
    async fetchUniversities() {
      try {
        const response = await apiService.get('/v1/universities');
        this.universities = response.data.payload;
        
        // Set default university if we have universities and creating a new user
        if (this.universities.length > 0 && !this.user) {
          this.userData.university_id = this.universities[0].id;
        }
      } catch (error) {
        console.error('Failed to fetch universities:', error);
      }
    },
    
    async fetchAvailableRoles() {
      try {
        const response = await apiService.get('/v1/roles/available');
        const roles = response.data.payload;
        
        this.availableRoles = roles.map((role: { id: string, name: string }) => ({
          label: this.formatRoleName(role.name),
          value: role.name
        }));
      } catch (error) {
        console.error('Failed to fetch roles:', error);
        
        // Fallback to static roles based on current user role
        if (this.currentUserRole === 'super_admin') {
          this.availableRoles = [
            { label: 'Editor', value: 'editor' },
            { label: 'Admin', value: 'admin' }
          ];
        } else if (this.currentUserRole === 'admin') {
          this.availableRoles = [
            { label: 'Editor', value: 'editor' }
          ];
        } else {
          this.availableRoles = [];
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
        role: 'editor', // Always set editor as default role
        university_id: this.universities.length > 0 ? this.universities[0].id : null,
        roles: [],
        permissions: [],
        must_change_password: true
      };
      this.submitted = false;
      this.autoGeneratePassword = true;
      this.passwordOption = 'keep';
    },
    
    hideDialog() {
      this.visible = false;
      this.submitted = false;
    },
    
    async saveUser() {
      this.submitted = true;

      if (this.userData.name?.trim() && 
          this.userData.email?.trim() && 
          this.userData.role && 
          this.userData.university_id) {
        this.saving = true;
        
        try {
          let payload = { ...this.userData };
          
          // Handle password options
          if (!this.userData.id) {
            // New user
            if (!this.userData.password && !this.autoGeneratePassword) {
              this.saving = false;
              return; // Don't proceed if no password and auto-generate not selected
            }
            
            if (this.autoGeneratePassword) {
              payload.password = undefined;
            }
          } else {
            // Existing user
            if (this.passwordOption === 'keep') {
              payload.password = undefined;
            } else if (this.passwordOption === 'generate') {
              payload.generate_password = true;
              payload.password = undefined;
            }
          }
          
          // Send data to parent component for saving
          this.$emit('save', payload);
          this.hideDialog();
        } finally {
          this.saving = false;
        }
      }
    }
  }
});
</script>