import api, { getCsrfToken } from '../utils/api';

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
}

class AuthService {
  async login(credentials: LoginCredentials): Promise<User> {
    // Get CSRF cookie first
    await getCsrfToken();
    
    // Then login
    const response = await api.post('/login', credentials);
    return response.data.user;
  }

  async register(data: RegisterData): Promise<User> {
    await getCsrfToken();
    const response = await api.post('/register', data);
    return response.data.user;
  }

  async logout(): Promise<void> {
    await api.post('/logout');
  }

  async getUser(): Promise<User> {
    const response = await api.get('/user');
    return response.data;
  }
}
