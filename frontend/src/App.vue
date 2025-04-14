<template>
  <div id="app">
    <header>
      <nav>
        <router-link to="/">Home</router-link>
        <span v-if="!isAuthenticated">
          <router-link to="/login">Login</router-link>
          <router-link to="/register">Register</router-link>
        </span>
        <span v-else>
          <router-link to="/dashboard">Dashboard</router-link>
          <a href="#" @click.prevent="logout">Logout</a>
        </span>
      </nav>
    </header>
    
    <main>
      <router-view />
    </main>
    
    <div v-if="error" class="global-error">
      {{ error }}
    </div>
  </div>
</template>

<script lang="ts">
import { useAuthStore } from '@/stores/auth';

export default {
  name: 'App',
  
  data() {
    return {
      store: useAuthStore(),
    };
  },
  
  computed: {
    isAuthenticated() {
      return this.store.isAuthenticated;
    },
    error() {
      return this.store.error;
    }
  },
  
  mounted() {
    this.checkAuthState();
  },
  
  methods: {
    async checkAuthState() {
      if (localStorage.getItem('isLoggedIn') === 'true') {
        try {
          await this.store.fetchCurrentUser();
        } catch (error) {
          console.error('Failed to restore session', error);
          localStorage.removeItem('isLoggedIn');
        }
      }
    },
    
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