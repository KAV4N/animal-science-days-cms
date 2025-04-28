// src/types/university.ts

/**
 * University entity
 */
export interface University {
    id: number;
    full_name: string;
    country: string;
    city: string;
  }
  
  /**
   * University response structure
   */
  export interface UniversityResponse {
    success: boolean;
    message: string;
    data: University[];
    meta?: PaginationMeta;
    links?: PaginationLinks;
  }
  
  /**
   * Single university response structure
   */
  export interface SingleUniversityResponse {
    success: boolean;
    message: string;
    data: University;
  }
  
  /**
   * Pagination metadata structure
   */
  export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  }
  
  /**
   * Pagination links structure
   */
  export interface PaginationLinks {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  }