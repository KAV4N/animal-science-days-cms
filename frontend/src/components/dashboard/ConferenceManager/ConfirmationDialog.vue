<!-- components/dashboard/ConfirmationDialog.vue -->
<template>
  <Dialog 
    v-model:visible="dialogVisible" 
    :style="{ width: '90vw', maxWidth: '450px' }" 
    :header="header" 
    :modal="true"
  >
    <div class="flex flex-column sm:flex-row items-center gap-4 p-3">
      <i :class="[icon, iconClass]" />
      <span v-html="message"></span>
    </div>
    <template #footer>
      <div class="flex justify-between gap-2">
        <Button :label="cancelLabel" icon="pi pi-times" text @click="onCancel" class="w-full sm:w-auto" />
        <Button :label="confirmLabel" icon="pi pi-check" @click="onConfirm" :severity="severity" class="w-full sm:w-auto" />
      </div>
    </template>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'ConfirmationDialog',
  props: {
    visible: {
      type: Boolean,
      required: true
    },
    header: {
      type: String,
      default: 'Confirm'
    },
    message: {
      type: String,
      required: true
    },
    icon: {
      type: String,
      default: 'pi pi-exclamation-triangle'
    },
    iconClass: {
      type: String,
      default: 'text-5xl text-yellow-500'
    },
    confirmLabel: {
      type: String,
      default: 'Yes'
    },
    cancelLabel: {
      type: String,
      default: 'No'
    },
    severity: {
      type: String,
      default: 'primary'
    }
  },
  emits: ['update:visible', 'confirm', 'cancel'],
  computed: {
    dialogVisible: {
      get() {
        return this.visible;
      },
      set(value: any) {
        this.$emit('update:visible', value);
      }
    }
  },
  methods: {
    onConfirm() {
      this.$emit('confirm');
      this.$emit('update:visible', false);
    },
    onCancel() {
      this.$emit('cancel');
      this.$emit('update:visible', false);
    }
  }
});
</script>