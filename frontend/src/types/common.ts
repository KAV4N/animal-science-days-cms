// Common API response structures
export interface ApiResponse<T> {
    success: boolean;
    message: string;
    payload: T;
  }
  
  // Common pagination structures
  export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  }
  
  export interface PaginationLinks {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  }
  
  export interface ApiPaginatedResponse<T> extends ApiResponse<T> {
    meta: PaginationMeta;
    links: PaginationLinks;
  }
  
  export interface ApiErrorResponse {
    success: boolean;
    message: string;
    errors?: Record<string, string[]>;
  }