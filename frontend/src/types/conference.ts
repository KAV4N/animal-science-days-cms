// src/types/conference.ts
import type { University } from './university';
import type { User } from './user';
import type { PaginationMeta, PaginationLinks } from './university';

/**
 * Conference entity
 */
export interface EditorPivot {
  conference_id: number;
  assigned_by: number;
  assigned_by_user: User;
  created_at: string;
  updated_at: string;
}

export interface Editor {
  id: number;
  name: string;
  email: string;
  university?: University;
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

/**
 * Conference filter options
 */
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

/**
 * Conference editor entity
 */
export interface ConferenceEditor {
  id: number;
  conference_id: number;
  user: User;
  assigned_by: User;
  assigned_at: string;
}

/**
 * Conference creation payload
 */
export interface CreateConferencePayload {
  university_id: number;
  name: string;
  title: string;
  slug: string;
  description?: string;
  location: string;
  venue_details?: string;
  start_date: string;
  end_date: string;
  primary_color: string;
  secondary_color: string;
  is_latest?: boolean;
  is_published?: boolean;
}

/**
 * Conference update payload
 */
export interface UpdateConferencePayload {
  university_id?: number;
  name?: string;
  title?: string;
  slug?: string;
  description?: string | null;
  location?: string;
  venue_details?: string;
  start_date?: string;
  end_date?: string;
  primary_color?: string;
  secondary_color?: string;
  is_latest?: boolean;
  is_published?: boolean;
}

/**
 * Conference status update payload
 */
export interface ConferenceStatusPayload {
  published?: boolean;
  latest?: boolean;
}

/**
 * Conference response structure
 */
export interface ConferencePaginatedResponse {
  success: boolean;
  message: string;
  data: Conference[];
  meta: PaginationMeta;
  links: PaginationLinks;
}

export interface ConferenceListResponse {
  success: boolean;
  message: string;
  data: Conference[];
}

/**
 * Single conference response structure
 */
export interface SingleConferenceResponse {
  success: boolean;
  message: string;
  data: Conference;
}

/**
 * Conference editors response structure
 */
export interface ConferenceEditorsResponse {
  success: boolean;
  message: string;
  data: User[];
}


/**
 * Conference editor attachment payload
 */
export interface AttachEditorPayload {
  user_id: number;
}