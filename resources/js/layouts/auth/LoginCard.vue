<script lang="ts">
import { defineComponent } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

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
  methods: {
    closeDialog() {
      this.$emit('update:visible', false);
    },
    handleLogin() {
      this.$emit('login');
      this.closeDialog();
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
      <div class="flex flex-col px-8 py-8 gap-6 rounded-2xl" style="background-image: radial-gradient(circle at left top, var(--p-primary-400), var(--p-primary-700))">
        <img src="/school-logo.png" alt="School Logo" class="block mx-auto h-20 w-auto" />
        <div class="inline-flex flex-col gap-2">
          <label for="username" class="primary-0 font-semibold text-primary-50">Username</label>
          <InputText id="username" class="!bg-white/20 !border-0 !p-4 !text-primary-50 w-80"></InputText>
        </div>
        <div class="inline-flex flex-col gap-2">
          <label for="password" class="text-primary-50 font-semibold">Password</label>
          <InputText id="password" class="!bg-white/20 !border-0 !p-4 !text-primary-50 w-80" type="password"></InputText>
        </div>
        <div class="flex items-center gap-4">
          <Button label="Cancel" @click="closeCallback" text class="!p-4 w-full !text-primary-50 !border !border-white/30 hover:!bg-white/10"></Button>
          <Button label="Sign-In" @click="handleLogin" text class="!p-4 w-full !text-primary-50 !border !border-white/30 hover:!bg-white/10"></Button>
        </div>
      </div>
    </template>
  </Dialog>
</template>
