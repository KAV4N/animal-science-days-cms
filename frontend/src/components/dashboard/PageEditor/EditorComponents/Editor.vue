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

  <!-- Custom Link Dialog -->
  <Dialog
    v-model:visible="showLinkDialog"
    header="Insert/Edit Link"
    :style="{ width: '90vw', maxWidth: '600px' }"
    :modal="true"
    :closable="true"
  >
    <div class="flex flex-col gap-4">
      <div>
        <label for="linkUrl" class="block text-sm font-medium mb-2">URL</label>
        <InputText
          id="linkUrl"
          v-model="linkData.url"
          placeholder="https://example.com"
          class="w-full"
          autofocus
        />
        <small class="text-gray-500">Enter the full URL including http:// or https://</small>
      </div>
      
      <div>
        <label for="linkText" class="block text-sm font-medium mb-2">Link Text</label>
        <InputText
          id="linkText"
          v-model="linkData.text"
          placeholder="Link text to display"
          class="w-full"
        />
        <small class="text-gray-500">Text that will be displayed as the link</small>
      </div>
      
      <div>
        <label for="linkTitle" class="block text-sm font-medium mb-2">Title (Optional)</label>
        <InputText
          id="linkTitle"
          v-model="linkData.title"
          placeholder="Additional information about the link"
          class="w-full"
        />
        <small class="text-gray-500">Tooltip text that appears when hovering over the link</small>
      </div>
      
      <div class="flex items-center gap-2">
        <Checkbox v-model="linkData.openInNewTab" inputId="linkNewTab" binary />
        <label for="linkNewTab" class="text-sm font-medium">Open in new tab</label>
      </div>
      
      <div class="flex items-center gap-2">
        <Checkbox v-model="linkData.noFollow" inputId="linkNoFollow" binary />
        <label for="linkNoFollow" class="text-sm font-medium">Add rel="nofollow"</label>
        <small class="text-gray-500 ml-2">(for SEO purposes)</small>
      </div>
    </div>
    
    <template #footer>
      <div class="flex justify-end gap-2">
        <Button
          label="Cancel"
          icon="pi pi-times"
          @click="closeLinkDialog"
          outlined
        />
        <Button
          label="Remove Link"
          icon="pi pi-trash"
          @click="removeLink"
          severity="danger"
          outlined
          v-if="isEditingExistingLink"
        />
        <Button
          label="Insert Link"
          icon="pi pi-check"
          @click="insertLink"
          :disabled="!linkData.url"
        />
      </div>
    </template>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import Editor from '@hugerte/hugerte-vue';
import MediaManager from '@/components/dashboard/PageEditor/MediaManager.vue'; 
import apiService from '@/services/apiService';

interface ComponentData {
  content: string;
}

interface MediaItemWithLinkType {
  id: number;
  uuid: string;
  collection_name: string;
  file_name: string;
  mime_type: string;
  size: number;
  size_human: string;
  url: string;
  download_url?: string;
  conversions: any;
  uploaded_by: number;
  created_at: string;
  updated_at: string;
  linkType?: 'serve' | 'download';
}

