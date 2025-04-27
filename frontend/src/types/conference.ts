// types/index.ts
import { type User } from "./user";
  
  // University related types
  export interface University {
    id: number;
    full_name: string;
    country: string;
    city: string;
    created_at: string;
    updated_at: string;
  }
  
  export interface UniversityResponse {
    data: University | University[];
    message: string;
  }
  
  // Conference related types
  export interface Conference {
    id: number;
    university_id: number;
    created_by: number | null;
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
    created_at: string;
    updated_at: string;
    university?: University;
    editors?: User[];
  }
  
  export interface ConferenceResponse {
    data: Conference | Conference[];
    message: string;
  }
  
  export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: {
      first: string;
      last: string;
      prev: string | null;
      next: string | null;
    };
  }
  
  // Conference Editor related types
  export interface ConferenceEditor {
    id: number;
    conference_id: number;
    user_id: number;
    assigned_by: number;
    assigned_at: string;
    user?: User;
    assignedByUser?: User;
  }
  
  export interface ConferenceEditorResponse {
    data: ConferenceEditor | ConferenceEditor[];
    message: string;
  }
  
  // Generic response type
  export interface ApiResponse<T> {
    data: T;
    message: string;
  }