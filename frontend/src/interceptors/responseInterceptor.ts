import { AxiosError, type AxiosInstance, type InternalAxiosRequestConfig } from 'axios';
import router from '@/router';
import { useAuthStore } from '@/stores/authStore';

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

export const responseErrorInterceptor = async (error: AxiosError, api: AxiosInstance) => {
  const originalRequest = error.config as InternalAxiosRequestConfig & { _retry?: boolean };
  
  if (!error.response) {
    console.error('Network error: No response received');
    return Promise.reject(error);
  }

  if (error.response.status === 401 && !originalRequest._retry) {
    originalRequest._retry = true;
    const authStore = useAuthStore();

    if (authStore.isRefreshingToken()) {
      return new Promise<any>((resolve) => {
        authStore.addRefreshSubscriber((token: string) => {
          originalRequest.headers['Authorization'] = `Bearer ${token}`;
          resolve(api(originalRequest));
        });
      });
    }

    try {
      await authStore.refreshToken();
      
      const newToken = authStore.getAccessToken();
      
      if (!newToken) {
        throw new Error('Failed to get new access token');
      }
      
      originalRequest.headers['Authorization'] = `Bearer ${newToken}`;
      
      return api(originalRequest);
    } catch (refreshError) {
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