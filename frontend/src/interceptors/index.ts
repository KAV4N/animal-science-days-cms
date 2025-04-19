import { AxiosError, type AxiosInstance, type AxiosResponse, type InternalAxiosRequestConfig } from 'axios';

import { responseErrorInterceptor } from './responseInterceptor';
import { requestInterceptor } from './requestInterceptor';


export const setupInterceptors = (api: AxiosInstance) => {
  // Request interceptor
  api.interceptors.request.use(
    requestInterceptor,
    (error: AxiosError) => Promise.reject(error)
  );
  
  // Response interceptor
  api.interceptors.response.use(
    (response: AxiosResponse) => response,
    (error: AxiosError) => responseErrorInterceptor(error, api)
  );
};