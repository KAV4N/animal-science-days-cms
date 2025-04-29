// src/types/user.ts

/**
 * User entity
 */
export interface User {
    id: number;
    name: string;
    email: string;
    created_at?: string;
    updated_at?: string;
  }
  
  /**
   * Authentication token structure
   */
  export interface Token {
    access_token: string;
    refresh_token: string;
    token_type: string;
  }
  
  /**
   * Data returned by authentication endpoints
   */
  export interface AuthResponseData {
    user: User;
    roles: string[];
    permissions: string[];
    access_token: string;
    refresh_token: string;
  }
  
  /**
   * Login response structure
   */
  export interface LoginResponse {
    data: AuthResponseData;
    message: string;
    success: boolean;
  }
  
  /**
   * Register response structure
   */
  export interface RegisterResponse {
    data: AuthResponseData;
    message: string;
    success: boolean;
  }
  
  /**
   * User profile response structure
   */
  export interface UserResponse {
    data: {
      user: User;
      roles: string[];
      permissions: string[];
    };
    success: boolean;
    message: string;
  }
  
  /**
   * Token refresh response structure
   */
  export interface RefreshTokenResponse {
    data: Token;
    success: boolean;
    message: string;


  }
    
  /**
   * Error response structure
   */
  export interface ApiErrorResponse {
    message: string;
    errors?: Record<string, string[]>;
  }