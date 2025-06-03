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
  emits: ['password-changed'],
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
          this.$emit('password-changed');

          if (this.authStore.user) {
            this.authStore.user.must_change_password = false;
          }

          this.router.push({ name: 'ConferenceManagement' });
        } else {
          this.errorMessage = this.authStore.error || 'Password change failed';
        }
      } catch (error: any) {
        this.errorMessage = error.message || 'An unexpected error occurred';
      } finally {
        this.isLoading = false;
      }
    },
    goBackHome() {
      this.router.push({ name: 'HomePage' });
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
      <div class="flex flex-col items-center px-8 py-8 gap-6 rounded-2xl" style="background-image: radial-gradient(circle at left top, var(--p-surface-400), var(--p-surface-700))">
        <img src="/school-logo.png" alt="School Logo" class="block mx-auto h-20 w-auto" />

        <div v-if="mustChange" class="text-center text-primary-50 w-80">
          <p>Since this is your first login, you need to change your password.</p>
        </div>

        <div v-if="errorMessage" class="bg-red-500/20 border border-red-500 text-red-400 p-3 rounded-lg text-center w-80">
          {{ errorMessage }}
        </div>

        <div v-if="successMessage" class="bg-green-500/20 border border-green-500 text-green-400 p-3 rounded-lg text-center w-80">
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
          />
        </div>

        <div class="inline-flex flex-col gap-2">
          <label for="confirmPassword" class="text-primary-50 font-semibold">Confirm Password</label>
          <InputText
            id="confirmPassword"
            v-model="confirmPassword"
            class="!bg-white/20 !border-0 !p-4 !text-primary-50 w-80"
            type="password"
            placeholder="Confirm new password"
          />
        </div>

        <div class="flex flex-col gap-3 w-80">
          <Button
            label="Change Password"
            @click="handlePasswordChange"
            :disabled="isLoading"
            text
            class="!p-4 w-full !text-primary-50 !border !border-white/30 hover:!bg-white/10"
          >
            {{ isLoading ? 'Changing...' : 'Change Password' }}
          </Button>

          <!-- 👇 Subtle "Go Back Home" Button -->
          <Button
            label="Go Back Home"
            @click="goBackHome"
            text
            class="!p-3 w-full !text-primary-300 !border !border-white/10 hover:!bg-white/5"
          />
        </div>
      </div>
    </template>
  </Dialog>
</template>
