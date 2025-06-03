<script lang="ts">
import { defineComponent } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { mapStores } from 'pinia';
import { useAuthStore } from '@/stores/authStore';
import { useRouter } from 'vue-router';

export default defineComponent({
  name: 'LoginCard',
  components: {
    Dialog,
    Button,
    InputText
  },
  props: {
    visible: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:visible', 'login'],
  data() {
    return {
      email: '',
      password: '',
      errorMessage: null as string | null,
      isLoading: false
    }
  },
  computed: {
    ...mapStores(useAuthStore)
  },
  setup() {
    const router = useRouter();
    return { router };
  },
  methods: {
    resetForm() {
      this.email = '';
      this.password = '';
      this.errorMessage = null;
    },
    closeDialog() {
      this.resetForm();
      this.$emit('update:visible', false);
    },
    async handleLogin() {
      this.errorMessage = null;
      this.isLoading = true;

      try {
        const loginSuccessful = await this.authStore.login({
          email: this.email,
          password: this.password
        });

        if (loginSuccessful) {
          this.$emit('login');
          this.resetForm();
          this.closeDialog();
          this.$router.push('dashboard');
        } else {
          this.errorMessage = this.authStore.error || 'Login failed';
        }
      } catch (error: any) {
        this.errorMessage = error.message || 'An unexpected error occurred';
      } finally {
        this.isLoading = false;
      }
    },
    goBackHome() {
      this.resetForm();
      this.router.push({ name: 'HomePage' });
    }
  }
});
</script>

<template>
  <Dialog
    :visible="visible"
    @update:visible="$emit('update:visible', $event)"
    pt:root:class="!border-0 !bg-transparent"
    pt:mask:class="backdrop-blur-sm"
    modal
  >
    <template #container="{ closeCallback }">
      <div class="flex flex-col px-8 py-8 gap-6 rounded-2xl" style="background-image: radial-gradient(circle at left top, var(--p-surface-400), var(--p-surface-700))">
        <img src="/school-logo.png" alt="School Logo" class="block mx-auto h-20 w-auto" />

        <div v-if="errorMessage" class="bg-red-500/20 border border-red-500 text-red-400 p-3 rounded-lg text-center">
          {{ errorMessage }}
        </div>

        <div class="inline-flex flex-col gap-2">
          <label for="email" class="primary-0 font-semibold text-surface-50">Email</label>
          <InputText
            id="email"
            v-model="email"
            class="!bg-white/20 !border-0 !p-4 !text-primary-50 w-80"
          />
        </div>
        <div class="inline-flex flex-col gap-2">
          <label for="password" class="text-primary-50 font-semibold">Password</label>
          <InputText
            id="password"
            v-model="password"
            class="!bg-white/20 !border-0 !p-4 !text-primary-50 w-80"
            type="password"
          />
        </div>

        <div class="flex flex-col gap-3 w-80">
          <div class="flex gap-4">
            <Button
              label="Cancel"
              @click="closeCallback"
              text
              class="!p-4 w-full !text-primary-50 !border !border-white/30 hover:!bg-white/10"
            />
            <Button
              label="Log in"
              @click="handleLogin"
              :disabled="isLoading"
              text
              class="!p-4 w-full !text-primary-50 !border !border-white/30 hover:!bg-white/10"
            >
              {{ isLoading ? 'Logging in...' : 'Log in' }}
            </Button>
          </div>

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
