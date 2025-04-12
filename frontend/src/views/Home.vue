<template>
    <div>
      <h1>Laravel Sanctum & Spatie Permissions Demo</h1>
      
      <div v-if="isAuthenticated">
        <p>You are logged in as {{ userName }}</p>
        <router-link to="/dashboard">Go to Dashboard</router-link>
      </div>
      
      <div v-else>
        <p>Please login or register to continue</p>
        <div>
          <router-link to="/login">Login</router-link> | 
          <router-link to="/register">Register</router-link>
        </div>
      </div>
      
      <div>
        <router-link to="/test">Go to Test Page</router-link>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { useAuthStore } from '@/stores/auth';
  
  export default {
    name: 'HomeView',
    
    data() {
      return {
        store: useAuthStore(),
      };
    },
    
    computed: {
      isAuthenticated() {
        return this.store.isAuthenticated;
      },
      userName() {
        return this.store.user?.name;
      }
    }
  };
  </script>