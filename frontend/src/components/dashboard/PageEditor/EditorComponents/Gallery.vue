<!-- Gallery.vue - Editor Component -->
<template>
  <Dialog 
    v-model:visible="localVisible" 
    header="Edit Gallery" 
    :style="{ width: '95vw', maxWidth: '1200px' }" 
    :modal="true"
    :maximizable="true"
    :closable="true"
    :breakpoints="{ '640px': '95vw' }"
  >
    <Card>
      <template #content>
        <div class="space-y-6 p-0">
          <!-- Header -->
          <div class="text-center mb-6">
            <div class="rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center bg-purple-100">
              <i class="pi pi-images text-2xl text-purple-600"></i>
            </div>
            <h2 class="text-3xl font-bold mb-4">Edit Gallery</h2>
            <p class="text-gray-600">Manage your image gallery collection</p>
          </div>
          
          <!-- Component Name -->
          <div>
            <label for="galleryComponentName" class="block text-sm font-medium mb-2">Component Name</label>
            <InputText 
              id="galleryComponentName" 
              v-model="localComponentName" 
              class="w-full" 
              placeholder="Enter component name..."
            />
          </div>

          <!-- Gallery Settings -->
          <Card>
            <template #title>
              <h4 class="text-lg font-semibold">Gallery Settings</h4>
            </template>
            <template #content>
              <div class="space-y-4 p-0">
                <!-- Gallery Title -->
                <div>
                  <label for="galleryTitle" class="block text-sm font-medium mb-2">Gallery Title</label>
                  <InputText 
                    id="galleryTitle" 
                    v-model="localGalleryData.title" 
                    class="w-full" 
                    placeholder="e.g., Conference Photos, Event Gallery"
                  />
                </div>

                <!-- Gallery Description -->
                <div>
                  <label for="galleryDescription" class="block text-sm font-medium mb-2">Description</label>
                  <Textarea
                    id="galleryDescription"
                    v-model="localGalleryData.description"
                    :rows="3"
                    class="w-full"
                    placeholder="Brief description of the gallery..."
                  />
                </div>

                <!-- Display Settings -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label for="galleryColumns" class="block text-sm font-medium mb-2">Columns (Desktop)</label>
                    <Select
                      id="galleryColumns"
                      v-model="localGalleryData.columns"
                      :options="columnOptions"
                      option-label="label"
                      option-value="value"
                      class="w-full"
                    />
                  </div>
                  
                  <div>
                    <label for="gallerySpacing" class="block text-sm font-medium mb-2">Image Spacing</label>
                    <Select
                      id="gallerySpacing"
                      v-model="localGalleryData.spacing"
                      :options="spacingOptions"
                      option-label="label"
                      option-value="value"
                      class="w-full"
                    />
                  </div>

                  <div>
                    <label for="galleryAspectRatio" class="block text-sm font-medium mb-2">Aspect Ratio</label>
                    <Select
                      id="galleryAspectRatio"
                      v-model="localGalleryData.aspectRatio"
                      :options="aspectRatioOptions"
                      option-label="label"
                      option-value="value"
                      class="w-full"
                    />
                  </div>
                </div>

                <!-- Gallery Options -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="flex items-center">
                    <Checkbox 
                      id="showCaptions" 
                      v-model="localGalleryData.showCaptions" 
                      binary 
                    />
                    <label for="showCaptions" class="ml-3 text-sm font-medium">
                      Show Image Captions
                    </label>
                  </div>
                  
                  <div class="flex items-center">
                    <Checkbox 
                      id="enableLightbox" 
                      v-model="localGalleryData.enableLightbox" 
                      binary 
                    />
                    <label for="enableLightbox" class="ml-3 text-sm font-medium">
                      Enable Lightbox View
                    </label>
                  </div>
                </div>
              </div>
            </template>
          </Card>

          <!-- Image Management -->
          <Card>
            <template #title>
              <div class="flex justify-between items-center">
                <h4 class="text-lg font-semibold">Gallery Images ({{ localGalleryData.images.length }})</h4>
                <Button
                  label="Add Images"
                  icon="pi pi-plus"
                  @click="openMediaBrowser"
                  size="small"
                />
              </div>
            </template>
            <template #content>
              <div class="p-0">
                <!-- Empty State -->
                <div v-if="localGalleryData.images.length === 0" class="text-center py-8">
                  <i class="pi pi-images text-6xl text-gray-400 mb-4"></i>
                  <h3 class="text-xl font-medium mb-4">No Images Added</h3>
                  <p class="text-gray-600 mb-6">Start building your gallery by adding some images</p>
                  <Button
                    label="Browse Media"
                    icon="pi pi-images"
                    @click="openMediaBrowser"
                    outlined
                  />
                </div>

                <!-- Images Grid -->
                <div v-else class="space-y-4">
                  <!-- Images List -->
                  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Card 
                      v-for="(image, index) in localGalleryData.images" 
                      :key="index"
                      class="group"
                    >
                      <template #content>
                        <div class="p-0">
                          <!-- Image Preview -->
                          <div class="aspect-square bg-gray-100 rounded-lg mb-3 overflow-hidden relative">
                            <img 
                              :src="image.thumbnail || image.url"
                              :alt="image.caption || `Gallery image ${index + 1}`"
                              class="w-full h-full object-cover"
                              @error="handleImageError($event, index)"
                              v-if="!image.isMissing"
                            />
                            <div 
                              v-if="image.isMissing" 
                              class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-600"
                            >
                              <i class="pi pi-exclamation-triangle mr-2"></i>
                              <span>Image Not Found</span>
                            </div>
                            
                            <!-- Image Actions -->
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                              <Button
                                icon="pi pi-pencil"
                                @click="editImage(index)"
                                class="rounded-full"
                                size="small"
                                severity="info"
                                :pt="{ root: { class: 'w-8 h-8' } }"
                              />
                              <Button
                                icon="pi pi-trash"
                                @click="removeImage(index)"
                                class="rounded-full"
                                size="small"
                                severity="danger"
                                :pt="{ root: { class: 'w-8 h-8' } }"
                              />
                            </div>

                            <!-- Order Controls -->
                            <div class="absolute bottom-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                              <Button
                                v-if="index > 0"
                                icon="pi pi-chevron-left"
                                @click="moveImage(index, index - 1)"
                                class="rounded-full"
                                size="small"
                                outlined
                                :pt="{ root: { class: 'w-6 h-6 text-xs' } }"
                              />
                              <Button
                                v-if="index < localGalleryData.images.length - 1"
                                icon="pi pi-chevron-right"
                                @click="moveImage(index, index + 1)"
                                class="rounded-full"
                                size="small"
                                outlined
                                :pt="{ root: { class: 'w-6 h-6 text-xs' } }"
                              />
                            </div>
                          </div>

                          <!-- Image Info -->
                          <div class="space-y-2">
                            <div class="flex items-center justify-between">
                              <Badge 
                                :value="`#${index + 1}`" 
                                class="text-xs"
                              />
                              <span class="text-xs text-gray-500">{{ getImageSize(image) }}</span>
                            </div>
                            <p class="text-sm font-medium truncate" :title="image.caption || image.filename">
                              {{ image.caption || image.filename || `Image ${index + 1}` }}
                            </p>
                          </div>
                        </div>
                      </template>
                    </Card>
                  </div>
                </div>
              </div>
            </template>
          </Card>
          
          <!-- Published Checkbox -->
          <Card>
            <template #content>
              <div class="flex items-center p-0">
                <Checkbox v-model="localPublished" inputId="galleryPublished" binary />
                <label for="galleryPublished" class="ml-4 text-sm font-medium">
                  Publish immediately
                </label>
              </div>
              <p class="text-sm mt-3 ml-8 text-gray-600">Published galleries are visible to visitors</p>
            </template>
          </Card>
          
          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4">
            <Button 
              label="Cancel" 
              icon="pi pi-times" 
              @click="handleCancel" 
              outlined
              class="w-full sm:w-auto"
            />
            <Button 
              label="Save Gallery" 
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
      header="Select Gallery Images" 
      :style="{ width: '95vw', maxWidth: '1200px', height: '80vh' }" 
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
                    placeholder="Search images..."
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
              
              <!-- Selection Info -->
              <div class="text-sm text-gray-600">
                {{ selectedImages.length }} selected
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
                  <h3 class="text-xl font-medium mb-4">No Images Found</h3>
                  <p class="mb-6">No images found matching your criteria</p>
                </div>
              </template>
            </Card>
          </div>

          <!-- Media Grid -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
            <Card 
              v-for="item in imageMedia" 
              :key="item.id"
              class="group hover:shadow-lg transition-all duration-200 cursor-pointer relative"
              :class="{ 'ring-2 ring-blue-500': isImageSelected(item) }"
              @click="toggleImageSelection(item)"
            >
              <template #content>
                <div class="p-0">
                  <!-- Image Preview -->
                  <div class="aspect-square bg-gray-100 rounded-lg mb-4 overflow-hidden relative">
                    <img 
                      v-if="item.conversions && item.conversions.thumb"
                      :src="item.conversions.thumb"
                      :alt="item.file_name"
                      class="w-full h-full object-cover"
                    />
                    <div 
                      v-else
                      class="w-full h-full flex items-center justify-center"
                    >
                      <i class="pi pi-image text-6xl text-gray-400"></i>
                    </div>
                    
                    <!-- Selection Indicator -->
                    <div class="absolute top-2 right-2">
                      <div 
                        v-if="isImageSelected(item)"
                        class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center"
                      >
                        <i class="pi pi-check text-white text-xs"></i>
                      </div>
                      <div 
                        v-else
                        class="w-6 h-6 bg-white bg-opacity-80 rounded-full border-2 border-gray-300 opacity-0 group-hover:opacity-100 transition-opacity"
                      >
                      </div>
                    </div>
                  </div>

                  <!-- Image Info -->
                  <div class="space-y-2">
                    <h4 class="font-semibold text-sm truncate" :title="item.file_name">
                      {{ item.file_name }}
                    </h4>
                    <div class="flex items-center justify-between text-xs text-gray-600">
                      <Badge 
                        :value="item.collection_name || 'General'" 
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
          <div v-if="hasMoreMediaPages" class="text-center mb-4">
            <Button
              label="Load More"
              icon="pi pi-chevron-down"
              @click="loadMoreMedia"
              :loading="mediaLoadingMore"
              outlined
            />
          </div>
        </div>

        <!-- Media Browser Actions -->
        <div class="flex-shrink-0 pt-4 border-t">
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">
              {{ selectedImages.length }} image(s) selected
            </span>
            <div class="flex gap-2">
              <Button
                label="Cancel"
                @click="showMediaBrowser = false"
                outlined
              />
              <Button
                label="Add Selected"
                @click="addSelectedImages"
                :disabled="selectedImages.length === 0"
              />
            </div>
          </div>
        </div>
      </div>
    </Dialog>

    <!-- Image Edit Dialog -->
    <Dialog 
      v-model:visible="showImageEditor" 
      header="Edit Image Details" 
      :style="{ width: '95vw', maxWidth: '600px' }" 
      :modal="true"
      :closable="true"
    >
      <div v-if="editingImage" class="space-y-4">
        <!-- Image Preview -->
        <div class="text-center mb-4">
          <div class="w-32 h-32 mx-auto rounded-lg overflow-hidden bg-gray-100">
            <img 
              :src="editingImage.thumbnail || editingImage.url"
              :alt="editingImage.caption"
              class="w-full h-full object-cover"
            />
          </div>
        </div>

        <!-- Image Caption -->
        <div>
          <label for="imageCaption" class="block text-sm font-medium mb-2">Caption</label>
          <InputText 
            id="imageCaption" 
            v-model="editingImage.caption" 
            class="w-full" 
            placeholder="Enter image caption..."
          />
        </div>

        <!-- Image Alt Text -->
        <div>
          <label for="imageAlt" class="block text-sm font-medium mb-2">Alt Text</label>
          <InputText 
            id="imageAlt" 
            v-model="editingImage.alt" 
            class="w-full" 
            placeholder="Describe the image for accessibility..."
          />
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-2 pt-4">
          <Button
            label="Cancel"
            @click="cancelImageEdit"
            outlined
          />
          <Button
            label="Save"
            @click="saveImageEdit"
          />
        </div>
      </div>
    </Dialog>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import apiService from '@/services/apiService';
