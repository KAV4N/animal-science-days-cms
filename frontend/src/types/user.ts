import type { PaginationMeta, PaginationLinks } from './university';

// src/types/user.ts
export interface User {
  id: number;
  name: string;
  email: string;
  created_at?: string;
  updated_at?: string;
}


export interface UserListResponse {
  success: boolean;
  message: string;
  data: User[];
}

export interface UserPaginatedResponse extends UserListResponse {
  meta: PaginationMeta;
  links: PaginationLinks;
}


export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterCredentials {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface ChangePasswordCredentials {
  new_password: string;
  new_password_confirmation: string;
}

// API Response Types
export interface LoginResponse {
  success: boolean;
  message: string;
  data: {
    user: User;
    roles: string[];
    permissions: string[];
    access_token: string;
  };
}

export interface RegisterResponse {
  success: boolean;
  message: string;
  data: {
    user: User;
    roles: string[];
    permissions: string[];
    access_token: string;
  };
}

export interface UserResponse {
  success: boolean;
  message: string;
  data: User;
}

export interface RefreshTokenResponse {
  success: boolean;
  message: string;
  data: {
    user: User;
    roles: string[];
    permissions: string[];
    access_token: string;
  };
}

export interface ChangePasswordResponse {
  success: boolean;
  message: string;
  data: {
    access_token: string;
  };
}

export interface ApiErrorResponse {
  success: boolean;
  message: string;
  errors?: Record<string, string[]>;
}