export interface User {
  id: number;
  name: string;
  email: string;
  first_login: boolean;
  university_id?: number;
  roles?: string[];
  permissions?: string[];
  created_at?: string;
  updated_at?: string;
}

// Represents the user object as shaped by UserResource.php
export interface UserFromResource {
  id: number;
  name: string;
  email: string;
  university_id?: number;
  roles: string[];
  permissions: string[];
  // Note: first_login is not part of UserResource output
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
  current_password?: string;
}

export interface ApiSuccessResponse<T> {
  success: boolean;
  message: string;
  payload: T;
}

export interface LoginResponsePayload {
  user: UserFromResource;
  access_token: string;
  first_login: boolean; // Authoritative first_login from backend for this flow
}

export type LoginResponse = ApiSuccessResponse<LoginResponsePayload>;

export interface RegisterResponsePayload {
  user: UserFromResource;
  access_token: string;
  first_login: boolean; // Authoritative first_login from backend for this flow
}

export type RegisterResponse = ApiSuccessResponse<RegisterResponsePayload>;

export interface UserResponsePayload {
  user: UserFromResource; // User data from /api/user endpoint, shaped by UserResource
}

export type UserResponse = ApiSuccessResponse<UserResponsePayload>;

export interface RefreshTokenResponsePayload {
  user: UserFromResource;
  access_token: string;
  first_login: boolean; // Authoritative first_login from backend for this flow
}

export type RefreshTokenResponse = ApiSuccessResponse<RefreshTokenResponsePayload>;

export interface ChangePasswordResponsePayload {
  access_token: string;
  first_login: boolean;
}

export type ChangePasswordResponse = ApiSuccessResponse<ChangePasswordResponsePayload>;

export interface ApiErrorResponse {
  success: boolean;
  message: string;
  errors?: Record<string, string[]>;
}
