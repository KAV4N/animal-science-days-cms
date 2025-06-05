<template>
  <Toolbar class="mb-6 flex flex-wrap gap-2 rounded-md shadow" style="border:none; margin-bottom: 1rem;">
    <template #start>
      <Button label="New User" icon="pi pi-plus" class="mr-2" @click="$emit('new-user')" />
      <Button 
        label="Delete" 
        icon="pi pi-trash" 
        severity="danger" 
        outlined 
        @click="$emit('delete-selected')" 
        :disabled="!hasSelectedUsers" 
      />
    </template>

    <template #end>
      <Button label="Export" icon="pi pi-upload" severity="secondary" @click="$emit('export')" />
    </template>
  </Toolbar>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'UserToolbar',
  
  props: {
    hasSelectedUsers: {
      type: Boolean,
      default: false
    }
  },
  
  emits: ['new-user', 'delete-selected', 'export']
});
</script>