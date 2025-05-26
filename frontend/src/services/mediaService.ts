import apiService from './apiService';
import type { 
  MediaItem, 
  MediaPaginatedData, 
  MediaUploadData, 
  MediaUpdateData,
  MediaFilterParams,
  MediaService,
  ApiResponse,
  ApiPaginatedResponse
} from '@/types/media';

class MediaApiService implements MediaService {
  /**
   * Get paginated media for a conference
   */
  async getMedia(conferenceId: number, params?: MediaFilterParams): Promise<MediaPaginatedData> {
    const response = await apiService.get<ApiPaginatedResponse<MediaItem[] | MediaPaginatedData>>(
      `/v1/conferences/${conferenceId}/media`,
      { params }
    );
    
    // Handle different response structures from your Laravel backend
    if (Array.isArray(response.data.payload)) {
      // Direct array response
      return {
        data: response.data.payload,
        current_page: response.data.meta?.current_page || 1,
        last_page: response.data.meta?.last_page || 1,
        per_page: response.data.meta?.per_page || response.data.payload.length,
        total: response.data.meta?.total || response.data.payload.length
      };
    } else {
      // Already paginated response
      return response.data.payload as MediaPaginatedData;
    }
  }

  /**
   * Upload a single media file
   */
  async uploadMedia(conferenceId: number, file: File, data: MediaUploadData): Promise<MediaItem> {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('collection', data.collection);
    
    if (data.name) {
      formData.append('name', data.name);
    }
    
    if (data.custom_properties && Object.keys(data.custom_properties).length > 0) {
      formData.append('custom_properties', JSON.stringify(data.custom_properties));
    }
    
    const response = await apiService.post<ApiResponse<MediaItem>>(
      `/v1/conferences/${conferenceId}/media`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    );
    
    return response.data.payload;
  }

  /**
   * Update media metadata
   */
  async updateMedia(conferenceId: number, mediaId: number, data: MediaUpdateData): Promise<MediaItem> {
    const response = await apiService.put<ApiResponse<MediaItem>>(
      `/v1/conferences/${conferenceId}/media/${mediaId}`,
      data
    );
    return response.data.payload;
  }

  /**
   * Delete a media file
   */
  async deleteMedia(conferenceId: number, mediaId: number): Promise<void> {
    await apiService.delete(`/v1/conferences/${conferenceId}/media/${mediaId}`);
  }

  /**
   * Download a media file
   */
  async downloadMedia(conferenceId: number, mediaId: number): Promise<Blob> {
    const response = await apiService.get(
      `/v1/conferences/${conferenceId}/media/${mediaId}/download`,
      { responseType: 'blob' }
    );
    return response.data;
  }

  /**
   * Get media by ID
   */
  async getMediaById(conferenceId: number, mediaId: number): Promise<MediaItem> {
    const response = await apiService.get<ApiResponse<MediaItem>>(
      `/v1/conferences/${conferenceId}/media/${mediaId}`
    );
    return response.data.payload;
  }

  /**
   * Upload multiple files
   */
  async uploadMultipleFiles(
    conferenceId: number, 
    files: File[], 
    data: MediaUploadData
  ): Promise<MediaItem[]> {
    const uploadPromises = files.map(file => this.uploadMedia(conferenceId, file, data));
    return Promise.all(uploadPromises);
  }

  /**
   * Check if file type is allowed for collection
   */
  validateFileForCollection(file: File, collection: string): { valid: boolean; message?: string } {
    const allowedTypes: Record<string, string[]> = {
      'images': ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
      'documents': [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      ],
      'general': [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'video/mp4', 'video/avi', 'video/mov'
      ]
    };

    const allowed = allowedTypes[collection] || [];
    
    if (!allowed.includes(file.type)) {
      return {
        valid: false,
        message: `File type ${file.type} is not allowed for collection ${collection}`
      };
    }

    return { valid: true };
  }

  /**
   * Format file size to human readable format
   */
  formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  /**
   * Check if file is an image
   */
  isImage(mimeType: string): boolean {
    return mimeType.startsWith('image/');
  }

  /**
   * Check if file is a document
   */
  isDocument(mimeType: string): boolean {
    return [
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ].includes(mimeType);
  }

  /**
   * Check if file is a video
   */
  isVideo(mimeType: string): boolean {
    return mimeType.startsWith('video/');
  }

  /**
   * Get appropriate icon for file type
   */
  getFileIcon(mimeType: string): string {
    if (this.isImage(mimeType)) return 'pi pi-image';
    if (mimeType === 'application/pdf') return 'pi pi-file-pdf';
    if (this.isDocument(mimeType)) return 'pi pi-file';
    if (this.isVideo(mimeType)) return 'pi pi-video';
    return 'pi pi-file';
  }
}

// Export singleton instance
export const mediaService = new MediaApiService();
export default mediaService;