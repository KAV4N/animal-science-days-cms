// Media-specific interfaces

export interface MediaConversions {
  thumb?: string;
  preview?: string;
}

export interface MediaItem {
  id: number;
  uuid: string;
  collection_name: string;
  file_name: string;
  mime_type: string;
  size: number;
  size_human: string;
  url: string;
  download_url?: string;
  conversions: MediaConversions;
  uploaded_by: number;
  created_at: string;
  updated_at: string;
  linkType?: 'serve' | 'download'; // New property for link type selection
}

export interface MediaUploadData {
  collection: string;
}

export interface MediaUpdateData {
  file_name?: string;
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
  mime_types?: string[]; // Added support for MIME type filtering
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

// Link type selection interfaces
export interface MediaLinkTypeSelection {
  type: 'serve' | 'download';
  description: string;
  icon: string;
  example: string;
}

export const MEDIA_LINK_TYPES: Record<string, MediaLinkTypeSelection> = {
  serve: {
    type: 'serve',
    description: 'Creates embedded content or a link that displays the file directly in the browser. Images and videos will be embedded inline, while documents open in browser viewer.',
    icon: 'pi pi-eye',
    example: 'Embeds images/videos inline, opens PDFs in browser viewer'
  },
  download: {
    type: 'download',
    description: 'Creates a link that downloads the file to the user\'s device. Best for documents, files, and resources that users need to save locally.',
    icon: 'pi pi-download',
    example: 'Downloads file with original filename'
  }
} as const;

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
  // New properties for link type selection
  showLinkTypeDialog: boolean;
  pendingMediaSelection: MediaItem | null;
}

// Extended media item for editor integration
export interface MediaItemWithLinkType extends MediaItem {
  linkType?: 'serve' | 'download';
}

// Helper functions for media handling
export const MediaHelpers = {
  isImage: (mimeType: string): boolean => mimeType.startsWith('image/'),
  
  isDocument: (mimeType: string): boolean => [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
  ].includes(mimeType),
  
  isVideo: (mimeType: string): boolean => mimeType.startsWith('video/'),
  
  getFileIcon: (mimeType: string): string => {
    if (MediaHelpers.isImage(mimeType)) return 'pi pi-image';
    if (mimeType === 'application/pdf') return 'pi pi-file-pdf';
    if (MediaHelpers.isDocument(mimeType)) return 'pi pi-file';
    if (MediaHelpers.isVideo(mimeType)) return 'pi pi-video';
    return 'pi pi-file';
  },
  
  getFileIconEmoji: (mimeType: string): string => {
    if (MediaHelpers.isImage(mimeType)) return '🖼️';
    if (mimeType === 'application/pdf') return '📄';
    if (MediaHelpers.isDocument(mimeType)) return '📄';
    if (MediaHelpers.isVideo(mimeType)) return '🎥';
    return '📁';
  },
  
  buildServeUrl: (conferenceId: number, mediaId: number, baseUrl: string): string => {
    return `${baseUrl}/api/v1/conferences/${conferenceId}/media/${mediaId}/serve`;
  },
  
  buildDownloadUrl: (conferenceId: number, mediaId: number, baseUrl: string): string => {
    return `${baseUrl}/api/v1/conferences/${conferenceId}/media/${mediaId}/download`;
  },
  
  formatFileSize: (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }
};