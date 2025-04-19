import axios, { type AxiosInstance } from 'axios';
import { setupInterceptors } from '@/interceptors';

console.log(import.meta.env.VITE_API_URL);
const API_CONFIG = {
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true,
};

const api: AxiosInstance = axios.create(API_CONFIG);

setupInterceptors(api);

export { api };