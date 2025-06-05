<template>
  <Toolbar class="mb-6 flex flex-wrap gap-2 rounded-md shadow" style="border:none; margin-bottom: 1rem;">
    <template #start>
      <div class="flex flex-wrap gap-2">
        <Button 
          v-if="authStore.hasAdminAccess"
          label="New Conference" 
          icon="pi pi-plus" 
          class="mr-2" 
          @click="onNewConference" 
        />
        <Button 
          v-if="authStore.hasAdminAccess"
          label="Delete Selected" 
          icon="pi pi-trash" 
          severity="danger" 
          outlined 
          @click="onConfirmDeleteSelected" 
          :disabled="!selectedConferences || selectedConferences.length === 0" 
        />
      </div>
    </template>
  </Toolbar>
</template>
  
<script lang="ts">
import { defineComponent } from 'vue';
import { useAuthStore } from '@/stores/authStore';

export default defineComponent({
  name: 'ConferenceToolbar',
  props: {
    selectedConferences: {
      type: Array,
      default: () => []
    }
  },
  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },
  methods: {
    onNewConference() {
      this.$emit('new-conference');
    },
    onConfirmDeleteSelected() {
      this.$emit('confirm-delete-selected');
    }
  }
});
</script>