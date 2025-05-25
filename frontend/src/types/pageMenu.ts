import type { ApiResponse, ApiPaginatedResponse } from './common';

export interface PageData {
  id: number;
  menu_id: number;
  component_type: string;
  order: number;
  data: any;
  tag: string;
  is_published: boolean;
  created_at: string;
  updated_at: string;
  created_by: number;
  updated_by: number;
}

export interface PageMenu {
  id: number;
  conference_id: number;
  title: string;
  slug: string;
  order?: number;
  is_published: boolean;
  page_data?: PageData[];
  created_at: string;
  updated_at: string;
  created_by: number;
  updated_by: number;
}

export interface PageMenuStoreRequest {
  title: string;
  slug: string;
  is_published?: boolean;
  order?: number;
}

export interface PageMenuUpdateRequest {
  title?: string;
  slug?: string;
  is_published?: boolean;
  order?: number;
}

export interface PageDataStoreRequest {
  component_type: string;
  order: number;
  data: any;
  tag: string;
  is_published?: boolean;
}

export interface PageDataUpdateRequest {
  component_type?: string;
  order?: number;
  data?: any;
  tag?: string;
  is_published?: boolean;
}

export interface PageMenuPositionRequest {
  order: number;
}

export interface PageDataPositionRequest {
  order: number;
}

export type PageMenuResponse = ApiResponse<PageMenu>;
export type PageMenuListResponse = ApiPaginatedResponse<PageMenu[]>;
export type PageDataResponse = ApiResponse<PageData>;
export type PageDataListResponse = ApiPaginatedResponse<PageData[]>;