<template>
    <div>
      <h1>Register</h1>
      
      <div v-if="store.error" class="error">
        {{ store.error }}
      </div>
      
      <form @submit.prevent="handleRegister">
        <div>
          <label for="name">Name:</label>
          <input 
            type="text" 
            id="name" 
            v-model="name" 
            required
          />
        </div>
        
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
        
        <div>
          <label for="password_confirmation">Confirm Password:</label>
          <input 
            type="password" 
            id="password_confirmation" 
            v-model="passwordConfirmation" 
            required
          />
        </div>
        
        <button type="submit" :disabled="store.loading">
          {{ store.loading ? 'Registering...' : 'Register' }}
        </button>
      </form>
      
      <div>
        Already have an account? 
        <router-link to="/login">Login</router-link>
      </div>
    </div>
  </template>
  
  <script lang="ts">
  import { useAuthStore } from '@/stores/auth';
  
  export default {
    name: 'RegisterView',
    
    data() {
      return {
        name: '',
        email: '',
        password: '',
        passwordConfirmation: '',
        store: useAuthStore(),
      };
    },
    
    methods: {
      async handleRegister() {
        try {
          await this.store.register(
            this.name, 
            this.email, 
            this.password, 
            this.passwordConfirmation
          );
          
          // Store login state in localStorage
          localStorage.setItem('isLoggedIn', 'true');
          
          // Redirect to dashboard after successful registration
          this.$router.push({ name: 'dashboard' });
        } catch (error) {
          console.error('Registration failed', error);
        }
      }
    }
  };
  </script>