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

  <!-- URL Media Dialog -->
  <Dialog
    v-model:visible="showUrlMediaDialog"
    header="Insert Media from URL"
    :style="{ width: '90vw', maxWidth: '800px' }"
    :modal="true"
    :closable="true"
  >
    <div class="flex flex-col gap-4">
      <div>
        <label for="mediaUrl" class="block text-sm font-medium mb-2">Media URL</label>
        <InputText
          id="mediaUrl"
          v-model="urlMediaData.url"
          placeholder="https://example.com/image.jpg or https://youtube.com/watch?v=..."
          class="w-full"
          autofocus
          @input="handleUrlChange"
        />
        <small class="text-gray-500">
          Supports images, videos, YouTube, Vimeo, and other embeddable content
        </small>
      </div>

      <div>
        <label for="mediaTagType" class="block text-sm font-medium mb-2">Insert As</label>
        <Dropdown
          id="mediaTagType"
          v-model="urlMediaData.tagType"
          :options="tagTypeOptions"
          optionLabel="label"
          optionValue="value"
          class="w-full"
          placeholder="Select how to insert the media"
        />
        <small class="text-gray-500">Choose how the media should be inserted</small>
      </div>

      <div>
        <label for="mediaAltText" class="block text-sm font-medium mb-2">Alt Text / Caption</label>
        <InputText
          id="mediaAltText"
          v-model="urlMediaData.altText"
          placeholder="Describe the media content"
          class="w-full"
        />
        <small class="text-gray-500">Important for accessibility and SEO</small>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="mediaWidth" class="block text-sm font-medium mb-2">Width</label>
          <InputText
            id="mediaWidth"
            v-model="urlMediaData.width"
            placeholder="auto, 100%, 500px"
            class="w-full"
          />
        </div>
        <div>
          <label for="mediaHeight" class="block text-sm font-medium mb-2">Height</label>
          <InputText
            id="mediaHeight"
            v-model="urlMediaData.height"
            placeholder="auto, 300px"
            class="w-full"
          />
        </div>
      </div>

      <div class="flex items-center gap-2" v-if="urlMediaData.detectedType === 'video' && urlMediaData.tagType === 'video'">
        <Checkbox v-model="urlMediaData.autoplay" inputId="mediaAutoplay" binary />
        <label for="mediaAutoplay" class="text-sm font-medium">Autoplay (if supported)</label>
      </div>

      <div class="flex items-center gap-2" v-if="urlMediaData.detectedType === 'video' && urlMediaData.tagType === 'video'">
        <Checkbox v-model="urlMediaData.controls" inputId="mediaControls" binary />
        <label for="mediaControls" class="text-sm font-medium">Show controls</label>
      </div>

      <div class="flex items-center gap-2" v-if="['youtube', 'vimeo'].includes(urlMediaData.detectedType) && urlMediaData.tagType === 'iframe'">
        <Checkbox v-model="urlMediaData.autoplay" inputId="mediaAutoplayEmbed" binary />
        <label for="mediaAutoplayEmbed" class="text-sm font-medium">Autoplay (if supported)</label>
      </div>

      <div class="flex items-center gap-2" v-if="urlMediaData.tagType === 'iframe'">
        <Checkbox v-model="urlMediaData.responsive" inputId="mediaResponsive" binary />
        <label for="mediaResponsive" class="text-sm font-medium">Make responsive (16:9 aspect ratio)</label>
      </div>
    </div>
    
    <template #footer>
      <div class="flex justify-end gap-2">
        <Button
          label="Cancel"
          icon="pi pi-times"
          @click="closeUrlMediaDialog"
          outlined
        />
        <Button
          label="Insert Media"
          icon="pi pi-check"
          @click="insertUrlMedia"
          :disabled="!urlMediaData.url || !urlMediaData.tagType"
        />
      </div>
    </template>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import Editor from '@hugerte/hugerte-vue';
