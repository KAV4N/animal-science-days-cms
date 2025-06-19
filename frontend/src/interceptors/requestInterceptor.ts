// src/interceptors/requestInterceptor.ts
import { type InternalAxiosRequestConfig } from 'axios';
import { tokenManager } from '@/utils/tokenManager';

export const requestInterceptor = (config: InternalAxiosRequestConfig) => {
  // Get token from localStorage instead of Pinia store
  const accessToken = tokenManager.getAccessToken();
  
  if (accessToken) {
    config.headers['Authorization'] = `Bearer ${accessToken}`;
  }
  
  return config;
};