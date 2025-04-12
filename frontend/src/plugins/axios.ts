import axios from 'axios';
import router from '@/router';
import { useAuthStore } from '@/stores/auth';


const api = axios.create({
  baseURL: 'http://localhost',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true,
  withXSRFToken: true
});

//TODO: REMOVE THIS IN FUTURE WHEN NOT USED
// Request interceptor
api.interceptors.request.use(
  (config) => {
    // You can modify the request configuration here if needed
    // For example, add a token if you're not using cookie-based authentication
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);


api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    if (error.response && error.response.status === 401) {
      const authStore = useAuthStore();

      authStore.user = null;
      authStore.roles = [];
      authStore.permissions = [];
      authStore.isAuthenticated = false;

      localStorage.removeItem('isLoggedIn');
      
      if (router.currentRoute.value.name !== 'login') {
        router.push({ name: 'login' });
      }
    }
    
    if (error.response && error.response.status === 403) {
      console.error('Permission denied:', error.response.data?.message || 'You do not have permission to access this resource');
    }
    if (error.response && error.response.status === 419) {
      console.error('CSRF token mismatch. Refreshing...');
      api.get('/api/csrf-cookie').then(() => {
        return api(error.config);
      });
    }
    
    if (error.response && error.response.status === 429) {
      console.error('Rate limit exceeded. Please try again later.');
    }

    if (error.response && error.response.status === 422) {
      console.error('Validation errors:', error.response.data?.errors || error.response.data);
    }
    
    return Promise.reject(error);
  }
);

export { api };