import MediaManager from '@/components/dashboard/PageEditor/MediaManager.vue'; 
import Dropdown from 'primevue/dropdown';

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

interface UrlMediaData {
  url: string;
  altText: string;
  width: string;
  height: string;
  autoplay: boolean;
  controls: boolean;
  responsive: boolean;
  detectedType: string;
  tagType: string;
}

export default defineComponent({
  name: 'EditorComponent',
  components: {
    Editor,
    MediaManager,
    Dropdown
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
      showUrlMediaDialog: false,
      mediaSelectionMode: 'single' as 'single' | 'multiple',
      allowedMimeTypes: [] as string[],
      editorInstance: null as any,
      filePickerCallback: null as Function | null,
      isEditingExistingLink: false,
      linkData: {
        url: '',
        text: '',
        title: '',
        openInNewTab: false,
        noFollow: false
      } as LinkData,
      urlMediaData: {
        url: '',
        altText: '',
        width: '',
        height: '',
        autoplay: false,
        controls: true,
        responsive: true,
        detectedType: '',
        tagType: ''
      } as UrlMediaData,
      tagTypeOptions: [
        { label: 'Link', value: 'link' },
        { label: 'Image', value: 'image' },
        { label: 'Video', value: 'video' },
        { label: 'Audio', value: 'audio' },
        { label: 'Iframe/Embed', value: 'iframe' }
      ]
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
          'emoticons', 'help', 'media'
        ].join(' '),
        toolbar: [
          'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify',
          'bullist numlist outdent indent | urlmedia | removeformat | help'
        ].join(' | '),
        mobile: {
          menubar: false,
          toolbar: 'undo redo | bold italic | bullist numlist | urlmedia | insert'
        },
        link_assume_external_targets: true,
        link_context_toolbar: true,
        default_link_target: '_blank',
        target_list: [
          { title: 'Same window', value: '' },
          { title: 'New window', value: '_blank' }
        ],
        images_upload_url: false,
        images_upload_handler: (blobInfo: any, success: Function, failure: Function) => {
          failure('Direct image upload is not allowed. Please use the Media Library or insert images by URL.');
        },
        automatic_uploads: false,
        paste_data_images: false,
        paste_block_drop: true,
        setup: (editor: any) => {
          this.editorInstance = editor;

          editor.ui.registry.addButton('urlmedia', {
            icon: 'embed',
            tooltip: 'Insert Media from URL',
            onAction: () => {
              this.openUrlMediaDialog();
            }
          });

          editor.ui.registry.addMenuItem('media_library', {
            text: 'Media Library...',
            icon: 'browse',
            onAction: () => {
              this.showMediaManager = true;
              this.mediaSelectionMode = 'single';
              this.allowedMimeTypes = [];
            }
          });

          editor.ui.registry.addMenuItem('downloadable_files', {
            text: 'Downloadable Files...',
            icon: 'download',
            onAction: () => {
              this.showMediaManager = true;
              this.mediaSelectionMode = 'single';
              this.allowedMimeTypes = [];
            }
          });

          editor.ui.registry.addMenuItem('url_media', {
            text: 'Media from URL...',
            icon: 'embed',
            onAction: () => {
              this.openUrlMediaDialog();
            }
          });
          
          editor.ui.registry.addMenuButton('insert', {
            text: 'Insert',
            fetch: (callback: Function) => {
              const items = [
                'media_library',
                'url_media',
                '|',
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
              'media_library',
              'downloadable_files',
              'url_media'
            ]
          });
          
          editor.ui.registry.addButton('image', {
            icon: 'image',
            tooltip: 'Insert Image',
            onAction: () => {
              this.showMediaManager = true;
              this.mediaSelectionMode = 'single';
              this.allowedMimeTypes = ['image/*'];
            }
          });

          editor.on('dblclick', (e: any) => {
            const target = e.target;
            if (target.tagName === 'A') {
              e.preventDefault();
              this.editExistingLink(target);
            }
          });

          editor.on('dragover dragenter', (e: Event) => {
            e.preventDefault();
            e.stopPropagation();
          });

          editor.on('drop', (e: any) => {
            e.preventDefault();
            e.stopPropagation();
            
            if (e.dataTransfer?.files?.length > 0) {
              editor.notificationManager.open({
                text: 'Direct file uploads are not allowed. Please use the Media Library to insert images and files.',
                type: 'warning',
                timeout: 5000
              });
              return false;
            }
          });

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
            items: 'media_library url_media | anchor | insertdatetime nonbreakingspace pagebreak horizontalrule'
          },
          media: {
            title: 'Media',
            items: 'media_library downloadable_files url_media'
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
          iframe {
            max-width: 100%;
          }
          .responsive-media {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
            max-width: 100%;
            margin: 10px 0;
          }
          .responsive-media iframe,
          .responsive-media video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
          }
          a {
            color: #3b82f6;
            text-decoration: underline;
          }
          a:hover {
            color: #1d4ed8;
          }
        `,
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
      const protocol = window.location.protocol;
      const host = window.location.host;
      return `${protocol}//${host}`;
    },
    
    openLinkDialog() {
      this.resetLinkData();
      
      if (this.editorInstance) {
        const selection = this.editorInstance.selection;
        const selectedText = selection.getContent({ format: 'text' });
        
        const linkElement = this.editorInstance.dom.getParent(selection.getNode(), 'a');
        
        if (linkElement) {
          this.isEditingExistingLink = true;
          this.linkData.url = linkElement.getAttribute('href') || '';
          this.linkData.text = linkElement.textContent || '';
          this.linkData.title = linkElement.getAttribute('title') || '';
          this.linkData.openInNewTab = linkElement.getAttribute('target') === '_blank';
          this.linkData.noFollow = (linkElement.getAttribute('rel') || '').includes('nofollow');
        } else {
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
      
      let url = this.linkData.url;
      if (!url.match(/^https?:\/\//)) {
        url = 'https://' + url;
      }
      
      const attributes: any = {
        href: url
      };
      
      if (this.linkData.title) {
        attributes.title = this.linkData.title;
      }
      
      if (this.linkData.openInNewTab) {
        attributes.target = '_blank';
        attributes.rel = 'noopener';
      }
      
      if (this.linkData.noFollow) {
        const existingRel = attributes.rel || '';
        attributes.rel = existingRel ? `${existingRel} nofollow` : 'nofollow';
      }
      
      if (this.isEditingExistingLink) {
        const selection = this.editorInstance.selection;
        const linkElement = this.editorInstance.dom.getParent(selection.getNode(), 'a');
        
        if (linkElement) {
          Object.keys(attributes).forEach(key => {
            linkElement.setAttribute(key, attributes[key]);
          });
          
          if (this.linkData.text && linkElement.textContent !== this.linkData.text) {
            linkElement.textContent = this.linkData.text;
          }
        }
      } else {
        const linkText = this.linkData.text || this.linkData.url;
        
        const selection = this.editorInstance.selection;
        const selectedContent = selection.getContent();
        
        if (selectedContent) {
          this.editorInstance.execCommand('mceInsertLink', false, attributes);
        } else {
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

    openUrlMediaDialog() {
      this.resetUrlMediaData();
      this.showUrlMediaDialog = true;
    },

    closeUrlMediaDialog() {
      this.showUrlMediaDialog = false;
      this.resetUrlMediaData();
    },

    resetUrlMediaData() {
      this.urlMediaData = {
        url: '',
        altText: '',
        width: '',
        height: '',
        autoplay: false,
        controls: true,
        responsive: true,
        detectedType: '',
        tagType: ''
      };
    },

    handleUrlChange() {
      this.urlMediaData.detectedType = this.detectMediaType(this.urlMediaData.url);
      switch (this.urlMediaData.detectedType) {
        case 'image':
          this.urlMediaData.tagType = 'image';
          break;
        case 'video':
          this.urlMediaData.tagType = 'video';
          break;
        case 'audio':
          this.urlMediaData.tagType = 'audio';
          break;
        case 'youtube':
        case 'vimeo':
        case 'embed':
          this.urlMediaData.tagType = 'iframe';
          break;
        default:
          this.urlMediaData.tagType = 'link';
          break;
      }
    },

    detectMediaType(url: string): string {
      if (!url) return '';

      // YouTube detection - supports various formats
      const youtubeRegex = /(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
      if (youtubeRegex.test(url)) {
        return 'youtube';
      }

      // Vimeo detection
      const vimeoRegex = /(?:vimeo\.com\/)(?:.*#|.*\/|)([\d]+)/;
      if (vimeoRegex.test(url)) {
        return 'vimeo';
      }

      // Twitter/X embed detection
      if (url.includes('twitter.com/') || url.includes('x.com/')) {
        return 'embed';
      }

      // Instagram embed detection
      if (url.includes('instagram.com/')) {
        return 'embed';
      }

      // TikTok embed detection
      if (url.includes('tiktok.com/')) {
        return 'embed';
      }

      // Generic iframe/embed detection
      if (url.includes('embed') || url.includes('iframe')) {
        return 'embed';
      }

      // Image file extensions
      const imageExtensions = /\.(jpg|jpeg|png|gif|webp|svg|bmp|ico)(\?.*)?$/i;
      if (imageExtensions.test(url)) {
        return 'image';
      }

      // Video file extensions
      const videoExtensions = /\.(mp4|webm|ogg|avi|mov|wmv|flv|mkv|m4v)(\?.*)?$/i;
      if (videoExtensions.test(url)) {
        return 'video';
      }

      // Audio file extensions
      const audioExtensions = /\.(mp3|wav|ogg|aac|flac|m4a|wma)(\?.*)?$/i;
      if (audioExtensions.test(url)) {
        return 'audio';
      }

      return 'embed';
    },

    getYouTubeId(url: string): string {
      const regex = /(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
      const match = url.match(regex);
      return match ? match[1] : '';
    },

    getVimeoId(url: string): string {
      const regex = /(?:vimeo\.com\/)(?:.*#|.*\/|)([\d]+)/;
      const match = url.match(regex);
      return match ? match[1] : '';
    },

    buildStyleAttribute(includeResponsive: boolean = false): string {
      const styles: string[] = [];
      
      if (!includeResponsive) {
        if (this.urlMediaData.width) {
          const width = this.urlMediaData.width.includes('px') || this.urlMediaData.width.includes('%') || this.urlMediaData.width === 'auto' 
            ? this.urlMediaData.width 
            : this.urlMediaData.width + 'px';
          styles.push(`width: ${width}`);
        }
        
        if (this.urlMediaData.height) {
          const height = this.urlMediaData.height.includes('px') || this.urlMediaData.height.includes('%') || this.urlMediaData.height === 'auto' 
            ? this.urlMediaData.height 
            : this.urlMediaData.height + 'px';
          styles.push(`height: ${height}`);
        }
      }
      
      return styles.length > 0 ? ` style="${styles.join('; ')}"` : '';
    },

    insertUrlMedia() {
      if (!this.urlMediaData.url || !this.urlMediaData.tagType || !this.editorInstance) {
        return;
      }

      let content = '';
      const url = this.urlMediaData.url;
      const altText = this.urlMediaData.altText || 'Media content';
      
      switch (this.urlMediaData.tagType) {
        case 'image':
          const imageStyle = this.buildStyleAttribute();
          content = `<p><img src="${url}" alt="${altText}" style="max-width: 100%; height: auto;${imageStyle ? ' ' + imageStyle.replace(' style="', '').replace('"', '') : ''}" /></p>`;
          break;

        case 'video':
          const videoAttrs = [];
          if (this.urlMediaData.controls) videoAttrs.push('controls');
          if (this.urlMediaData.autoplay) videoAttrs.push('autoplay');
          
          const videoStyle = this.buildStyleAttribute();
          content = `<p><video ${videoAttrs.join(' ')} style="max-width: 100%; height: auto;${videoStyle ? ' ' + videoStyle.replace(' style="', '').replace('"', '') : ''}"${videoStyle}>
            <source src="${url}" type="video/mp4">
            Your browser does not support the video tag.
          </video></p>`;
          break;

        case 'audio':
          const audioStyle = this.buildStyleAttribute();
          content = `<p><audio controls${audioStyle}>
            <source src="${url}" type="audio/mpeg">
            Your browser does not support the audio element.
          </audio></p>`;
          break;

        case 'iframe':
          content = this.generateIframeContent(url, altText);
          break;

        case 'link':
          content = `<p><a href="${url}" target="_blank" rel="noopener">${altText || url}</a></p>`;
          break;

        default:
          content = `<p><a href="${url}" target="_blank" rel="noopener">${altText || url}</a></p>`;
          break;
      }

      if (content) {
        this.editorInstance.insertContent(content);
        this.closeUrlMediaDialog();
      }
    },

    generateIframeContent(url: string, altText: string): string {
      const detectedType = this.urlMediaData.detectedType;
      
      if (detectedType === 'youtube') {
        return this.generateYouTubeEmbed(url, altText);
      } else if (detectedType === 'vimeo') {
        return this.generateVimeoEmbed(url, altText);
      } else if (detectedType === 'embed' && (url.includes('twitter.com') || url.includes('x.com'))) {
        return this.generateTwitterEmbed(url, altText);
      } else if (detectedType === 'embed' && url.includes('instagram.com')) {
        return this.generateInstagramEmbed(url, altText);
      } else if (detectedType === 'embed' && url.includes('tiktok.com')) {
        return this.generateTikTokEmbed(url, altText);
      } else {
        return this.generateGenericIframe(url, altText);
      }
    },

    generateYouTubeEmbed(url: string, altText: string): string {
      const youtubeId = this.getYouTubeId(url);
      if (!youtubeId) {
        return `<p><a href="${url}" target="_blank" rel="noopener">${altText}</a></p>`;
      }

      const autoplayParam = this.urlMediaData.autoplay ? '&autoplay=1' : '';
      const embedUrl = `https://www.youtube.com/embed/${youtubeId}?rel=0${autoplayParam}`;
      
      const style = this.buildStyleAttribute();
      const width = this.urlMediaData.width || '560';
      const height = this.urlMediaData.height || '315';
      return `<p><iframe width="${width}" height="${height}" src="${embedUrl}" title="${altText}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen${style}></iframe></p>`;
    },

    generateVimeoEmbed(url: string, altText: string): string {
      const vimeoId = this.getVimeoId(url);
      if (!vimeoId) {
        return `<p><a href="${url}" target="_blank" rel="noopener">${altText}</a></p>`;
      }

      const autoplayParam = this.urlMediaData.autoplay ? '&autoplay=1' : '';
      const embedUrl = `https://player.vimeo.com/video/${vimeoId}?${autoplayParam}`;
      
      const style = this.buildStyleAttribute();
      const width = this.urlMediaData.width || '640';
      const height = this.urlMediaData.height || '360';
      return `<p><iframe width="${width}" height="${height}" src="${embedUrl}" title="${altText}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen${style}></iframe></p>`;
    },

    generateTwitterEmbed(url: string, altText: string): string {
      // For Twitter/X embeds, we'll create a simple iframe or link
      // Note: Twitter embeds typically require their script, so we'll fallback to a link
      return `<p><a href="${url}" target="_blank" rel="noopener">${altText} (Twitter/X Post)</a></p>`;
    },

    generateInstagramEmbed(url: string, altText: string): string {
      // For Instagram embeds, we'll create a link since Instagram embeds require their script
      return `<p><a href="${url}" target="_blank" rel="noopener">${altText} (Instagram Post)</a></p>`;
    },

    generateTikTokEmbed(url: string, altText: string): string {
      // For TikTok embeds, we'll create a link since TikTok embeds require their script
      return `<p><a href="${url}" target="_blank" rel="noopener">${altText} (TikTok Video)</a></p>`;
    },

    generateGenericIframe(url: string, altText: string): string {
      const style = this.buildStyleAttribute();
      const width = this.urlMediaData.width || '100%';
      const height = this.urlMediaData.height || '400';
      
      return `<p><iframe src="${url}" width="${width}" height="${height}" title="${altText}" frameborder="0" allowfullscreen${style}></iframe></p>`;
    },
    
    buildFullUrl(selectedItem: MediaItemWithLinkType, linkType?: 'serve' | 'download'): string {
      const baseUrl = this.getBaseUrl();
      const type = linkType || selectedItem.linkType || 'serve';
      
      if (type === 'download') {
        if (selectedItem.download_url && selectedItem.download_url.startsWith('http')) {
          return selectedItem.download_url;
        }
        
        if (selectedItem.download_url) {
          const cleanUrl = selectedItem.download_url.startsWith('/') 
            ? selectedItem.download_url.substring(1) 
            : selectedItem.download_url;
          return `${baseUrl}/${cleanUrl}`;
        }
        
        return `${baseUrl}/api/v1/conferences/${this.conferenceId}/media/${selectedItem.id}/download`;
      } else {
        if (selectedItem.url && selectedItem.url.startsWith('http')) {
          return selectedItem.url;
        }
        
        if (selectedItem.url) {
          const cleanUrl = selectedItem.url.startsWith('/') 
            ? selectedItem.url.substring(1) 
            : selectedItem.url;
          return `${baseUrl}/${cleanUrl}`;
        }
        
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
            const fileIcon = this.getFileIconForDisplay(selectedItem.mime_type);
            content = `<p><a href="${fullUrl}" target="_blank" download="${selectedItem.file_name}" title="Download ${selectedItem.file_name}">
              ${fileIcon} ${selectedItem.file_name}
            </a></p>`;
          } else {
            if (this.isImage(selectedItem.mime_type)) {
              content = `<p><img src="${fullUrl}" alt="${selectedItem.file_name}" style="max-width: 100%; height: auto;" /></p>`;
            } else if (this.isVideo(selectedItem.mime_type)) {
              content = `<p><video width="100%" height="auto" controls style="max-width: 100%;">
                <source src="${fullUrl}" type="${selectedItem.mime_type}">
                Your browser does not support the video tag.
              </video></p>`;
            } else if (this.isAudio(selectedItem.mime_type)) {
              content = `<p><audio controls style="width: 100%; max-width: 500px;">
                <source src="${fullUrl}" type="${selectedItem.mime_type}">
                Your browser does not support the audio element.
              </audio></p>`;
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
      if (this.isAudio(mimeType)) return '🎵';
      if (this.isArchive(mimeType)) return '📦';
      return '📁';
    },
    
    isImage(mimeType: string): boolean {
      return mimeType.startsWith('image/');
    },
    
    isVideo(mimeType: string): boolean {
      return mimeType.startsWith('video/');
    },

    isAudio(mimeType: string): boolean {
      return mimeType.startsWith('audio/');
    },
    
    isDocument(mimeType: string): boolean {
      return [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
        'application/rtf'
      ].includes(mimeType);
    },

    isArchive(mimeType: string): boolean {
      return [
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'application/x-tar',
        'application/gzip'
      ].includes(mimeType);
    }
  }
});
</script>