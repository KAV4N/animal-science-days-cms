<template>
  <Dialog v-model:visible="visible" :style="{ width: '450px' }" header="Confirm" :modal="true">
    <div class="flex items-center gap-4">
      <i class="pi pi-exclamation-triangle !text-3xl" />
      <span v-if="multiple">Are you sure you want to delete the selected users?</span>
      <span v-else-if="user">
        Are you sure you want to delete <b>{{ user.name }}</b>?
      </span>
    </div>
    <template #footer>
      <Button label="No" icon="pi pi-times" outlined @click="hideDialog" />
      <Button label="Yes" icon="pi pi-check" text @click="confirmDelete" />
    </template>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import { type User } from '@/stores/userManagement';

export default defineComponent({
  name: 'DeleteConfirmDialog',
  
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    user: {
      type: Object as PropType<User | null>,
      default: null
    },
    multiple: {
      type: Boolean,
      default: false
    }
  },
  
  emits: ['update:modelValue', 'confirm'],
  
  computed: {
    visible: {
      get() {
        return this.modelValue;
      },
      set(value:any) {
        this.$emit('update:modelValue', value);
      }
    }
  },
  
  methods: {
    hideDialog() {
      this.visible = false;
    },
    
    confirmDelete() {
      this.$emit('confirm');
      this.hideDialog();
    }
  }
});
</script>