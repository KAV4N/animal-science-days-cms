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

    <!-- Media Browser Dialog -->
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
                  <Select
                    v-model="mediaSelectedCollection"
                    :options="mediaCollectionOptions"
                    option-label="label"
                    option-value="value"
                    placeholder="All Collections"
                    class="w-40"
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
          <div v-else-if="browserMedia.length === 0" class="flex items-center justify-center h-64">
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
              v-for="item in browserMedia" 
              :key="item.id"
              class="group hover:shadow-lg transition-all duration-200 cursor-pointer"
              @click="selectMediaItem(item)"
            >
              <template #content>
                <div class="p-0">
                  <!-- Media Preview -->
                  <div class="aspect-square bg-gray-100 rounded-lg mb-4 overflow-hidden relative">
                    <!-- Image Preview -->
                    <img 
                      v-if="isImage(item.mime_type) && item.conversions.thumb"
                      :src="item.conversions.thumb"
                      :alt="item.file_name"
                      class="w-full h-full object-cover"
                    />
                    <!-- Document Icon -->
                    <div 
                      v-else-if="isDocument(item.mime_type)"
                      class="w-full h-full flex items-center justify-center"
                    >
                      <i class="pi pi-file-pdf text-6xl text-red-500" v-if="item.mime_type === 'application/pdf'"></i>
                      <i class="pi pi-file text-6xl text-blue-500" v-else></i>
                    </div>
                    <!-- Video Icon -->
                    <div 
                      v-else-if="isVideo(item.mime_type)"
                      class="w-full h-full flex items-center justify-center"
                    >
                      <i class="pi pi-video text-6xl text-purple-500"></i>
                    </div>
                    <!-- Generic File Icon -->
                    <div 
                      v-else
                      class="w-full h-full flex items-center justify-center"
                    >
                      <i class="pi pi-file text-6xl text-gray-500"></i>
                    </div>
                    
                    <!-- Selection Indicator -->
                    <div class="absolute top-2 right-2">
                      <Button
                        icon="pi pi-check"
                        class="rounded-full"
                        size="small"
                        severity="success"
                      />
                    </div>
                  </div>

                  <!-- Media Info -->
                  <div class="space-y-2">
                    <h4 class="font-semibold text-sm truncate" :title="item.file_name">
                      {{ item.file_name }}
                    </h4>
                    <div class="flex items-center justify-between text-xs text-gray-600">
                      <Badge 
                        :value="item.collection_name" 
                        class="text-xs"
                      />
                      <span>{{ item.size_human }}</span>
                    </div>
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
      
      mediaCollectionOptions: [
        { label: 'All Collections', value: '' },
        { label: 'Images', value: 'images' },
        { label: 'Documents', value: 'documents' },
        { label: 'General', value: 'general' }
      ],
      
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
          'bullist numlist outdent indent | link | removeformat | help'
        ].join(' | '),
        mobile: {
          menubar: false,
          toolbar: 'undo redo | bold italic | bullist numlist | insert'
        },
        setup: (editor: any) => {
          this.editorInstance = editor;
          
          editor.ui.registry.addMenuItem('media_library', {
            text: 'Media Library...',
            icon: 'browse',
            onAction: () => {
              this.openMediaBrowser();
            }
          });

          editor.ui.registry.addMenuItem('downloadable_files', {
            text: 'Downloadable Files...',
            icon: 'download',
            onAction: () => {
              this.openMediaBrowser();
            }
          });
          
          editor.ui.registry.addMenuButton('insert', {
            text: 'Insert',
            fetch: (callback: Function) => {
              const items = [
                'image',
                'media_library',
                '|',
                'link',
                'anchor',
                '|',
                'insertdatetime',
                'nonbreakingspace',
                'pagebreak',
                'horizontalrule'
              ];
              callback(items);
            }
          });

          editor.ui.registry.addNestedMenuItem('media_files', {
            text: 'Media Files',
            icon: 'embed',
            getSubmenuItems: () => [
              'media',
              'downloadable_files'
            ]
          });
          
          editor.ui.registry.addButton('image', {
            icon: 'image',
            tooltip: 'Insert/Edit Image',
            onAction: () => {
              this.openMediaBrowser();
            }
          });
        },
        images_upload_handler: (blobInfo: any, success: Function, failure: Function) => {
          failure('Please use the Media Library to upload and insert images');
        },
        file_picker_callback: (callback: Function, value: string, meta: any) => {
          if (meta.filetype === 'image' || meta.filetype === 'media') {
            this.openMediaBrowser(callback);
          }
        },
        menu: {
          insert: {
            title: 'Insert',
            items: 'image media_library | link anchor | insertdatetime nonbreakingspace pagebreak horizontalrule'
          },
          media: {
            title: 'Media',
            items: 'media downloadable_files'
          }
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
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            text-decoration: none;
            color: #374151;
            font-weight: 500;
            transition: all 0.2s;
          }
          .download-link:hover {
            background: #e5e7eb;
            border-color: #9ca3af;
            text-decoration: none;
          }
          .download-icon {
            font-size: 1.2em;
          }
        `
      };
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
    
    openMediaBrowser(callback?: Function) {
      this.showMediaBrowser = true;
      this.mediaCallback = callback;
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
    
    selectMediaItem(media: MediaItem) {
      if (this.editorInstance) {
        let content = '';
        
        if (this.isImage(media.mime_type)) {
          const imageUrl = media.download_url || `/v1/conferences/${this.conferenceId}/media/${media.id}/download`;
          const altText = media.file_name;
          
          content = `<img src="${imageUrl}" alt="${altText}" title="${altText}" style="max-width: 100%; height: auto;" />`;
          
          this.editorInstance.insertContent(content);
        } else if (this.isDocument(media.mime_type) || this.isVideo(media.mime_type)) {
          const linkUrl = media.download_url || `/v1/conferences/${this.conferenceId}/media/${media.id}/download`;
          const linkText = media.file_name;
          
          content = `<p><a href="${linkUrl}" target="_blank" download="${media.file_name}" class="download-link" title="Download ${linkText}">
            <span class="download-icon">📁</span> ${linkText}
          </a></p>`;
          
          this.editorInstance.insertContent(content);
        }
        
        if (this.mediaCallback) {
          const callbackUrl = media.download_url || `/v1/conferences/${this.conferenceId}/media/${media.id}/download`;
          this.mediaCallback(callbackUrl, { alt: media.file_name });
          this.mediaCallback = null;
        }
      }
      
      this.showMediaBrowser = false;
    },
    
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
    
    getFileIcon(mimeType: string): string {
      if (this.isImage(mimeType)) return 'pi pi-image';
      if (mimeType === 'application/pdf') return 'pi pi-file-pdf';
      if (this.isDocument(mimeType)) return 'pi pi-file';
      if (this.isVideo(mimeType)) return 'pi pi-video';
      return 'pi pi-file';
    }
  }
});
</script>