export interface User {
  id: number;
  name: string;
  email: string;
  first_login: boolean;
  university_id?: number; // Added this field based on the API response
  roles?: string[]; // Added this field based on the API response
  permissions?: string[]; // Added this field based on the API response
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

// Updated API Response Types to match the actual API response structure
export interface LoginResponse {
  user: User;
  roles: string[];
  permissions: string[];
  access_token: string;
  first_login: boolean;
}

export interface RegisterResponse {
  user: User;
  roles: string[];
  permissions: string[];
  access_token: string;
  first_login: boolean;
}

export interface UserResponse {
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

export interface RefreshTokenResponse {
  user: User;
  roles: string[];
  permissions: string[];
  access_token: string;
  first_login: boolean;
}

export interface ChangePasswordResponse {
  access_token: string;
}

export interface ApiErrorResponse {
  success: boolean;
  message: string;
  errors?: Record<string, string[]>;
}
