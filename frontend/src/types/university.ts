import type { ApiResponse, ApiPaginatedResponse } from './common';

export interface University {
  id: number;
  full_name: string;
  country: string;
  city: string;
}

export interface UniversityStoreRequest {
  full_name: string;
  country: string;
  city: string;
}

export interface UniversityUpdateRequest extends Partial<UniversityStoreRequest> {}

export type UniversityResponse = ApiResponse<University>;
export type UniversityListResponse = ApiResponse<University[]>;
export type UniversityPaginatedResponse = ApiPaginatedResponse<University[]>;