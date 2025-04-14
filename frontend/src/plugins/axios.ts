import axios, { type AxiosInstance, AxiosError, type AxiosResponse, type InternalAxiosRequestConfig } from 'axios';
import router from '@/router';
import { useAuthStore } from '@/stores/auth';

/**
 * API Configuration
 */
const API_CONFIG = {
  baseURL: 'http://localhost',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true,
  withXSRFToken: true
};

/**
 * Create API instance with default configuration
 */
const api: AxiosInstance = axios.create(API_CONFIG);

/**
 * Handle unauthorized access (401)
 */
const handleUnauthorized = (): void => {
  const authStore = useAuthStore();
  authStore.user = null;
  authStore.roles = [];
  authStore.permissions = [];
  authStore.isAuthenticated = false;
  
  localStorage.removeItem('isLoggedIn');
  
  if (router.currentRoute.value.name !== 'login') {
    router.push({ name: 'login' });
  }
};

/**
 * Handle CSRF token mismatch (419)
 */
const handleCsrfTokenMismatch = (config: InternalAxiosRequestConfig): Promise<AxiosResponse> => {
  console.error('CSRF token mismatch. Refreshing...');
  return api.get('/api/csrf-cookie').then(() => api(config));
};

/**
 * Response interceptor to handle API response issues
 */
api.interceptors.response.use(
  (response: AxiosResponse) => response,
  (error: AxiosError) => {
    const { response } = error;
    
    if (!response) {
      console.error('Network error: No response received');
      return Promise.reject(error);
    }
    
    switch (response.status) {
      case 401:
        handleUnauthorized();
        break;
      
      case 403:
        console.error(
          'Permission denied:', 
          'You do not have permission to access this resource'
        );
        break;
      
      case 419:
        return handleCsrfTokenMismatch(error.config as InternalAxiosRequestConfig);
      
      case 422:
        console.error('Validation errors:', response.data);
        break;
      
      case 429:
        console.error('Rate limit exceeded. Please try again later.');
        break;
      
      default:
        console.error(`Request failed with status ${response.status}:`, response.data);
    }
    
    return Promise.reject(error);
  }
);

// Request interceptor
//TODO: use in future token auth
/*
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    // You can modify the request configuration here if needed
    // For example, add a token if you're not using cookie-based authentication
    return config;
  },
  (error: AxiosError) => Promise.reject(error)
);
*/

export { api };