import type { ApiResponse } from './common';
import type { User } from './user';

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface RefreshTokenRequest {
  refresh_token: string;
}

export interface ChangePasswordRequest {
  current_password?: string;
  new_password: string;
  new_password_confirmation: string;
}

export interface AuthResponse {
  user: User;
  access_token: string;
}

export type LoginResponse = ApiResponse<AuthResponse>;
export type RegisterResponse = ApiResponse<AuthResponse>;
export type RefreshTokenResponse = ApiResponse<AuthResponse>;
export type ChangePasswordResponse = ApiResponse<{
  access_token: string;
}>;