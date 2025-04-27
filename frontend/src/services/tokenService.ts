// src/services/token-service.ts
import axios from 'axios';
import type { RefreshTokenResponse } from '@/types/user';

export const tokenService = {
  /**
   * Get the stored access token
   */
  getAccessToken(): string | null {
    return localStorage.getItem('access_token');
  },
  
  /**
   * Get the stored refresh token
   */
  getRefreshToken(): string | null {
    return localStorage.getItem('refresh_token');
  },
  
  /**
   * Store authentication tokens
   */
  setTokens(accessToken: string, refreshToken: string): void {
    localStorage.setItem('access_token', accessToken);
    localStorage.setItem('refresh_token', refreshToken);
  },
  
  /**
   * Remove authentication tokens
   */
  removeTokens(): void {
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
  },
  
  /**
   * Check if user has valid tokens
   */
  hasTokens(): boolean {
    return !!this.getAccessToken() && !!this.getRefreshToken();
  },
  
  /**
   * Refresh the access token using the refresh token
   */
  async refreshToken(): Promise<RefreshTokenResponse> {
    const refreshToken = this.getRefreshToken();
    
    if (!refreshToken) {
      throw new Error('No refresh token available');
    }
    
    try {
      const response = await axios.post<RefreshTokenResponse>(
        `${import.meta.env.VITE_API_URL}/v1/auth/refresh`,
        { refresh_token: refreshToken },
        {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
        }
      );
      
      const { access_token, refresh_token } = response.data;
    
      this.setTokens(access_token, refresh_token);
      
      return response.data;
    } catch (error) {
      this.removeTokens();
      throw error;
    }
  }
};