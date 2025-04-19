import { AxiosError, type AxiosInstance, type AxiosResponse, type InternalAxiosRequestConfig } from 'axios';
import router from '@/router';
import { useAuthStore } from '@/stores/authStore';
import { tokenService } from '@/services/tokenService';


export const responseErrorInterceptor = async (error: AxiosError, api: AxiosInstance) => {
    const originalRequest = error.config as InternalAxiosRequestConfig & { _retry?: boolean };
    
    if (!error.response) {
      console.error('Network error: No response received');
      return Promise.reject(error);
    }
    
    if (error.response.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;
      
      try {
        const refreshToken = tokenService.getRefreshToken();
        
        if (!refreshToken) {
          const authStore = useAuthStore();
          authStore.resetState();
          
          if (router.currentRoute.value.name !== 'login') {
            router.push({ name: 'login' });
          }
          
          return Promise.reject(error);
        }
        
        const refreshResponse = await tokenService.refreshToken();
        
        originalRequest.headers['Authorization'] = `Bearer ${refreshResponse.access_token}`;

        return api(originalRequest);
      } catch (refreshError) {
        const authStore = useAuthStore();
        authStore.resetState();
        
        if (router.currentRoute.value.name !== 'login') {
          router.push({ name: 'login' });
        }
        
        return Promise.reject(refreshError);
      }
    }
    
    handleApiError(error);
    
    return Promise.reject(error);
  };


  const handleApiError = (error: AxiosError) => {
    if (!error.response) return;
    
    switch (error.response.status) {
      case 403:
        console.error('Permission denied:', 'You do not have permission to access this resource');
        break;
      
      case 422:
        console.error('Validation errors:', error.response.data);
        break;
      
      case 429:
        console.error('Rate limit exceeded. Please try again later.');
        break;
      
      default:
        console.error(`Request failed with status ${error.response.status}:`, error.response.data);
    }
  };