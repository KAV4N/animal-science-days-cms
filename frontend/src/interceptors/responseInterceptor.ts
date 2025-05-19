import { AxiosError, type AxiosInstance } from 'axios';
import { useAuthStore } from '@/stores/authStore';

const handleApiError = (error: AxiosError) => {
  if (!error.response) {
    console.error('Network error:', 'No response received from the server');
    return;
  }

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

let isRefreshing = false;
let refreshSubscribers: Array<(token: string) => void> = [];

const subscribeTokenRefresh = (callback: (token: string) => void) => {
  refreshSubscribers.push(callback);
};

const onTokenRefreshed = (token: string) => {
  refreshSubscribers.forEach(callback => callback(token));
  refreshSubscribers = [];
};

export const responseErrorInterceptor = async (error: AxiosError, api: AxiosInstance) => {
  const originalRequest = error.config;

  if (!originalRequest) {
    handleApiError(error);
    return Promise.reject(error);
  }

  if (error.response?.status === 401) {
    const authStore = useAuthStore();
    
    if (originalRequest.url?.includes('/auth/refresh')) {
      console.log(originalRequest.url);
      authStore.clearUserData();
      handleApiError(error);
      return Promise.reject(error);
    }

    if (!isRefreshing) {
      isRefreshing = true;

      try {
        await authStore.refreshToken();
        const newToken = authStore.getToken;

        if (newToken) {
          onTokenRefreshed(newToken);

          if (originalRequest.headers) {
            originalRequest.headers['Authorization'] = `Bearer ${newToken}`;
          }

          return api(originalRequest);
        }
      } catch (refreshError) {
        refreshSubscribers = [];
        authStore.clearUserData();
        handleApiError(error);
        return Promise.reject(refreshError);
      } finally {
        isRefreshing = false;
      }
    } else {

      return new Promise(resolve => {
        subscribeTokenRefresh(token => {
          if (originalRequest.headers) {
            originalRequest.headers['Authorization'] = `Bearer ${token}`;
          }
          resolve(api(originalRequest));
        });
      });
    }
  }

  handleApiError(error);
  return Promise.reject(error);
};