interface LinkData {
  url: string;
  text: string;
  title: string;
  openInNewTab: boolean;
  noFollow: boolean;
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
      showLinkDialog: false,
      mediaSelectionMode: 'single' as 'single' | 'multiple',
      allowedMimeTypes: [] as string[],
      editorInstance: null as any,
      filePickerCallback: null as Function | null,
      linkCallback: null as Function | null,
      isEditingExistingLink: false,
      linkData: {
        url: '',
        text: '',
        title: '',
        openInNewTab: false,
        noFollow: false
      } as LinkData
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
          'insertdatetime', 'link', 'lists', 'nonbreaking',
          'pagebreak', 
          'table', 'template', 'visualblocks', 'visualchars', 'wordcount', 
          'emoticons', 'help'
        ].join(' '),
        toolbar: [
          'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify',
          'bullist numlist outdent indent | customlink unlink | removeformat | help'
        ].join(' | '),
        mobile: {
          menubar: false,
          toolbar: 'undo redo | bold italic | bullist numlist | customlink | insert'
        },
        // Link configuration
        link_assume_external_targets: true,
        link_context_toolbar: true,
        default_link_target: '_blank',
        target_list: [
          { title: 'Same window', value: '' },
          { title: 'New window', value: '_blank' }
        ],
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
          
          // Custom Link button
          editor.ui.registry.addButton('customlink', {
            icon: 'link',
            tooltip: 'Insert/Edit Link',
            onAction: () => {
              this.openLinkDialog();
            }
          });

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

          // Handle double-click on links to edit them
          editor.on('dblclick', (e: any) => {
            const target = e.target;
            if (target.tagName === 'A') {
              e.preventDefault();
              this.editExistingLink(target);
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
          video {
            max-width: 100%;
            height: auto;
          }
          a {
            color: #3b82f6;
            text-decoration: underline;
          }
          a:hover {
            color: #1d4ed8;
          }
        `,
        // Add convert_urls configuration to handle URL conversion properly
        convert_urls: false,
        relative_urls: false,
        remove_script_host: false,
        document_base_url: this.getBaseUrl()
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
    
    getBaseUrl(): string {
      // Get the current base URL for the application
      const protocol = window.location.protocol;
      const host = window.location.host;
      return `${protocol}//${host}`;
    },
    
    // Link Dialog Methods
    openLinkDialog() {
      this.resetLinkData();
      
      if (this.editorInstance) {
        const selection = this.editorInstance.selection;
        const selectedText = selection.getContent({ format: 'text' });
        
        // Check if we're editing an existing link
        const linkElement = this.editorInstance.dom.getParent(selection.getNode(), 'a');
        
        if (linkElement) {
          // Editing existing link
          this.isEditingExistingLink = true;
          this.linkData.url = linkElement.getAttribute('href') || '';
          this.linkData.text = linkElement.textContent || '';
          this.linkData.title = linkElement.getAttribute('title') || '';
          this.linkData.openInNewTab = linkElement.getAttribute('target') === '_blank';
          this.linkData.noFollow = (linkElement.getAttribute('rel') || '').includes('nofollow');
        } else {
          // Creating new link
          this.isEditingExistingLink = false;
          this.linkData.text = selectedText;
        }
      }
      
      this.showLinkDialog = true;
    },
    
    editExistingLink(linkElement: HTMLAnchorElement) {
      this.resetLinkData();
      this.isEditingExistingLink = true;
      
      this.linkData.url = linkElement.getAttribute('href') || '';
      this.linkData.text = linkElement.textContent || '';
      this.linkData.title = linkElement.getAttribute('title') || '';
      this.linkData.openInNewTab = linkElement.getAttribute('target') === '_blank';
      this.linkData.noFollow = (linkElement.getAttribute('rel') || '').includes('nofollow');
      
      // Select the link in the editor
      if (this.editorInstance) {
        this.editorInstance.selection.select(linkElement);
      }
      
      this.showLinkDialog = true;
    },
    
    resetLinkData() {
      this.linkData = {
        url: '',
        text: '',
        title: '',
        openInNewTab: false,
        noFollow: false
      };
    },
    
    insertLink() {
      if (!this.linkData.url || !this.editorInstance) {
        return;
      }
      
      // Ensure URL has protocol
      let url = this.linkData.url;
      if (!url.match(/^https?:\/\//)) {
        url = 'https://' + url;
      }
      
      // Build link attributes
      const attributes: any = {
        href: url
      };
      
      if (this.linkData.title) {
        attributes.title = this.linkData.title;
      }
      
      if (this.linkData.openInNewTab) {
        attributes.target = '_blank';
        // Add rel="noopener" for security when opening in new tab
        attributes.rel = 'noopener';
      }
      
      if (this.linkData.noFollow) {
        const existingRel = attributes.rel || '';
        attributes.rel = existingRel ? `${existingRel} nofollow` : 'nofollow';
      }
      
      if (this.isEditingExistingLink) {
        // Update existing link
        const selection = this.editorInstance.selection;
        const linkElement = this.editorInstance.dom.getParent(selection.getNode(), 'a');
        
        if (linkElement) {
          // Update attributes
          Object.keys(attributes).forEach(key => {
            linkElement.setAttribute(key, attributes[key]);
          });
          
          // Update text if changed
          if (this.linkData.text && linkElement.textContent !== this.linkData.text) {
            linkElement.textContent = this.linkData.text;
          }
        }
      } else {
        // Create new link
        const linkText = this.linkData.text || this.linkData.url;
        
        const selection = this.editorInstance.selection;
        const selectedContent = selection.getContent();
        
        if (selectedContent) {
          // Wrap selected content in link
          this.editorInstance.execCommand('mceInsertLink', false, attributes);
        } else {
          // Insert new link with text
          const linkHtml = this.buildLinkHtml(linkText, attributes);
          this.editorInstance.insertContent(linkHtml);
        }
      }
      
      this.closeLinkDialog();
    },
    
    removeLink() {
      if (this.editorInstance) {
        this.editorInstance.execCommand('unlink');
      }
      this.closeLinkDialog();
    },
    
    closeLinkDialog() {
      this.showLinkDialog = false;
      this.resetLinkData();
      this.isEditingExistingLink = false;
    },
    
    buildLinkHtml(text: string, attributes: any): string {
      const attrString = Object.keys(attributes)
        .map(key => `${key}="${attributes[key]}"`)
        .join(' ');
      
      return `<a ${attrString}>${text}</a>`;
    },
    
    buildFullUrl(selectedItem: MediaItemWithLinkType, linkType?: 'serve' | 'download'): string {
      const baseUrl = this.getBaseUrl();
      const type = linkType || selectedItem.linkType || 'serve';
      
      if (type === 'download') {
        // Use download URL
        if (selectedItem.download_url && selectedItem.download_url.startsWith('http')) {
          return selectedItem.download_url;
        }
        
        if (selectedItem.download_url) {
          const cleanUrl = selectedItem.download_url.startsWith('/') 
            ? selectedItem.download_url.substring(1) 
            : selectedItem.download_url;
          return `${baseUrl}/${cleanUrl}`;
        }
        
        // Fallback: construct download URL manually
        return `${baseUrl}/api/v1/conferences/${this.conferenceId}/media/${selectedItem.id}/download`;
      } else {
        // Use serve URL (default)
        if (selectedItem.url && selectedItem.url.startsWith('http')) {
          return selectedItem.url;
        }
        
        if (selectedItem.url) {
          const cleanUrl = selectedItem.url.startsWith('/') 
            ? selectedItem.url.substring(1) 
            : selectedItem.url;
          return `${baseUrl}/${cleanUrl}`;
        }
        
        // Fallback: construct serve URL manually
        return `${baseUrl}/api/v1/conferences/${this.conferenceId}/media/${selectedItem.id}/serve`;
      }
    },
    
    handleMediaSelect(selectedItem: MediaItemWithLinkType) {
      if (selectedItem) {
        const linkType = selectedItem.linkType || 'serve';
        const fullUrl = this.buildFullUrl(selectedItem, linkType);
        
        if (this.filePickerCallback) {
          this.filePickerCallback(fullUrl, { alt: selectedItem.file_name });
          this.filePickerCallback = null;
        } else if (this.editorInstance) {
          let content = '';
          
          if (linkType === 'download') {
            // Create download link regardless of file type
            const fileIcon = this.getFileIconForDisplay(selectedItem.mime_type);
            content = `<p><a href="${fullUrl}" target="_blank" download="${selectedItem.file_name}" title="Download ${selectedItem.file_name}">
              ${fileIcon} ${selectedItem.file_name}
            </a></p>`;
          } else {
            // Create serve link - different behavior for different file types
            if (this.isImage(selectedItem.mime_type)) {
              content = `<img src="${fullUrl}" alt="${selectedItem.file_name}" style="max-width: 100%; height: auto;" />`;
            } else if (this.isVideo(selectedItem.mime_type)) {
              // Embed video with controls
              content = `<video width="100%" height="auto" controls style="max-width: 100%;">
                <source src="${fullUrl}" type="${selectedItem.mime_type}">
                Your browser does not support the video tag.
              </video>`;
            } else {
              const fileIcon = this.getFileIconForDisplay(selectedItem.mime_type);
              content = `<p><a href="${fullUrl}" target="_blank" title="View ${selectedItem.file_name}">
                ${fileIcon} ${selectedItem.file_name}
              </a></p>`;
            }
          }
          
          this.editorInstance.insertContent(content);
        }
      }
      this.showMediaManager = false;
    },
    
    getFileIconForDisplay(mimeType: string): string {
      if (this.isImage(mimeType)) return '🖼️';
      if (mimeType === 'application/pdf') return '📄';
      if (this.isDocument(mimeType)) return '📄';
      if (this.isVideo(mimeType)) return '🎥';
      return '📁';
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