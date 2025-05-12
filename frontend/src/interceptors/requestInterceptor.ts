// src/interceptors/requestInterceptor.ts
import { type InternalAxiosRequestConfig } from 'axios';
import { useAuthStore } from '@/stores/authStore';

export const requestInterceptor = (config: InternalAxiosRequestConfig) => {
  const authStore = useAuthStore();
  const accessToken = authStore.getToken;
  
  if (accessToken) {
    config.headers['Authorization'] = `Bearer ${accessToken}`;
  }
  
  return config;
};