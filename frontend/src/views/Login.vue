<template>
    <div>
      <h1>Login</h1>
      
      <div v-if="store.error" class="error">
        {{ store.error }}
      </div>
      
      <form @submit.prevent="handleLogin">
        <div>
          <label for="email">Email:</label>
          <input 
            type="email" 
            id="email" 
            v-model="email" 
            required
          />
        </div>
        
        <div>
          <label for="password">Password:</label>
          <input 
            type="password" 
            id="password" 
            v-model="password" 
            required
          />
        </div>
        
        <button type="submit" :disabled="store.loading">
          {{ store.loading ? 'Logging in...' : 'Login' }}
        </button>
      </form>
      
      <div>
        Don't have an account? 
        <router-link to="/register">Register</router-link>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { useAuthStore } from '@/stores/auth';
  
  export default {
    name: 'LoginView',
    
    data() {
      return {
        email: '',
        password: '',
        store: useAuthStore(),
      };
    },
    
    methods: {
      async handleLogin() {
        try {
          await this.store.login(this.email, this.password);
          
          // Store login state in localStorage to persist through page refresh
          localStorage.setItem('isLoggedIn', 'true');
          
          // Redirect to dashboard after successful login
          this.$router.push({ name: 'dashboard' });
        } catch (error) {
          console.error('Login failed', error);
        }
      }
    }
  };
  </script>