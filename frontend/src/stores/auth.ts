import { defineStore } from 'pinia';
import api, { getCsrfToken } from '../utils/api';
import router from '../router';

interface User {
  id: number;
  name: string;
  email: string;
}

interface LoginCredentials {
  email: string;
  password: string;
}

interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

interface AuthState {
  user: User | null;
  authenticated: boolean;
  loading: boolean;
  errors: Record<string, string[]>;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    authenticated: false,
    loading: false,
    errors: {}
  }),
  
  getters: {
    isAuthenticated: (state) => state.authenticated,
    getUser: (state) => state.user,
    isLoading: (state) => state.loading,
    getErrors: (state) => state.errors
  },
  
  actions: {
    // Reset errors
    resetErrors() {
      this.errors = {};
    },
    
    // Register a new user
    async register(data: RegisterData) {
      try {
        this.loading = true;
        this.resetErrors();
        
        // Get CSRF token first
        await getCsrfToken();
        console.log(data);
        
        // Register request
        const response = await api.post('/register', data);
        
        if (response.status === 201) {
          this.authenticated = true;
          this.user = response.data.user;
          router.push({ name: 'dashboard' });
          return true;
        }
        return false;
      } catch (error: any) {
        if (error.response && error.response.data && error.response.data.errors) {
          this.errors = error.response.data.errors;
        } else {
          this.errors = { general: ['An unexpected error occurred'] };
        }
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    // Login user
    async login(credentials: LoginCredentials) {
      try {
        this.loading = true;
        this.resetErrors();
        
        // Get CSRF token first
        await getCsrfToken();
        
        // Login request
        const response = await api.post('/login', credentials);
        
        if (response.status === 200) {
          this.authenticated = true;
          this.user = response.data.user;
          router.push({ name: 'dashboard' });
          return true;
        }
        return false;
      } catch (error: any) {
        if (error.response && error.response.status === 401) {
          this.errors = { 
            email: ['These credentials do not match our records.'] 
          };
        } else if (error.response && error.response.data && error.response.data.errors) {
          this.errors = error.response.data.errors;
        } else {
          this.errors = { general: ['An unexpected error occurred'] };
        }
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    // Logout user
    async logout() {
      try {
        this.loading = true;
        await api.post('/logout');
        this.user = null;
        this.authenticated = false;
        router.push({ name: 'login' });
        return true;
      } catch (error) {
        console.error('Logout failed:', error);
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    // Fetch authenticated user
    async fetchUser() {
      try {
        this.loading = true;
        const response = await api.get('/user');
        if (response.status === 200) {
          this.user = response.data;
          this.authenticated = true;
          return true;
        }
        return false;
      } catch (error) {
        this.user = null;
        this.authenticated = false;
        return false;
      } finally {
        this.loading = false;
      }
    }
  }
});