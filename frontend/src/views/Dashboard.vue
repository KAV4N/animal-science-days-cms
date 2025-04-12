<template>
    <div>
      <h1>Dashboard</h1>
      
      <div v-if="user">
        <h2>Welcome, {{ user.name }}!</h2>
        
        <div>
          <h3>Your Information</h3>
          <p>Email: {{ user.email }}</p>
          
          <h3>Your Roles</h3>
          <ul>
            <li v-for="role in roles" :key="role">{{ role }}</li>
          </ul>
          
          <h3>Your Permissions</h3>
          <ul>
            <li v-for="permission in permissions" :key="permission">{{ permission }}</li>
          </ul>
        </div>
        
        <div>
          <h3>Access Pages</h3>
          <div>
            <router-link to="/editor" v-if="hasEditorAccess">Editor Page</router-link>
          </div>
          <div>
            <router-link to="/admin" v-if="hasAdminAccess">Admin Page</router-link>
          </div>
          <div>
            <router-link to="/super-admin" v-if="hasSuperAdminAccess">Super Admin Page</router-link>
          </div>
        </div>
        
        <button @click="logout">Logout</button>
      </div>
      
      <div v-else>
        Loading user information...
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { useAuthStore } from '@/stores/auth';
  
  export default {
    name: 'DashboardView',
    
    data() {
      return {
        store: useAuthStore(),
      };
    },
    
    computed: {
      user() {
        return this.store.user;
      },
      roles() {
        return this.store.roles;
      },
      permissions() {
        return this.store.permissions;
      },
      hasEditorAccess() {
        return this.store.hasEditorAccess;
      },
      hasAdminAccess() {
        return this.store.hasAdminAccess;
      },
      hasSuperAdminAccess() {
        return this.store.hasSuperAdminAccess;
      }
    },
    
    methods: {
      async logout() {
        try {
          await this.store.logout();
          localStorage.removeItem('isLoggedIn');
          this.$router.push({ name: 'login' });
        } catch (error) {
          console.error('Logout failed', error);
        }
      }
    }
  };
  </script>