import type { MediaItem } from '@/types/media';

interface GalleryImage {
  id?: string | number;
  url: string;
  thumbnail?: string;
  caption?: string;
  alt?: string;
  filename?: string;
  size?: string;
  isMissing?: boolean; // Added for handling missing images
}

interface GalleryData {
  title: string;
  description: string;
  columns: number;
  spacing: string;
  aspectRatio: string;
  showCaptions: boolean;
  enableLightbox: boolean;
  images: GalleryImage[];
}

export default defineComponent({
  name: 'GalleryEditor',
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
      type: Object as PropType<GalleryData>,
      default: () => ({
        title: '',
        description: '',
        columns: 3,
        spacing: 'normal',
        aspectRatio: 'square',
        showCaptions: true,
        enableLightbox: true,
        images: []
      })
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
      localGalleryData: {
        title: '',
        description: '',
        columns: 3,
        spacing: 'normal',
        aspectRatio: 'square',
        showCaptions: true,
        enableLightbox: true,
        images: []
      } as GalleryData,
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
      selectedImages: [] as MediaItem[],
      
      // Image editor state
      showImageEditor: false,
      editingImage: null as GalleryImage | null,
      editingImageIndex: -1,
      
      // Options
      columnOptions: [
        { label: '1 Column', value: 1 },
        { label: '2 Columns', value: 2 },
        { label: '3 Columns', value: 3 },
        { label: '4 Columns', value: 4 },
        { label: '5 Columns', value: 5 }
      ],
      
      spacingOptions: [
        { label: 'Tight', value: 'tight' },
        { label: 'Normal', value: 'normal' },
        { label: 'Loose', value: 'loose' }
      ],
      
      aspectRatioOptions: [
        { label: 'Square (1:1)', value: 'square' },
        { label: 'Landscape (4:3)', value: 'landscape' },
        { label: 'Portrait (3:4)', value: 'portrait' },
        { label: 'Wide (16:9)', value: 'wide' },
        { label: 'Auto', value: 'auto' }
      ],
      
      mediaCollectionOptions: [
        { label: 'All Collections', value: '' },
        { label: 'Images', value: 'images' },
        { label: 'General', value: 'general' }
      ]
    };
  },
  computed: {
    // Filter to show only images in the media browser
    imageMedia() {
      return this.browserMedia.filter(item => this.isImage(item.mime_type));
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
      handler() {
        this.initializeData();
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
      
      const galleryData = this.componentData || {};
      
      this.localGalleryData = { 
        title: galleryData.title || '',
        description: galleryData.description || '',
        columns: galleryData.columns || 3,
        spacing: galleryData.spacing || 'normal',
        aspectRatio: galleryData.aspectRatio || 'square',
        showCaptions: galleryData.showCaptions !== false,
        enableLightbox: galleryData.enableLightbox !== false,
        images: galleryData.images || []
      };
      
      this.localPublished = this.isPublished;
    },
    
    handleSave() {
      if (!this.localGalleryData.title.trim()) {
        this.$toast?.add({
          severity: 'warn',
          summary: 'Validation Error',
          detail: 'Gallery title is required',
          life: 3000
        });
        return;
      }
      
      this.$emit('save', {
        name: this.localComponentName,
        data: this.localGalleryData,
        isPublished: this.localPublished
      });
    },
    
    handleCancel() {
      this.$emit('cancel');
      this.$emit('update:visible', false);
    },

    // Media browser methods
    openMediaBrowser() {
      this.showMediaBrowser = true;
      this.selectedImages = [];
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
    
    toggleImageSelection(media: MediaItem) {
      const index = this.selectedImages.findIndex(img => img.id === media.id);
      if (index > -1) {
        this.selectedImages.splice(index, 1);
      } else {
        this.selectedImages.push(media);
      }
    },
    
    isImageSelected(media: MediaItem): boolean {
      return this.selectedImages.some(img => img.id === media.id);
    },
    
    addSelectedImages() {
      const newImages: GalleryImage[] = this.selectedImages.map(media => ({
        id: media.id,
        url: media.download_url || `/v1/conferences/${this.conferenceId}/media/${media.id}/download`,
        thumbnail: media.conversions?.thumb || media.download_url,
        caption: media.alt_text || media.file_name,
        alt: media.alt_text || media.file_name,
        filename: media.file_name,
        size: media.size_human,
        isMissing: false // Initialize as not missing
      }));
      
      this.localGalleryData.images.push(...newImages);
      this.selectedImages = [];
      this.showMediaBrowser = false;
      
      this.$toast?.add({
        severity: 'success',
        summary: 'Success',
        detail: `${newImages.length} image(s) added to gallery`,
        life: 3000
      });
    },
    
    // Image management methods
    editImage(index: number) {
      this.editingImageIndex = index;
      this.editingImage = { ...this.localGalleryData.images[index] };
      this.showImageEditor = true;
    },
    
    saveImageEdit() {
      if (this.editingImage && this.editingImageIndex >= 0) {
        this.localGalleryData.images[this.editingImageIndex] = { ...this.editingImage };
        this.showImageEditor = false;
        this.editingImage = null;
        this.editingImageIndex = -1;
        
        this.$toast?.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Image updated successfully',
          life: 3000
        });
      }
    },
    
    cancelImageEdit() {
      this.showImageEditor = false;
      this.editingImage = null;
      this.editingImageIndex = -1;
    },
    
    removeImage(index: number) {
      this.$confirm?.require({
        message: 'Are you sure you want to remove this image from the gallery?',
        header: 'Confirm Removal',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
          this.localGalleryData.images.splice(index, 1);
          this.$toast?.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Image removed from gallery',
            life: 3000
          });
        }
      });
    },
    
    moveImage(fromIndex: number, toIndex: number) {
      const images = [...this.localGalleryData.images];
      const [movedImage] = images.splice(fromIndex, 1);
      images.splice(toIndex, 0, movedImage);
      this.localGalleryData.images = images;
    },
    
    handleImageError(event: Event, index: number) {
      const target = event.target as HTMLImageElement;
      if (target) {
        target.style.display = 'none';
      }
      this.localGalleryData.images[index].isMissing = true;
      this.$toast?.add({
        severity: 'warn',
        summary: 'Image Not Found',
        detail: `Image at position ${index + 1} could not be loaded.`,
        life: 3000
      });
    },
    
    getImageSize(image: GalleryImage): string {
      return image.size || 'Unknown size';
    },
    
    isImage(mimeType: string): boolean {
      return mimeType.startsWith('image/');
    }
  }
});
</script>