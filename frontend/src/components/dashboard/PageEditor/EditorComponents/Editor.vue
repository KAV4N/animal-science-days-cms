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
  </Dialog>
  
  <!-- MediaManager Integration -->
  <MediaManager
    v-model:visible="showMediaManager"
    :selectionMode="mediaSelectionMode"
    :allowedMimeTypes="allowedMimeTypes"
    @select="handleMediaSelect"
    :conferenceId="conferenceId"
  />
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import Editor from '@hugerte/hugerte-vue';
import MediaManager from '@/components/dashboard/PageEditor/MediaManager.vue'; 
import apiService from '@/services/apiService';

interface ComponentData {
  content: string;
}

export default defineComponent({
  name: 'EditorComponent',
  components: {
    Editor,
    MediaManager
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
      showMediaManager: false,
      mediaSelectionMode: 'single' as 'single' | 'multiple',
      allowedMimeTypes: [] as string[],
      editorInstance: null as any,
      filePickerCallback: null as Function | null
    };
  },
  computed: {
    editorConfig() {
      return {
        height: '100%',
        menubar: true,
        plugins: [
          'accordion', 'advlist', 'anchor', 'autolink', 'autoresize', 
          'charmap', 'code', 'codesample', 'directionality', 'fullscreen', 
          'insertdatetime', 'lists', 'nonbreaking',
          'pagebreak', 
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
        // Disable image upload functionality
        images_upload_url: false,
        images_upload_handler: (blobInfo: any, success: Function, failure: Function) => {
          failure('Direct image upload is not allowed. Please use the Media Library or insert images by URL.');
        },
        // Disable automatic uploads
        automatic_uploads: false,
        // Disable paste data images
        paste_data_images: false,
        // Disable drag and drop file uploads
        paste_block_drop: true,
        setup: (editor: any) => {
          this.editorInstance = editor;
          
          // Custom Media Library button
          editor.ui.registry.addMenuItem('media_library', {
            text: 'Media Library...',
            icon: 'browse',
            onAction: () => {
              this.showMediaManager = true;
              this.mediaSelectionMode = 'single';
              this.allowedMimeTypes = [];
            }
          });

          // Custom Downloadable Files button
          editor.ui.registry.addMenuItem('downloadable_files', {
            text: 'Downloadable Files...',
            icon: 'download',
            onAction: () => {
              this.showMediaManager = true;
              this.mediaSelectionMode = 'single';
              this.allowedMimeTypes = [];
            }
          });
          
          // Custom Insert menu
          editor.ui.registry.addMenuButton('insert', {
            text: 'Insert',
            fetch: (callback: Function) => {
              const items = [
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

          // Custom Media Files submenu
          editor.ui.registry.addNestedMenuItem('media_files', {
            text: 'Media Files',
            icon: 'embed',
            getSubmenuItems: () => [
              'media_library',
              'downloadable_files'
            ]
          });
          
          // Override default image button to use Media Library
          editor.ui.registry.addButton('image', {
            icon: 'image',
            tooltip: 'Insert Image from Media Library',
            onAction: () => {
              this.showMediaManager = true;
              this.mediaSelectionMode = 'single';
              this.allowedMimeTypes = ['image/*'];
            }
          });

          // Block drag and drop uploads
          editor.on('dragover dragenter', (e: Event) => {
            e.preventDefault();
            e.stopPropagation();
          });

          editor.on('drop', (e: any) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Check if files are being dropped
            if (e.dataTransfer?.files?.length > 0) {
              editor.notificationManager.open({
                text: 'Direct file uploads are not allowed. Please use the Media Library to insert images and files.',
                type: 'warning',
                timeout: 5000
              });
              return false;
            }
          });

          // Block paste with files
          editor.on('paste', (e: any) => {
            const clipboardData = e.clipboardData || e.originalEvent?.clipboardData;
            if (clipboardData?.files?.length > 0) {
              e.preventDefault();
              editor.notificationManager.open({
                text: 'Pasting files directly is not allowed. Please use the Media Library or insert images by URL.',
                type: 'warning',
                timeout: 5000
              });
              return false;
            }
          });
        },
        // Custom file picker that only allows Media Library
        file_picker_callback: (callback: Function, value: string, meta: any) => {
          if (meta.filetype === 'image' || meta.filetype === 'media') {
            this.filePickerCallback = callback;
            this.showMediaManager = true;
            this.mediaSelectionMode = 'single';
            this.allowedMimeTypes = meta.filetype === 'image' ? ['image/*'] : [];
          }
        },
        menu: {
          insert: {
            title: 'Insert',
            items: 'media_library | link anchor | insertdatetime nonbreakingspace pagebreak horizontalrule'
          },
          media: {
            title: 'Media',
            items: 'media_library downloadable_files'
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
    
    handleMediaSelect(selectedItem: any) {
      if (selectedItem) {
        const url = selectedItem.download_url || `/v1/conferences/${this.conferenceId}/media/${selectedItem.id}/download`;
        if (this.filePickerCallback) {
          this.filePickerCallback(url, { alt: selectedItem.file_name });
          this.filePickerCallback = null;
        } else if (this.editorInstance) {
          let content = '';
          if (this.isImage(selectedItem.mime_type)) {
            content = `<img src="${url}" alt="${selectedItem.file_name}" style="max-width: 100%; height: auto;" />`;
          } else {
            content = `<p><a href="${url}" target="_blank" download="${selectedItem.file_name}" title="Download ${selectedItem.file_name}">
              📁 ${selectedItem.file_name}
            </a></p>`;
          }
          this.editorInstance.insertContent(content);
        }
      }
      this.showMediaManager = false;
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
    }
  }
});
</script>