import type { ApiResponse, ApiPaginatedResponse } from './common';
import type { University } from './university';

export interface Role {
  id: number;
  name: string;
}

export interface Permission {
  id: number;
  name: string;
}


export interface User {
  id: number;
  name: string;
  email: string;
  university?: University;
  roles: Role[];
  permissions: Permission[];
  must_change_password: boolean;
  created_at?: string;
  updated_at?: string;
}
export type UserResponse = ApiResponse<User>;
export type UserListResponse = ApiResponse<User[]>;
export type UserPaginatedResponse = ApiPaginatedResponse<User[]>;