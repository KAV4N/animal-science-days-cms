import axios from 'axios';
import router from '@/router';
import { useAuthStore } from '@/stores/auth';


const api = axios.create({
  baseURL: 'http://localhost',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true,
  withXSRFToken: true
});


export { api };