// components/dashboard/UserManagement/UserDialog.vue
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
      
      <div>
        <label for="password" class="block font-bold mb-2">Password</label>
        <Password id="password" v-model="userData.password" :feedback="false" toggleMask class="w-full"
          :invalid="submitted && !userData.password && !userData.id" />
        <small v-if="submitted && !userData.password && !userData.id" class="text-red-500">Password is required for new users.</small>
        <small v-if="userData.id" class="text-gray-500">Leave blank to keep current password.</small>
      </div>
      
      <div>
        <label for="role" class="block font-bold mb-2">Role</label>
        <Dropdown id="role" v-model="userData.role" :options="availableRoles" optionLabel="label" optionValue="value" 
          placeholder="Select a Role" :invalid="submitted && !userData.role" class="w-full" />
        <small v-if="submitted && !userData.role" class="text-red-500">Role is required.</small>
      </div>
    </div>
    
    <template #footer>
      <Button label="Cancel" icon="pi pi-times" outlined @click="hideDialog" />
      <Button label="Save" icon="pi pi-check" @click="saveUser" />
    </template>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import { type User } from '@/stores/userManagement';

interface PasswordUser extends Omit<User, 'createdAt' | 'updatedAt'> {
  password?: string;
  createdAt?: Date;
  updatedAt?: Date;
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
      submitted: false
    };
  },
  
  computed: {
    visible: {
      get() {
        return this.modelValue;
      },
      set(value:any) {
        this.$emit('update:modelValue', value);
      }
    },
    
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
    }
  },
  
  watch: {
    user(newUser) {
      if (newUser) {
        this.userData = { ...newUser, password: '' };
      } else {
        this.resetForm();
      }
    },
    
    visible(newValue) {
      if (newValue && !this.user) {
        this.resetForm();
      }
    }
  },
  
  methods: {
    resetForm() {
      this.userData = {
        id: '',
        name: '',
        email: '',
        password: '',
        role: this.availableRoles[0]?.value || 'editor'
      };
      this.submitted = false;
    },
    
    hideDialog() {
      this.visible = false;
      this.submitted = false;
    },
    
    saveUser() {
      this.submitted = true;

      if (this.userData.name?.trim() && this.userData.email?.trim() && this.userData.role) {
        // Check if password is required for new users
        if (!this.userData.id && !this.userData.password) {
          return; // Don't proceed if password is missing for new user
        }
        
        this.$emit('save', this.userData);
        this.hideDialog();
      }
    }
  }
});
</script>