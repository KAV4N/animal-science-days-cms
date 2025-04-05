<template>
    <div class="register-container">
      <h1>Register</h1>
      
      <div v-if="authStore.getErrors.general" class="error-message">
        {{ authStore.getErrors.general[0] }}
      </div>
      
      <form @submit.prevent="register">
        <div class="form-group">
          <label for="name">Name</label>
          <input 
            type="text" 
            id="name" 
            v-model="form.name" 
            required 
            autocomplete="name"
          />
          <div v-if="authStore.getErrors.name" class="field-error">
            {{ authStore.getErrors.name[0] }}
          </div>
        </div>
        
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
            autocomplete="new-password"
          />
          <div v-if="authStore.getErrors.password" class="field-error">
            {{ authStore.getErrors.password[0] }}
          </div>
        </div>
        
        <div class="form-group">
          <label for="password_confirmation">Confirm Password</label>
          <input 
            type="password" 
            id="password_confirmation" 
            v-model="form.password_confirmation" 
            required 
            autocomplete="new-password"
          />
        </div>
        
        <div class="actions">
          <button type="submit" :disabled="authStore.isLoading">
            {{ authStore.isLoading ? 'Registering...' : 'Register' }}
          </button>
        </div>
      </form>
      
      <div class="links">
        <router-link to="/login">Already have an account? Login</router-link>
      </div>
    </div>
  </template>
  
  <script>
  import { defineComponent, reactive } from 'vue';
  import { useAuthStore } from '../stores/auth';
  
  export default defineComponent({
    name: 'RegisterView',
    
    setup() {
      const authStore = useAuthStore();
      
      // Reset any previous errors when component mounts
      authStore.resetErrors();
      
      const form = reactive({
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
      });
      
      const register = async () => {
        await authStore.register({
          name: form.name,
          email: form.email,
          password: form.password,
          password_confirmation: form.password_confirmation
        });
      };
      
      return {
        authStore,
        form,
        register
      };
    }
  });
  </script>
  
  <style scoped>
  .register-container {
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