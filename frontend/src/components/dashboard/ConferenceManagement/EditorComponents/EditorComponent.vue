<template>
  <Dialog 
    v-model:visible="localVisible" 
    header="Edit Text Content" 
    :style="{ width: '95vw', maxWidth: '1400px', height: '90vh' }" 
    :modal="true"
    :maximizable="false"
    :closable="true"
    :breakpoints="{ '640px': '95vw' }"
    :contentStyle="{ height: 'calc(90vh - 100px)', padding: '1rem' }"
  >
    <Card class="h-full">
      <template #content>
        <div class="h-full flex flex-col p-0">
          <div class="text-center mb-4 flex-none">
            <div class="rounded-full p-4 w-12 h-12 mx-auto mb-4 flex items-center justify-center">
              <i class="pi pi-pencil text-lg"></i>
            </div>
            <h2 class="text-2xl font-bold mb-2">Edit Text Content</h2>
            <p class="text-sm">Modify your text content and settings</p>
          </div>
          
          <div class="flex-1 flex flex-col overflow-hidden min-h-0">
            <!-- Component Name Input -->
            <div class="mb-4 max-w-md mx-auto w-full flex-none">
              <label for="editorComponentName" class="block text-sm font-medium mb-2 text-center">Component Name</label>
              <InputText 
                id="editorComponentName" 
                v-model="localComponentName" 
                class="w-full text-center" 
              />
            </div>
            
            <!-- Content Editor -->
            <div class="flex-1 flex flex-col overflow-hidden mb-4 min-h-0">
              <label for="editContent" class="block text-sm font-medium mb-2 text-center flex-none">Content Editor</label>
              <Card class="flex-1 overflow-hidden min-h-0">
                <template #content>
                  <div class="h-full p-0">
                    <editor
                      v-model="localContent"
                      :init="editorConfig"
                    />
                  </div>
                </template>
              </Card>
            </div>
            
            <!-- Published Checkbox -->
            <Card class="max-w-md mx-auto w-full mb-4 flex-none">
              <template #content>
                <div class="flex items-center justify-center p-0">
                  <Checkbox v-model="localPublished" inputId="editorPublished" binary />
                  <label for="editorPublished" class="ml-4 text-sm font-medium">
                    Published
                  </label>
                </div>
                <p class="text-sm mt-3 text-center">Controls component visibility</p>
              </template>
            </Card>
          </div>
          
          <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4 flex-none">
            <Button 
              label="Cancel" 
              icon="pi pi-times" 
              @click="handleCancel" 
              outlined
              class="w-full sm:w-auto"
            />
            <Button 
              label="Save Changes" 
              icon="pi pi-check" 
              @click="handleSave" 
              autofocus
              class="w-full sm:w-auto"
            />
          </div>
        </div>
      </template>
    </Card>

    <!-- Enhanced Media Browser Dialog -->
    <Dialog 
      v-model:visible="showMediaBrowser" 
      header="Select Media" 
      :style="{ width: '95vw', maxWidth: '1000px', height: '80vh' }" 
      :modal="true"
      :maximizable="true"
      :closable="true"
      :breakpoints="{ '640px': '95vw' }"
    >
      <div class="h-full flex flex-col">
        <!-- Media Browser Header -->
        <Card class="mb-4 flex-shrink-0">
          <template #content>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 p-0">
              <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1">
                <!-- Collection Filter -->
                <div class="flex items-center gap-2">
                  <label class="text-sm font-medium whitespace-nowrap">Collection:</label>
                  <Dropdown
                    v-model="mediaSelectedCollection"
                    :options="mediaCollectionOptions"
                    option-label="label"
                    option-value="value"
                    placeholder="All Collections"
                    class="w-40"
                    @change="fetchMediaForBrowser"
                  />
                </div>
                
                <!-- File Type Filter -->
                <div class="flex items-center gap-2">
                  <label class="text-sm font-medium whitespace-nowrap">Type:</label>
                  <Dropdown
                    v-model="mediaTypeFilter"
                    :options="mediaTypeOptions"
                    option-label="label"
                    option-value="value"
                    placeholder="All Types"
                    class="w-32"
                    @change="fetchMediaForBrowser"
                  />
                </div>
                
                <!-- Search -->
                <div class="flex items-center gap-2 flex-1 min-w-0">
                  <label class="text-sm font-medium whitespace-nowrap">Search:</label>
                  <InputText
                    v-model="mediaSearchTerm"
                    placeholder="Search files..."
                    class="flex-1"
                    @keyup.enter="fetchMediaForBrowser"
                  />
                  <Button
                    icon="pi pi-search"
                    @click="fetchMediaForBrowser"
                    outlined
                    size="small"
                  />
                </div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Media Grid -->
        <div class="flex-1 overflow-y-auto">
          <!-- Loading State -->
          <div v-if="mediaLoading" class="flex items-center justify-center h-64">
            <Card>
              <template #content>
                <div class="text-center p-0">
                  <i class="pi pi-spin pi-spinner text-4xl mb-4"></i>
                  <p class="text-lg">Loading media files...</p>
                </div>
              </template>
            </Card>
          </div>

          <!-- Empty State -->
          <div v-else-if="filteredBrowserMedia.length === 0" class="flex items-center justify-center h-64">
            <Card>
              <template #content>
                <div class="text-center p-0">
                  <i class="pi pi-images text-6xl mb-4 text-gray-400"></i>
                  <h3 class="text-xl font-medium mb-4">No Media Files</h3>
                  <p class="mb-6">No media files found matching your criteria</p>
                </div>
              </template>
            </Card>
          </div>

          <!-- Media Grid -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <Card 
              v-for="item in filteredBrowserMedia" 
              :key="item.id"
              class="group hover:shadow-lg transition-all duration-200 cursor-pointer border-2 hover:border-blue-300"
              :class="{ 'border-blue-500': selectedMediaItem?.id === item.id }"
              @click="selectMediaForPreview(item)"
            >
              <template #content>
                <div class="p-0">
                  <!-- Media Preview -->
                  <div class="aspect-square bg-gray-100 rounded-lg mb-4 overflow-hidden relative">
                    <!-- Image Preview -->
                    <img 
                      v-if="isImage(item.mime_type) && item.conversions.thumb"
                      :src="item.conversions.thumb"
                      :alt="item.name"
                      class="w-full h-full object-cover"
                    />
                    <!-- Document Icon -->
                    <div 
                      v-else-if="isDocument(item.mime_type)"
                      class="w-full h-full flex items-center justify-center bg-red-50"
                    >
                      <i class="pi pi-file-pdf text-6xl text-red-500" v-if="item.mime_type === 'application/pdf'"></i>
                      <i class="pi pi-file-word text-6xl text-blue-600" v-else-if="isWordDocument(item.mime_type)"></i>
                      <i class="pi pi-file-excel text-6xl text-green-600" v-else-if="isExcelDocument(item.mime_type)"></i>
                      <i class="pi pi-file text-6xl text-blue-500" v-else></i>
                    </div>
                    <!-- Video Icon -->
                    <div 
                      v-else-if="isVideo(item.mime_type)"
                      class="w-full h-full flex items-center justify-center bg-purple-50"
                    >
                      <i class="pi pi-video text-6xl text-purple-500"></i>
                    </div>
                    <!-- Generic File Icon -->
                    <div 
                      v-else
                      class="w-full h-full flex items-center justify-center bg-gray-50"
                    >
                      <i class="pi pi-file text-6xl text-gray-500"></i>
                    </div>
                    
                    <!-- Download Badge for Non-Images -->
                    <div v-if="!isImage(item.mime_type)" class="absolute top-2 left-2">
                      <Badge value="Download" severity="info" class="text-xs" />
                    </div>
                    
                    <!-- File Size Badge -->
                    <div class="absolute bottom-2 right-2">
                      <Badge :value="item.size_human" severity="secondary" class="text-xs" />
                    </div>
                  </div>

                  <!-- Media Info -->
                  <div class="space-y-2">
                    <h4 class="font-semibold text-sm truncate" :title="item.name">
                      {{ item.name || item.file_name }}
                    </h4>
                    <div class="flex items-center justify-between text-xs text-gray-600">
                      <Badge 
                        :value="item.collection_name" 
                        class="text-xs"
                        :severity="getCollectionSeverity(item.collection_name)"
                      />
                      <span>{{ getFileTypeLabel(item.mime_type) }}</span>
                    </div>
                    <p class="text-xs text-gray-500 truncate" :title="item.file_name">
                      {{ item.file_name }}
                    </p>
                  </div>
                </div>
              </template>
            </Card>
          </div>

          <!-- Load More Button -->
          <div v-if="hasMoreMediaPages" class="text-center mt-6">
            <Button
              label="Load More"
              icon="pi pi-chevron-down"
              @click="loadMoreMedia"
              :loading="mediaLoadingMore"
              outlined
            />
          </div>
        </div>

        <!-- Media Selection Preview & Insert -->
        <Card v-if="selectedMediaItem" class="mt-4 flex-shrink-0">
          <template #content>
            <div class="flex flex-col sm:flex-row items-start gap-4 p-0">
              <!-- Preview -->
              <div class="flex-shrink-0">
                <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
                  <img 
                    v-if="isImage(selectedMediaItem.mime_type) && selectedMediaItem.conversions.thumb"
                    :src="selectedMediaItem.conversions.thumb"
                    :alt="selectedMediaItem.name"
                    class="w-full h-full object-cover"
                  />
                  <i v-else :class="getFileIcon(selectedMediaItem.mime_type)" class="text-3xl text-gray-500"></i>
                </div>
              </div>
              
              <!-- Info & Actions -->
              <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-base mb-2">{{ selectedMediaItem.name || selectedMediaItem.file_name }}</h4>
                <div class="flex flex-wrap gap-2 mb-3">
                  <Badge :value="selectedMediaItem.collection_name" />
                  <Badge :value="getFileTypeLabel(selectedMediaItem.mime_type)" severity="secondary" />
                  <Badge :value="selectedMediaItem.size_human" severity="info" />
                </div>
                
                <!-- Link Text Input for Non-Images -->
                <div v-if="!isImage(selectedMediaItem.mime_type)" class="mb-3">
                  <label class="block text-sm font-medium mb-1">Link Text:</label>
                  <InputText 
                    v-model="customLinkText" 
                    :placeholder="selectedMediaItem.name || selectedMediaItem.file_name"
                    class="w-full"
                  />
                </div>
                
                <!-- Link Style Options for Non-Images -->
                <div v-if="!isImage(selectedMediaItem.mime_type)" class="mb-3">
                  <label class="block text-sm font-medium mb-2">Link Style:</label>
                  <div class="flex flex-wrap gap-2">
                    <Button 
                      :label="`📄 ${customLinkText || selectedMediaItem.name || selectedMediaItem.file_name}`"
                      @click="insertMediaAsLink('simple')"
                      size="small"
                      outlined
                      class="text-left"
                    />
                    <Button 
                      :label="`⬇️ Download ${getFileTypeLabel(selectedMediaItem.mime_type)}`"
                      @click="insertMediaAsLink('download')"
                      size="small"
                      outlined
                      severity="info"
                    />
                    <Button 
                      label="📋 Button Style"
                      @click="insertMediaAsLink('button')"
                      size="small"
                      outlined
                      severity="success"
                    />
                  </div>
                </div>
                
                <!-- Insert Actions -->
                <div class="flex gap-2">
                  <Button
                    v-if="isImage(selectedMediaItem.mime_type)"
                    label="Insert Image"
                    icon="pi pi-image"
                    @click="insertMediaAsImage"
                    severity="success"
                    size="small"
                  />
                  <Button
                    v-else
                    label="Insert Link"
                    icon="pi pi-link"
                    @click="insertMediaAsLink('simple')"
                    severity="success"
                    size="small"
                  />
                  <Button
                    label="Cancel"
                    icon="pi pi-times"
                    @click="clearSelection"
                    outlined
                    size="small"
                  />
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>
    </Dialog>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import Editor from '@hugerte/hugerte-vue';
