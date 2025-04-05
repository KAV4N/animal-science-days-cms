<template>
    <div class="login-container">
      <h1>Login</h1>
      
      <div v-if="authStore.getErrors.general" class="error-message">
        {{ authStore.getErrors.general[0] }}
      </div>
      
      <form @submit.prevent="login">
        <div class="form-group">
          <label for="email">Email</label>
          <input 
            type="email" 
            id="email" 
            v-model="form.email" 
            required 
            autocomplete="email"
          />
          <div v-if="authStore.getErrors.email" class="field-error">
            {{ authStore.getErrors.email[0] }}
          </div>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input 
            type="password" 
            id="password" 
            v-model="form.password" 
            required 
            autocomplete="current-password"
          />
          <div v-if="authStore.getErrors.password" class="field-error">
            {{ authStore.getErrors.password[0] }}
          </div>
        </div>
        
        <div class="actions">
          <button type="submit" :disabled="authStore.isLoading">
            {{ authStore.isLoading ? 'Logging in...' : 'Login' }}
          </button>
        </div>
      </form>
      
      <div class="links">
        <router-link to="/register">Don't have an account? Register</router-link>
      </div>
    </div>
  </template>
  
  <script>
  import { defineComponent, reactive } from 'vue';
  import { useAuthStore } from '../stores/auth';
  
  export default defineComponent({
    name: 'LoginView',
    
    setup() {
      const authStore = useAuthStore();
      
      // Reset any previous errors when component mounts
      authStore.resetErrors();
      
      const form = reactive({
        email: '',
        password: ''
      });
      
      const login = async () => {
        await authStore.login({
          email: form.email,
          password: form.password
        });
      };
      
      return {
        authStore,
        form,
        login
      };
    }
  });
  </script>
  
  <style scoped>
  .login-container {
    max-width: 400px;
    margin: 0 auto;
    padding: 20px;
  }
  
  .form-group {
    margin-bottom: 15px;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 5px;
  }
  
  .form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
  
  .error-message, .field-error {
    color: red;
    margin: 5px 0;
  }
  
  .actions {
    margin-top: 20px;
  }
  
  button {
    padding: 8px 16px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }
  
  button:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
  }
  
  .links {
    margin-top: 20px;
  }
  </style>