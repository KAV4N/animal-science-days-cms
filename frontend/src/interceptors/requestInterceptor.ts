import { type InternalAxiosRequestConfig } from 'axios';
import { tokenService } from '@/services/tokenService';

export const requestInterceptor = (config: InternalAxiosRequestConfig) => {
  const accessToken = tokenService.getAccessToken();
  
  if (accessToken) {
    config.headers['Authorization'] = `Bearer ${accessToken}`;
  }
  
  return config;
};