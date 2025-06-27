import apiService from './apiService';
import type {
  MediaItem,
  MediaPaginatedData,
  MediaUploadData,
  MediaUpdateData,
  MediaFilterParams,
  MediaItemWithLinkType
} from '@/types/media';

class MediaService {
  async getMedia(conferenceId: number, params?: MediaFilterParams): Promise<MediaPaginatedData> {
    try {
      const response = await apiService.get(`/v1/conference-management/conferences/${conferenceId}/media`, { params });
      return response.data.payload;
    } catch (error) {
      console.error('Error fetching media:', error);
      throw error;
    }
  }

  async uploadMedia(conferenceId: number, file: File, data: MediaUploadData): Promise<MediaItem> {
    try {
      const formData = new FormData();
      formData.append('file', file);
      formData.append('collection', data.collection);

      const response = await apiService.post(`/v1/conference-management/conferences/${conferenceId}/media`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      return response.data.payload;
    } catch (error) {
      console.error('Error uploading media:', error);
      throw error;
    }
  }

  async updateMedia(conferenceId: number, mediaId: number, data: MediaUpdateData): Promise<MediaItem> {
    try {
      const response = await apiService.put(`/v1/conference-management/conferences/${conferenceId}/media/${mediaId}`, data);
      return response.data.payload;
    } catch (error) {
      console.error('Error updating media:', error);
      throw error;
    }
  }

  async deleteMedia(conferenceId: number, mediaId: number): Promise<void> {
    try {
      await apiService.delete(`/v1/conference-management/conferences/${conferenceId}/media/${mediaId}`);
    } catch (error) {
      console.error('Error deleting media:', error);
      throw error;
    }
  }

  async downloadMediaFile(conferenceId: number, media: MediaItem): Promise<void> {
    try {
      const response = await apiService.get(`/v1/conferences/${conferenceId}/media/${media.id}/download`, {
        responseType: 'blob'
      });

      const blob = new Blob([response.data], { type: media.mime_type });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = media.file_name;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
    } catch (error) {
      console.error('Error downloading media:', error);
      throw error;
    }
  }

  getServeUrl(conferenceId: number, mediaId: number): string {
    const baseUrl = this.getBaseUrl();
    return `${baseUrl}/api/v1/conferences/${conferenceId}/media/${mediaId}/serve`;
  }

  getDownloadUrl(conferenceId: number, mediaId: number): string {
    const baseUrl = this.getBaseUrl();
    return `${baseUrl}/api/v1/conferences/${conferenceId}/media/${mediaId}/download`;
  }

  getUrlByType(conferenceId: number, media: MediaItemWithLinkType, linkType?: 'serve' | 'download'): string {
    const type = linkType || media.linkType || 'serve';

    if (type === 'download') {
      return media.download_url || this.getDownloadUrl(conferenceId, media.id);
    } else {
      return media.url || this.getServeUrl(conferenceId, media.id);
    }
  }

  createMediaContent(conferenceId: number, media: MediaItemWithLinkType, linkType?: 'serve' | 'download'): string {
    const type = linkType || media.linkType || 'serve';
    const url = this.getUrlByType(conferenceId, media, type);
    const fileIcon = this.getFileIconEmoji(media.mime_type);

    if (type === 'download') {
      return `<p><a href="${url}" target="_blank" download="${media.file_name}" title="Download ${media.file_name}">
        ${fileIcon} ${media.file_name}
      </a></p>`;
    } else {
      if (this.isImage(media.mime_type)) {
        return `<img src="${url}" alt="${media.file_name}" style="max-width: 100%; height: auto;" />`;
      } else {
        return `<p><a href="${url}" target="_blank" title="View ${media.file_name}">
          ${fileIcon} ${media.file_name}
        </a></p>`;
      }
    }
  }

  async batchDeleteMedia(conferenceId: number, mediaIds: number[]): Promise<void> {
    try {
      const deletePromises = mediaIds.map(id =>
        this.deleteMedia(conferenceId, id)
      );
      await Promise.all(deletePromises);
    } catch (error) {
      console.error('Error batch deleting media:', error);
      throw error;
    }
  }

  validateFile(file: File, collection: string): { valid: boolean; message?: string } {
    const allowedMimeTypes: Record<string, string[]> = {
      images: [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp', // Retained from original
      ],
      documents: [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/csv',
      ],
      general: [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp', // Retained from original
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/csv',
        'video/mp4',
        'video/avi', // Retained from original
        'video/mov', // Retained from original
        'audio/mpeg',
        'audio/wav',
      ]
    };

    const maxFileSize = 52428800; // 50MB
    const allowed = allowedMimeTypes[collection] || [];

    if (file.size > maxFileSize) {
      return {
        valid: false,
        message: `File size must be less than 50MB. Current size: ${this.formatFileSize(file.size)}`
      };
    }

    if (!allowed.includes(file.type)) {
      return {
        valid: false,
        message: `File type ${file.type} is not allowed for collection ${collection}`
      };
    }

    return { valid: true };
  }

  formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  isImage(mimeType: string): boolean {
    return mimeType.startsWith('image/');
  }

  isDocument(mimeType: string): boolean {
    return [
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'application/vnd.ms-powerpoint',
      'application/vnd.openxmlformats-officedocument.presentationml.presentation',
      'text/csv'
    ].includes(mimeType);
  }

  isVideo(mimeType: string): boolean {
    return mimeType.startsWith('video/');
  }

  getFileIcon(mimeType: string): string {
    if (this.isImage(mimeType)) return 'pi pi-image';
    if (mimeType === 'application/pdf') return 'pi pi-file-pdf';
    if (this.isDocument(mimeType)) return 'pi pi-file';
    if (this.isVideo(mimeType)) return 'pi pi-video';
    if (mimeType.startsWith('audio/')) return 'pi pi-music';
    return 'pi pi-file';
  }

  getFileIconEmoji(mimeType: string): string {
    if (this.isImage(mimeType)) return '🖼️';
    if (mimeType === 'application/pdf') return '📄';
    if (this.isDocument(mimeType)) return '📄';
    if (this.isVideo(mimeType)) return '🎥';
    if (mimeType.startsWith('audio/')) return '🎵';
    return '📁';
  }

  private getBaseUrl(): string {
    const protocol = window.location.protocol;
    const host = window.location.host;
    return `${protocol}//${host}`;
  }

  getThumbnailUrl(media: MediaItem): string {
    return media.conversions?.thumb || media.url;
  }

  getPreviewUrl(media: MediaItem): string {
    return media.conversions?.preview || media.url;
  }

  hasConversions(media: MediaItem): boolean {
    return !!(media.conversions?.thumb || media.conversions?.preview);
  }

  getCollectionDisplayName(collection: string): string {
    const collectionNames: Record<string, string> = {
      images: 'Images',
      documents: 'Documents',
      general: 'General'
    };
    return collectionNames[collection] || collection;
  }

  filterByMimeTypes(media: MediaItem[], allowedMimeTypes: string[]): MediaItem[] {
    if (allowedMimeTypes.length === 0) return media;

    return media.filter(item => {
      return allowedMimeTypes.some(type => {
        if (type.endsWith('/*')) {
          const baseType = type.replace('/*', '');
          return item.mime_type.startsWith(baseType);
        }
        return item.mime_type === type;
      });
    });
  }
}

const mediaService = new MediaService();
export default mediaService;