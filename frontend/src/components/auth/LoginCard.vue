<script lang="ts">
import { defineComponent } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { useAuthStore } from '@/stores/auth';
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
      error: null as string | null
    }
  },
  methods: {
    closeDialog() {
      this.$emit('update:visible', false);
    },
    async handleLogin() {
      const authStore = useAuthStore();
      const router = useRouter();

      try {
        await authStore.login(this.email, this.password);
        this.$emit('login');
        this.closeDialog();

        // After successful login, show password change dialog
        router.push({ name: 'change-password' });
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Login failed';
      }
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
        <div v-if="error" class="text-red-400 text-sm">{{ error }}</div>
        <div class="inline-flex flex-col gap-2">
          <label for="email" class="primary-0 font-semibold text-surface-50">Email</label>
          <InputText
            id="email"
            v-model="email"
            class="!bg-white/20 !border-0 !p-4 !text-primary-50 w-80"
          ></InputText>
        </div>
        <div class="inline-flex flex-col gap-2">
          <label for="password" class="text-primary-50 font-semibold">Password</label>
          <InputText
            id="password"
            v-model="password"
            class="!bg-white/20 !border-0 !p-4 !text-primary-50 w-80"
            type="password"
          ></InputText>
        </div>
        <div class="flex items-center gap-4">
          <Button label="Cancel" @click="closeCallback" text class="!p-4 w-full !text-primary-50 !border !border-white/30 hover:!bg-white/10"></Button>
          <Button label="Log in" @click="handleLogin" text class="!p-4 w-full !text-primary-50 !border !border-white/30 hover:!bg-white/10"></Button>
        </div>
      </div>
    </template>
  </Dialog>
</template>
