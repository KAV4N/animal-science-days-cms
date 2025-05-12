<script lang="ts">
import { defineComponent } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { useAuthStore } from '@/stores/authStore';
import { useRouter } from 'vue-router';

export default defineComponent({
  name: 'ChangePasswordCard',
  components: {
    Dialog,
    Button,
    InputText
  },
  data() {
    return {
      newPassword: '',
      confirmPassword: '',
      errorMessage: null as string | null,
      successMessage: null as string | null,
      isLoading: false
    }
  },
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();

    return {
      authStore,
      router,
      mustChange: authStore.user?.must_change_password
    };
  },
  methods: {
    async handlePasswordChange() {
      this.errorMessage = null;
      this.successMessage = null;
      this.isLoading = true;

      if (this.newPassword !== this.confirmPassword) {
        this.errorMessage = 'Passwords do not match';
        this.isLoading = false;
        return;
      }

      if (this.newPassword.length < 8) {
        this.errorMessage = 'Password must be at least 8 characters long';
        this.isLoading = false;
        return;
      }

      try {
        const changeSuccessful = await this.authStore.changePassword({
          new_password: this.newPassword,
          new_password_confirmation: this.confirmPassword,
        });

        if (changeSuccessful) {
          this.successMessage = 'Password changed successfully!';

          setTimeout(() => {
            this.router.push({ name: 'Dashboard' });
          }, 2000);
        } else {
          this.errorMessage = this.authStore.error || 'Password change failed';
        }
      } catch (error: any) {
        this.errorMessage = error.message || 'An unexpected error occurred';
      } finally {
        this.isLoading = false;
      }
    }
  }
});
</script>

<template>
  <Dialog
    :visible="true"
    :closable="false"
    pt:root:class="!border-0 !bg-transparent"
    pt:mask:class="backdrop-blur-sm"
    modal
  >
    <template #container>
      <div class="flex flex-col px-8 py-8 gap-6 rounded-2xl" style="background-image: radial-gradient(circle at left top, var(--p-surface-400), var(--p-surface-700))">
        <img src="/school-logo.png" alt="School Logo" class="block mx-auto h-20 w-auto" />
        <h2 class="text-center text-primary-50 font-semibold">Change Password</h2>

        <!-- Mandatory Change Password Message -->
        <div v-if="mustChange" class="text-center text-primary-50">
          <p>Since this is your first login, you need to change your password.</p>
        </div>

        <!-- Error Message Display -->
        <div v-if="errorMessage" class="bg-red-500/20 border border-red-500 text-red-400 p-3 rounded-lg text-center">
          {{ errorMessage }}
        </div>

        <!-- Success Message Display -->
        <div v-if="successMessage" class="bg-green-500/20 border border-green-500 text-green-400 p-3 rounded-lg text-center">
          {{ successMessage }}
        </div>

        <div class="inline-flex flex-col gap-2">
          <label for="newPassword" class="text-primary-50 font-semibold">New Password</label>
          <InputText
            id="newPassword"
            v-model="newPassword"
            class="!bg-white/20 !border-0 !p-4 !text-primary-50 w-80"
            type="password"
            placeholder="Enter new password"
          ></InputText>
        </div>

        <div class="inline-flex flex-col gap-2">
          <label for="confirmPassword" class="text-primary-50 font-semibold">Confirm Password</label>
          <InputText
            id="confirmPassword"
            v-model="confirmPassword"
            class="!bg-white/20 !border-0 !p-4 !text-primary-50 w-80"
            type="password"
            placeholder="Confirm new password"
          ></InputText>
        </div>

        <div class="flex items-center gap-4">
          <Button
            label="Change Password"
            @click="handlePasswordChange"
            :disabled="isLoading"
            text
            class="!p-4 w-full !text-primary-50 !border !border-white/30 hover:!bg-white/10"
          >
            {{ isLoading ? 'Changing...' : 'Change Password' }}
          </Button>
        </div>
      </div>
    </template>
  </Dialog>
</template>