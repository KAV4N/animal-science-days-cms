// Common API response structures (matching your Laravel ApiResponse trait)
export interface ApiResponse<T> {
  success: boolean;
  message: string;
  payload: T;
}

export interface ApiPaginatedResponse<T> extends ApiResponse<T> {
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}

export interface ApiErrorResponse {
  success: boolean;
  message: string;
  errors?: Record<string, string[]>;
}

// Media-specific interfaces

export interface MediaConversions {
  thumb?: string;
  preview?: string;
}

export interface MediaItem {
  id: number;
  uuid: string;
  collection_name: string;
  name: string;
  file_name: string;
  mime_type: string;
  size: number;
  size_human: string;
  url: string;
  conversions: MediaConversions;
  uploaded_by: number;
  created_at: string;
  updated_at: string;
}

export interface MediaUploadData {
  collection: string;
  name?: string;
}

export interface MediaUpdateData {
  name?: string;
}

// Laravel pagination response structure (from your paginatedResponse method)
export interface MediaPaginatedData {
  data?: MediaItem[]; // Optional because payload might be direct array
  current_page?: number;
  last_page?: number;
  per_page?: number;
  total?: number;
  from?: number;
  to?: number;
  path?: string;
  first_page_url?: string;
  last_page_url?: string;
  next_page_url?: string | null;
  prev_page_url?: string | null;
}

export interface MediaFilterParams {
  collection?: string;
  search?: string;
  per_page?: number;
  page?: number;
}

export interface MediaCollectionOption {
  label: string;
  value: string;
}

export interface FileUploadEvent {
  files: File[];
}

export interface FileRemoveEvent {
  file: File;
}

// Media validation constants
export const MEDIA_COLLECTIONS = {
  IMAGES: 'images',
  DOCUMENTS: 'documents',
  GENERAL: 'general'
} as const;

export type MediaCollection = typeof MEDIA_COLLECTIONS[keyof typeof MEDIA_COLLECTIONS];

export const ALLOWED_MIME_TYPES = {
  [MEDIA_COLLECTIONS.IMAGES]: [
    'image/jpeg',
    'image/png', 
    'image/gif',
    'image/webp'
  ],
  [MEDIA_COLLECTIONS.DOCUMENTS]: [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
  ],
  [MEDIA_COLLECTIONS.GENERAL]: [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'video/mp4',
    'video/avi',
    'video/mov'
  ]
} as const;

export const MAX_FILE_SIZE = 52428800; // 50MB in bytes

// Media service interfaces
export interface MediaService {
  getMedia(conferenceId: number, params?: MediaFilterParams): Promise<MediaPaginatedData>;
  uploadMedia(conferenceId: number, file: File, data: MediaUploadData): Promise<MediaItem>;
  updateMedia(conferenceId: number, mediaId: number, data: MediaUpdateData): Promise<MediaItem>;
  deleteMedia(conferenceId: number, mediaId: number): Promise<void>;
  downloadMedia(conferenceId: number, mediaId: number): Promise<Blob>;
}

// Error handling for media operations
export interface MediaError {
  type: 'upload' | 'update' | 'delete' | 'fetch';
  message: string;
  details?: any;
}

// Media component state interface
export interface MediaManagerState {
  media: MediaItem[];
  loading: boolean;
  loadingMore: boolean;
  currentPage: number;
  hasMorePages: boolean;
  searchTerm: string;
  selectedCollection: string;
  uploading: boolean;
  updating: boolean;
  deleting: boolean;
  error: MediaError | null;
}