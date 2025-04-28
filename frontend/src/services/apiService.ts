// src/services/api-service.ts
import { api } from '@/plugins/axios';
import { tokenService } from '@/services/tokenService';
import type { LoginResponse, RegisterResponse, UserResponse, RefreshTokenResponse } from '@/types/user';
import axios, { type AxiosRequestConfig, type AxiosResponse } from 'axios';
/**
 * Authentication related API calls
 */
const authService = {
  /**
   * Login with email and password
   */
  login(email: string, password: string) {
    return api.post<LoginResponse>('/v1/auth/login', { email, password });
  },

  /**
   * Register a new user
   */
  register(name: string, email: string, password: string, password_confirmation: string) {
    return api.post<RegisterResponse>('/v1/auth/register', {
      name,
      email,
      password,
      password_confirmation
    });
  },

  /**
   * Logout the current user
   */
  logout() {
    const refreshToken = tokenService.getRefreshToken();
    return api.post('/v1/auth/logout', { refresh_token: refreshToken });
  },

  /**
   * Refresh authentication tokens
   */
  refresh() {
    const refreshToken = tokenService.getRefreshToken();
    return api.post<RefreshTokenResponse>('/v1/auth/refresh', { refresh_token: refreshToken });
  },

  /**
   * Get current authenticated user
   */
  getCurrentUser() {
    return api.get<UserResponse>('/v1/users/me');
  },

  /**
   * Change user password
   */
  changePassword(new_password: string, new_password_confirmation: string) {
    //TODO: not defined on
    return api.post('/v1/auth/change-password', {
      new_password,
      new_password_confirmation
    });
  }
};



/**
 * General API methods
 */
const apiService = {
  auth: authService,
  
  /**
   * Generic GET request
   */

  get<T = any>(url: string, config?: AxiosRequestConfig): Promise<AxiosResponse<T>> {
    return api.get<T>(url, config);
  },
  
  post<T = any>(url: string, data?: any, config?: AxiosRequestConfig): Promise<AxiosResponse<T>> {
    return api.post<T>(url, data, config);
  },
  
  put<T = any>(url: string, data?: any, config?: AxiosRequestConfig): Promise<AxiosResponse<T>> {
    return api.put<T>(url, data, config);
  },

  patch<T = any>(url: string, data?: any, config?: AxiosRequestConfig): Promise<AxiosResponse<T>> {
    return api.patch<T>(url, data, config);
  },
  
  delete<T = any>(url: string, config?: AxiosRequestConfig): Promise<AxiosResponse<T>> {
    return api.delete<T>(url, config);
  }

};

export default apiService;