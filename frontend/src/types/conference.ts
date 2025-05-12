import type { ApiResponse, ApiPaginatedResponse } from './common';
import type { University } from './university';
import type { User } from './user';

export interface EditorPivot {
  conference_id: number;
  assigned_by: number;
  assigned_by_user: User;
  created_at: string;
  updated_at: string;
}

export interface Editor extends User {
  pivot: EditorPivot;
}

export interface Conference {
  id: number;
  name: string;
  title: string;
  slug: string;
  description: string | null;
  location: string;
  venue_details: string | null;
  start_date: string;
  end_date: string;
  primary_color: string;
  secondary_color: string;
  is_latest: boolean;
  is_published: boolean;
  university?: University;
  editors?: Editor[];
  created_at: string;
  updated_at: string;
}

export interface ConferenceStoreRequest {
  university_id: number;
  name: string;
  title: string;
  slug: string;
  description?: string | null;
  location: string;
  venue_details?: string | null;
  start_date: string;
  end_date: string;
  primary_color: string;
  secondary_color: string;
  is_latest?: boolean;
  is_published?: boolean;
}

export interface ConferenceUpdateRequest extends Partial<ConferenceStoreRequest> {}

export interface ConferenceEditorStoreRequest {
  user_id: number;
}

export interface ConferenceFilters {
  search?: string;
  university_id?: number;
  is_published?: boolean;
  is_latest?: boolean;
  start_date_after?: string;
  end_date_before?: string;
  sort_field?: 'name' | 'title' | 'start_date' | 'end_date' | 'created_at' | 'updated_at';
  sort_order?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}

export type ConferenceResponse = ApiResponse<Conference>;
export type ConferenceListResponse = ApiResponse<Conference[]>;
export type ConferencePaginatedResponse = ApiPaginatedResponse<Conference[]>;
export type ConferenceEditorsResponse = ApiResponse<User[]>;