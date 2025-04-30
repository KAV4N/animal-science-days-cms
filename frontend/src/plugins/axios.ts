import axios, { type AxiosInstance } from 'axios';
import { setupInterceptors } from '@/interceptors';

// Ensure withCredentials is true to send/receive cookies
const API_CONFIG = {
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true, // This is crucial for sending/receiving cookies
};

const api: AxiosInstance = axios.create(API_CONFIG);

setupInterceptors(api);

export { api };