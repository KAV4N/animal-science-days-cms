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
  user: User;
  roles: string[];
  permissions: string[];
  access_token: string;
  first_login: boolean;
}

export type LoginResponse = ApiSuccessResponse<LoginResponsePayload>;

export interface RegisterResponsePayload {
  user: User;
  roles: string[];
  permissions: string[];
  access_token: string;
  first_login: boolean;
}

export type RegisterResponse = ApiSuccessResponse<RegisterResponsePayload>;

export interface UserResponsePayload {
  user: {
    id: number;
    name: string;
    email: string;
    first_login: boolean;
    university_id?: number;
    created_at?: string;
    updated_at?: string;
  };
  roles: string[];
  permissions: string[];
}

export type UserResponse = ApiSuccessResponse<UserResponsePayload>;

export interface RefreshTokenResponsePayload {
  user: User;
  roles: string[];
  permissions: string[];
  access_token: string;
  first_login: boolean;
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
