<!-- components/dashboard/ConferenceManagement/ConferenceToolbar.vue -->
<template>
    <Toolbar class="mb-6 flex flex-wrap gap-2 rounded-md shadow" style="border:none; margin-bottom: 1rem;">
      <template #start>
        <div class="flex flex-wrap gap-2">
          <Button label="New Conference" icon="pi pi-plus" class="mr-2" @click="onNewConference" />
          <Button 
            label="Delete" 
            icon="pi pi-trash" 
            severity="danger" 
            outlined 
            @click="onConfirmDeleteSelected" 
            :disabled="!selectedConferences || !selectedConferences.length" 
          />
        </div>
      </template>
    </Toolbar>
  </template>
  
  <script>
  import { defineComponent } from 'vue';
  
  export default defineComponent({
    name: 'ConferenceToolbar',
    props: {
      selectedConferences: {
        type: Array,
        default: () => []
      }
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