import apiService from '@/services/apiService';
import type { MediaItem } from '@/types/media';

interface ComponentData {
  content: string;
}

export default defineComponent({
  name: 'EditorComponent',
  components: {
    'editor': Editor
  },
  props: {
    visible: {
      type: Boolean,
      required: true
    },
    componentName: {
      type: String,
      default: ''
    },
    componentData: {
      type: Object as PropType<ComponentData>,
      default: () => ({ content: '<p>Enter content here...</p>' })
    },
    isPublished: {
      type: Boolean,
      default: false
    },
    conferenceId: {
      type: Number,
      required: true
    }
  },
  emits: ['update:visible', 'save', 'cancel'],
  data() {
    return {
      localComponentName: '',
      localContent: '<p>Enter content here...</p>',
      localPublished: false,
      localVisible: false,
      
      // Media browser state
      showMediaBrowser: false,
      browserMedia: [] as MediaItem[],
      mediaLoading: false,
      mediaLoadingMore: false,
      mediaCurrentPage: 1,
      hasMoreMediaPages: false,
      mediaSearchTerm: '',
      mediaSelectedCollection: '',
      mediaTypeFilter: '',
      
      // Media selection
      selectedMediaItem: null as MediaItem | null,
      customLinkText: '',
      
      mediaCollectionOptions: [
        { label: 'All Collections', value: '' },
        { label: 'Images', value: 'images' },
        { label: 'Documents', value: 'documents' },
        { label: 'General', value: 'general' }
      ],
      
      mediaTypeOptions: [
        { label: 'All Types', value: '' },
        { label: 'Images', value: 'image' },
        { label: 'Documents', value: 'document' },
        { label: 'Videos', value: 'video' }
      ],
      
      // TinyMCE editor instance
      editorInstance: null as any,
      mediaCallback: null as Function | null
    };
  },
  computed: {
    editorConfig() {
      return {
        height: '100%',
        menubar: true,
        plugins: [
          'accordion', 'advlist', 'anchor', 'autolink', 'autoresize', 'autosave', 
          'charmap', 'code', 'codesample', 'directionality', 'fullscreen', 
          'image', 'insertdatetime', 'link', 'lists', 'media', 'nonbreaking', 
          'pagebreak', 'preview', 'quickbars', 'save', 'searchreplace', 
          'table', 'template', 'visualblocks', 'visualchars', 'wordcount', 
          'emoticons', 'help'
        ].join(' '),
        toolbar: [
          'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify',
          'bullist numlist outdent indent | link image media_browser | removeformat | help'
        ].join(' | '),
        mobile: {
          menubar: false,
          toolbar: 'undo redo | bold italic | bullist numlist | image media_browser'
        },
        setup: (editor: any) => {
          this.editorInstance = editor;
          
          // Add custom button for media browser
          editor.ui.registry.addButton('media_browser', {
            icon: 'browse',
            tooltip: 'Browse Media Library',
            onAction: () => {
              this.openMediaBrowser();
            }
          });
          
          // Override default image button
          editor.ui.registry.addButton('image', {
            icon: 'image',
            tooltip: 'Insert Image/Media',
            onAction: () => {
              this.openMediaBrowser();
            }
          });
        },
        // Override default image upload
        images_upload_handler: (blobInfo: any, success: Function, failure: Function) => {
          failure('Please use the Media Library to upload and insert images');
        },
        // Customize file picker
        file_picker_callback: (callback: Function, value: string, meta: any) => {
          this.openMediaBrowser(callback);
        },
        content_style: `
          body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; 
            font-size: 14px; 
            line-height: 1.6;
          }
          img {
            max-width: 100%;
            height: auto;
          }
          .download-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            text-decoration: none;
            color: #374151;
            transition: all 0.2s;
          }
          .download-link:hover {
            background-color: #e5e7eb;
            text-decoration: none;
          }
          .download-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
          }
          .download-button:hover {
            background-color: #2563eb;
            text-decoration: none;
            color: white;
          }
        `
      };
    },
    
    filteredBrowserMedia() {
      if (!this.mediaTypeFilter) return this.browserMedia;
      
      return this.browserMedia.filter(item => {
        switch (this.mediaTypeFilter) {
          case 'image':
            return this.isImage(item.mime_type);
          case 'document':
            return this.isDocument(item.mime_type);
          case 'video':
            return this.isVideo(item.mime_type);
          default:
            return true;
        }
      });
    }
  },
  watch: {
    visible: {
      handler(newVal) {
        this.localVisible = newVal;
        if (newVal) {
          this.initializeData();
        }
      },
      immediate: true
    },
    localVisible(newVal) {
      if (!newVal && this.visible) {
        this.handleCancel();
      }
    },
    componentName: {
      handler(newVal) {
        this.localComponentName = newVal;
      },
      immediate: true
    },
    componentData: {
      handler(newVal) {
        if (newVal && newVal.content) {
          this.localContent = newVal.content;
        }
      },
      immediate: true,
      deep: true
    },
    isPublished: {
      handler(newVal) {
        this.localPublished = newVal;
      },
      immediate: true
    }
  },
  methods: {
    initializeData() {
      this.localComponentName = this.componentName;
      this.localContent = this.componentData?.content || '<p>Enter content here...</p>';
      this.localPublished = this.isPublished;
    },
    
    handleSave() {
      this.$emit('save', {
        name: this.localComponentName,
        data: { content: this.localContent },
        isPublished: this.localPublished
      });
    },
    
    handleCancel() {
      this.$emit('cancel');
      this.$emit('update:visible', false);
    },
    
    // Media browser methods
    openMediaBrowser(callback?: Function) {
      this.showMediaBrowser = true;
      this.mediaCallback = callback;
      this.clearSelection();
      this.fetchMediaForBrowser();
    },
    
    async fetchMediaForBrowser(reset = true) {
      if (reset) {
        this.mediaCurrentPage = 1;
        this.browserMedia = [];
      }
      
      this.mediaLoading = reset;
      this.mediaLoadingMore = !reset;
      
      try {
        const params: any = {
          page: this.mediaCurrentPage,
          per_page: 20
        };
        
        if (this.mediaSelectedCollection) {
          params.collection = this.mediaSelectedCollection;
        }
        
        if (this.mediaSearchTerm) {
          params.search = this.mediaSearchTerm;
        }
        
        const response = await apiService.get(`/v1/conferences/${this.conferenceId}/media`, { params });
        
        let newMedia: MediaItem[] = [];
        let paginationInfo: any = {};
        
        if (response.data.payload && Array.isArray(response.data.payload)) {
          newMedia = response.data.payload;
        } else if (response.data.payload && response.data.payload.data) {
          newMedia = response.data.payload.data;
        } else if (response.data.payload) {
          newMedia = response.data.payload;
        }
        
        if (response.data.meta) {
          paginationInfo = response.data.meta;
        }
        
        if (reset) {
          this.browserMedia = newMedia || [];
        } else {
          this.browserMedia.push(...(newMedia || []));
        }
        
        if (paginationInfo.current_page && paginationInfo.last_page) {
          this.hasMoreMediaPages = paginationInfo.current_page < paginationInfo.last_page;
        } else {
          this.hasMoreMediaPages = false;
        }
        
      } catch (error) {
        console.error('Error fetching media:', error);
        this.$toast?.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to fetch media files',
          life: 3000
        });
      } finally {
        this.mediaLoading = false;
        this.mediaLoadingMore = false;
      }
    },
    
    async loadMoreMedia() {
      this.mediaCurrentPage++;
      await this.fetchMediaForBrowser(false);
    },
    
    selectMediaForPreview(media: MediaItem) {
      this.selectedMediaItem = media;
      this.customLinkText = media.name || media.file_name;
    },
    
    clearSelection() {
      this.selectedMediaItem = null;
      this.customLinkText = '';
    },
    
    insertMediaAsImage() {
      if (!this.selectedMediaItem || !this.editorInstance) return;
      
      const imageUrl = this.selectedMediaItem.url;
      const altText = this.selectedMediaItem.name || this.selectedMediaItem.file_name;
      
      const content = `<img src="${imageUrl}" alt="${altText}" title="${altText}" style="max-width: 100%; height: auto;" />`;
      
      this.editorInstance.insertContent(content);
      
      if (this.mediaCallback) {
        this.mediaCallback(imageUrl, { alt: altText });
        this.mediaCallback = null;
      }
      
      this.showMediaBrowser = false;
      this.clearSelection();
    },
    
    insertMediaAsLink(style: 'simple' | 'download' | 'button' = 'simple') {
      if (!this.selectedMediaItem || !this.editorInstance) return;
      
      const media = this.selectedMediaItem;
      const linkText = this.customLinkText || media.name || media.file_name;
      const fileIcon = this.getFileIcon(media.mime_type);
      const fileType = this.getFileTypeLabel(media.mime_type);
      
      // Use download endpoint for documents, serve endpoint for images/videos
      const linkUrl = this.shouldUseDownloadEndpoint(media.mime_type) 
        ? `/v1/conferences/${this.conferenceId}/media/${media.id}/download`
        : media.url;
      
      let content = '';
      
      switch (style) {
        case 'simple':
          content = `<a href="${linkUrl}" target="_blank" title="Download ${linkText}" rel="noopener noreferrer">
            <i class="${fileIcon.replace('pi ', '')}"></i> ${linkText}
          </a>`;
          break;
          
        case 'download':
          content = `<a href="${linkUrl}" target="_blank" class="download-link" title="Download ${linkText}" rel="noopener noreferrer">
            <span>⬇️</span>
            <span>Download ${fileType}</span>
            <span>(${media.size_human})</span>
          </a>`;
          break;
          
        case 'button':
          content = `<a href="${linkUrl}" target="_blank" class="download-button" title="Download ${linkText}" rel="noopener noreferrer">
            <span>📁</span>
            <span>${linkText}</span>
            <span>(${media.size_human})</span>
          </a>`;
          break;
      }
      
      this.editorInstance.insertContent(content);
      
      if (this.mediaCallback) {
        this.mediaCallback(linkUrl, { text: linkText });
        this.mediaCallback = null;
      }
      
      this.showMediaBrowser = false;
      this.clearSelection();
    },
    
    // File type checking methods
    isImage(mimeType: string): boolean {
      return mimeType.startsWith('image/');
    },
    
    isDocument(mimeType: string): boolean {
      return [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      ].includes(mimeType);
    },
    
    isVideo(mimeType: string): boolean {
      return mimeType.startsWith('video/');
    },
    
    isWordDocument(mimeType: string): boolean {
      return [
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
      ].includes(mimeType);
    },
    
    isExcelDocument(mimeType: string): boolean {
      return [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      ].includes(mimeType);
    },
    
    getFileIcon(mimeType: string): string {
      if (this.isImage(mimeType)) return 'pi pi-image';
      if (mimeType === 'application/pdf') return 'pi pi-file-pdf';
      if (this.isWordDocument(mimeType)) return 'pi pi-file-word';
      if (this.isExcelDocument(mimeType)) return 'pi pi-file-excel';
      if (this.isDocument(mimeType)) return 'pi pi-file';
      if (this.isVideo(mimeType)) return 'pi pi-video';
      return 'pi pi-file';
    },
    
    getFileTypeLabel(mimeType: string): string {
      if (this.isImage(mimeType)) return 'Image';
      if (mimeType === 'application/pdf') return 'PDF';
      if (this.isWordDocument(mimeType)) return 'Word Document';
      if (this.isExcelDocument(mimeType)) return 'Excel Spreadsheet';
      if (this.isDocument(mimeType)) return 'Document';
      if (this.isVideo(mimeType)) return 'Video';
      return 'File';
    },
    
    getCollectionSeverity(collection: string): string {
      switch (collection) {
        case 'images': return 'success';
        case 'documents': return 'info';
        case 'general': return 'secondary';
        default: return 'secondary';
      }
    },
    
    shouldUseDownloadEndpoint(mimeType: string): boolean {
      // Use download endpoint for documents and other files that should be downloaded
      // Use serve endpoint for images and videos that can be displayed inline
      return this.isDocument(mimeType) || (!this.isImage(mimeType) && !this.isVideo(mimeType));
    }
  }
});
</script>

<style scoped>
.download-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background-color: #f3f4f6;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  text-decoration: none;
  color: #374151;
  transition: all 0.2s;
}

.download-link:hover {
  background-color: #e5e7eb;
  text-decoration: none;
}

.download-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background-color: #3b82f6;
  color: white;
  text-decoration: none;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s;
}

.download-button:hover {
  background-color: #2563eb;
  text-decoration: none;
  color: white;
}
</style>