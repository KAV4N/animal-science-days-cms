import type { ApiResponse, ApiPaginatedResponse } from '../types/common';
import type { University } from '../types/university';

export interface User {
  id: number;
  name: string;
  email: string;
  university?: University;
  university_id?: number | null;
  roles: string[];
  permissions: string[];
  must_change_password: boolean;
  created_at?: string;
  updated_at?: string;
}

export type UserResponse = ApiResponse<User>;
export type UserListResponse = ApiResponse<User[]>;
export type UserPaginatedResponse = ApiPaginatedResponse<User[]>;