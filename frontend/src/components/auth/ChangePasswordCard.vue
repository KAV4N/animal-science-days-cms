<script lang="ts">
import { defineComponent } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { useAuthStore } from '@/stores/authStore';
import { useRouter } from 'vue-router';
import type { ChangePasswordCredentials } from '@/types/user';

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
      currentPassword: '',
      error: null as string | null,
      success: null as string | null
    }
  },
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();

    return {
      authStore,
      router,
      isFirstLogin: authStore.user?.first_login || false
    };
  },
  methods: {
    async handlePasswordChange() {
      this.error = null;
      this.success = null;

      if (this.newPassword !== this.confirmPassword) {
        this.error = 'Passwords do not match';
        return;
      }

      if (this.newPassword.length < 8) {
        this.error = 'Password must be at least 8 characters long';
        return;
      }

      try {
        const payload: ChangePasswordCredentials = {
          new_password: this.newPassword,
          new_password_confirmation: this.confirmPassword,
        };

        // Add current password if not first login
        if (!this.isFirstLogin) {
          payload.current_password = this.currentPassword;
        }

        console.log('Sending change password payload:', payload);

        await this.authStore.changePassword(payload);
        this.success = 'Password changed successfully!';

        // Redirect to dashboard after 2 seconds
        setTimeout(() => {
          this.router.push({ name: 'dashboard' });
        }, 2000);
      } catch (error: any) {
        console.error('Password change error:', error);
        this.error = error.message || 'Password change failed';
      }
    }
  }
});</script>

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

        <div v-if="isFirstLogin" class="text-center text-primary-50">
          <p>Since this is your first login, you need to change your password.</p>
        </div>

        <div v-if="error" class="text-red-400 text-sm">{{ error }}</div>
        <div v-if="success" class="text-green-400 text-sm">{{ success }}</div>

        <!-- Only show current password field if not first login -->
        <div v-if="!isFirstLogin" class="inline-flex flex-col gap-2">
          <label for="currentPassword" class="text-primary-50 font-semibold">Current Password</label>
          <InputText
            id="currentPassword"
            v-model="currentPassword"
            class="!bg-white/20 !border-0 !p-4 !text-primary-50 w-80"
            type="password"
            placeholder="Enter current password"
          ></InputText>
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
            text
            class="!p-4 w-full !text-primary-50 !border !border-white/30 hover:!bg-white/10"
          ></Button>
        </div>
      </div>
    </template>
  </Dialog>
</template>
