<template>
    <div>
      <h1>Editor Page</h1>
      <p>This page is only accessible to users with the 'editor' permission.</p>
      
      <div v-if="accessData">
        <h3>Access Response:</h3>
        <pre>{{ JSON.stringify(accessData, null, 2) }}</pre>
      </div>
      
      <div>
        <router-link to="/dashboard">Back to Dashboard</router-link>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { useAuthStore } from '@/stores/auth';
  
  export default {
    name: 'EditorPage',
    
    data() {
      return {
        accessData: null,
        store: useAuthStore(),
      };
    },
    
    mounted() {
      this.checkAccess();
    },
    
    methods: {
      async checkAccess() {
        try {
          const response = await this.store.checkAccess('editor');
          this.accessData = response;
        } catch (error) {
          console.error('Failed to check editor access', error);
        }
      }
    }
  };
  </script>