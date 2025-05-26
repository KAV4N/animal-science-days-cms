<template>
  <Dialog 
    v-model:visible="visible" 
    header="Media Manager" 
    :style="{ width: '95vw', maxWidth: '1200px', height: '90vh' }" 
    :modal="true"
    :maximizable="true"
    :closable="true"
    :breakpoints="{ '640px': '95vw' }"
    @update:visible="$emit('update:visible', $event)"
  >
    <div class="h-full flex flex-col">
      <!-- Header Actions -->
      <Card class="mb-4 flex-shrink-0">
        <template #content>
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 p-0">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1">
              <!-- Collection Filter -->
              <div class="flex items-center gap-2">
                <label class="text-sm font-medium whitespace-nowrap">Collection:</label>
                <Dropdown
                  v-model="selectedCollection"
                  :options="collectionOptions"
                  option-label="label"
                  option-value="value"
                  placeholder="All Collections"
                  class="w-40"
                  @change="fetchMedia"
                />
              </div>
              
              <!-- Search -->
              <div class="flex items-center gap-2 flex-1 min-w-0">
                <label class="text-sm font-medium whitespace-nowrap">Search:</label>
                <InputText
                  v-model="searchTerm"
                  placeholder="Search files..."
                  class="flex-1"
                  @keyup.enter="fetchMedia"
                />
                <Button
                  icon="pi pi-search"
                  @click="fetchMedia"
                  outlined
                  size="small"
                />
              </div>
            </div>
            
            <!-- Upload Button -->
            <Button
              label="Upload Files"
              icon="pi pi-upload"
              @click="showUploadDialog = true"
              :disabled="!isLocked"
              size="small"
            />
          </div>
        </template>
      </Card>

      <!-- Media Grid -->
      <div class="flex-1 overflow-y-auto">
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center h-64">
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
        <div v-else-if="media.length === 0" class="flex items-center justify-center h-64">
          <Card>
            <template #content>
              <div class="text-center p-0">
                <i class="pi pi-images text-6xl mb-4 text-gray-400"></i>
                <h3 class="text-xl font-medium mb-4">No Media Files</h3>
                <p class="mb-6">Upload your first file to get started</p>
                <Button
                  label="Upload Files"
                  icon="pi pi-upload"
                  @click="showUploadDialog = true"
                  :disabled="!isLocked"
                />
              </div>
            </template>
          </Card>
        </div>

        <!-- Media Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <Card 
            v-for="item in media" 
            :key="item.id"
            class="group hover:shadow-lg transition-all duration-200"
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
                  
                  <!-- Actions Overlay -->
                  <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                    <Button
                      icon="pi pi-eye"
                      @click="viewMedia(item)"
                      text
                      severity="secondary"
                      size="large"
                      class="text-white hover:bg-white hover:bg-opacity-20"
                    />
                    <Button
                      icon="pi pi-download"
                      @click="downloadMedia(item)"
                      text
                      severity="secondary"
                      size="large"
                      class="text-white hover:bg-white hover:bg-opacity-20"
                    />
                    <Button
                      icon="pi pi-pencil"
                      @click="editMedia(item)"
                      :disabled="!isLocked"
                      text
                      severity="secondary"
                      size="large"
                      class="text-white hover:bg-white hover:bg-opacity-20"
                    />
                    <Button
                      icon="pi pi-trash"
                      @click="confirmDeleteMedia(item)"
                      :disabled="!isLocked"
                      text
                      severity="danger"
                      size="large"
                      class="text-white hover:bg-white hover:bg-opacity-20"
                    />
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
                    />
                    <span>{{ item.size_human }}</span>
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
        <div v-if="hasMorePages" class="text-center mt-6">
          <Button
            label="Load More"
            icon="pi pi-chevron-down"
            @click="loadMore"
            :loading="loadingMore"
            outlined
          />
        </div>
      </div>
    </div>

    <!-- Upload Dialog -->
    <Dialog 
      v-model:visible="showUploadDialog" 
      header="Upload Files" 
      :style="{ width: '95vw', maxWidth: '600px' }" 
      :modal="true"
      :closable="true"
    >
      <Card>
        <template #content>
          <div class="space-y-6 p-0">
            <div class="text-center">
              <div class="rounded-full p-4 w-16 h-16 mx-auto mb-4 bg-blue-50 flex items-center justify-center">
                <i class="pi pi-upload text-2xl text-blue-600"></i>
              </div>
              <h3 class="text-2xl font-bold mb-4">Upload Files</h3>
              <p class="text-gray-600">Select files to upload to your conference</p>
            </div>

            <div class="space-y-4">
              <!-- Collection Selection -->
              <div>
                <label for="uploadCollection" class="block text-sm font-medium mb-2">Collection *</label>
                <Dropdown
                  id="uploadCollection"
                  v-model="uploadData.collection"
                  :options="uploadCollectionOptions"
                  option-label="label"
                  option-value="value"
                  placeholder="Select collection"
                  class="w-full"
                />
              </div>

              <!-- File Upload -->
              <div>
                <label class="block text-sm font-medium mb-2">Files *</label>
                <FileUpload
                  ref="fileUpload"
                  mode="advanced"
                  :multiple="true"
                  accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,video/*"
                  :maxFileSize="52428800"
                  :showUploadButton="false"
                  :showCancelButton="false"
                  class="w-full"
                  @select="onFileSelect"
                  @remove="onFileRemove"
                >
                  <template #empty>
                    <div class="text-center p-8">
                      <i class="pi pi-cloud-upload text-4xl text-gray-400 mb-4"></i>
                      <p class="text-lg text-gray-600 mb-2">Drag and drop files here</p>
                      <p class="text-sm text-gray-500">or click to browse</p>
                      <p class="text-xs text-gray-400 mt-2">Max file size: 50MB</p>
                    </div>
                  </template>
                </FileUpload>
              </div>

              <!-- Custom Name -->
              <div v-if="selectedFiles.length === 1">
                <label for="uploadName" class="block text-sm font-medium mb-2">Custom Name</label>
                <InputText
                  id="uploadName"
                  v-model="uploadData.name"
                  placeholder="Leave empty to use original filename"
                  class="w-full"
                />
              </div>

              <!-- Custom Properties -->
              <div>
                <label class="block text-sm font-medium mb-2">Custom Properties (Optional)</label>
                <div class="space-y-2">
                  <div v-for="(prop, index) in uploadData.customProperties" :key="index" class="flex gap-2">
                    <InputText
                      v-model="prop.key"
                      placeholder="Property name"
                      class="flex-1"
                    />
                    <InputText
                      v-model="prop.value"
                      placeholder="Property value"
                      class="flex-1"
                    />
                    <Button
                      icon="pi pi-trash"
                      @click="removeCustomProperty(index)"
                      text
                      severity="danger"
                      size="small"
                    />
                  </div>
                  <Button
                    label="Add Property"
                    icon="pi pi-plus"
                    @click="addCustomProperty"
                    text
                    size="small"
                  />
                </div>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4">
              <Button
                label="Cancel"
                icon="pi pi-times"
                @click="cancelUpload"
                outlined
                class="w-full sm:w-auto"
              />
              <Button
                label="Upload"
                icon="pi pi-check"
                @click="handleUpload"
                :disabled="selectedFiles.length === 0 || !uploadData.collection"
                :loading="uploading"
                class="w-full sm:w-auto"
              />
            </div>
          </div>
        </template>
      </Card>
    </Dialog>

    <!-- Edit Media Dialog -->
    <Dialog 
      v-model:visible="showEditDialog" 
      header="Edit Media" 
      :style="{ width: '95vw', maxWidth: '500px' }" 
      :modal="true"
      :closable="true"
    >
      <Card>
        <template #content>
          <div class="space-y-6 p-0">
            <div class="text-center">
              <div class="rounded-full p-4 w-16 h-16 mx-auto mb-4 bg-green-50 flex items-center justify-center">
                <i class="pi pi-pencil text-2xl text-green-600"></i>
              </div>
              <h3 class="text-2xl font-bold mb-4">Edit Media</h3>
              <p class="text-gray-600">Update media information</p>
            </div>

            <div class="space-y-4">
              <!-- Name -->
              <div>
                <label for="editName" class="block text-sm font-medium mb-2">Name</label>
                <InputText
                  id="editName"
                  v-model="editData.name"
                  class="w-full"
                />
              </div>

              <!-- Custom Properties -->
              <div>
                <label class="block text-sm font-medium mb-2">Custom Properties</label>
                <div class="space-y-2">
                  <div v-for="(prop, index) in editData.customProperties" :key="index" class="flex gap-2">
                    <InputText
                      v-model="prop.key"
                      placeholder="Property name"
                      class="flex-1"
                    />
                    <InputText
                      v-model="prop.value"
                      placeholder="Property value"
                      class="flex-1"
                    />
                    <Button
                      icon="pi pi-trash"
                      @click="removeEditCustomProperty(index)"
                      text
                      severity="danger"
                      size="small"
                    />
                  </div>
                  <Button
                    label="Add Property"
                    icon="pi pi-plus"
                    @click="addEditCustomProperty"
                    text
                    size="small"
                  />
                </div>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4">
              <Button
                label="Cancel"
                icon="pi pi-times"
                @click="cancelEdit"
                outlined
                class="w-full sm:w-auto"
              />
              <Button
                label="Save"
                icon="pi pi-check"
                @click="saveEdit"
                :loading="updating"
                class="w-full sm:w-auto"
              />
            </div>
          </div>
        </template>
      </Card>
    </Dialog>

    <!-- View Media Dialog -->
    <Dialog 
      v-model:visible="showViewDialog" 
      :header="viewingMedia?.name || 'View Media'" 
      :style="{ width: '95vw', maxWidth: '800px' }" 
      :modal="true"
      :maximizable="true"
      :closable="true"
    >
      <div v-if="viewingMedia" class="space-y-4">
        <!-- Media Display -->
        <div class="text-center">
          <img 
            v-if="isImage(viewingMedia.mime_type)"
            :src="viewingMedia.conversions.preview || viewingMedia.url"
            :alt="viewingMedia.name"
            class="max-w-full max-h-96 mx-auto rounded-lg shadow-md"
          />
          <div 
            v-else
            class="bg-gray-100 rounded-lg p-8 text-center"
          >
            <i 
              :class="getFileIcon(viewingMedia.mime_type)"
              class="text-6xl text-gray-400 mb-4"
            ></i>
            <p class="text-lg font-medium">{{ viewingMedia.name }}</p>
            <p class="text-sm text-gray-600">{{ viewingMedia.file_name }}</p>
          </div>
        </div>

        <!-- Media Details -->
        <Card>
          <template #title>
            <h4 class="text-lg font-semibold">File Details</h4>
          </template>
          <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-0">
              <div>
                <strong class="text-sm">Name:</strong>
                <p class="text-sm">{{ viewingMedia.name || 'N/A' }}</p>
              </div>
              <div>
                <strong class="text-sm">File Name:</strong>
                <p class="text-sm">{{ viewingMedia.file_name }}</p>
              </div>
              <div>
                <strong class="text-sm">Collection:</strong>
                <p class="text-sm">{{ viewingMedia.collection_name }}</p>
              </div>
              <div>
                <strong class="text-sm">Size:</strong>
                <p class="text-sm">{{ viewingMedia.size_human }}</p>
              </div>
              <div>
                <strong class="text-sm">Type:</strong>
                <p class="text-sm">{{ viewingMedia.mime_type }}</p>
              </div>
              <div>
                <strong class="text-sm">Uploaded:</strong>
                <p class="text-sm">{{ formatDate(viewingMedia.created_at) }}</p>
              </div>
            </div>
          </template>
        </Card>

        <!-- Actions -->
        <div class="flex justify-center gap-3">
          <Button
            label="Download"
            icon="pi pi-download"
            @click="downloadMedia(viewingMedia)"
            outlined
          />
          <Button
            label="Edit"
            icon="pi pi-pencil"
            @click="editMedia(viewingMedia); showViewDialog = false"
            :disabled="!isLocked"
            outlined
          />
        </div>
      </div>
    </Dialog>

    <!-- Confirm Delete Dialog -->
    <Dialog 
      v-model:visible="showDeleteDialog" 
      header="Confirm Delete" 
      :style="{ width: '95vw', maxWidth: '400px' }" 
      :modal="true"
      :closable="true"
    >
      <Card>
        <template #content>
          <div class="space-y-6 p-0">
            <div class="text-center">
              <div class="rounded-full p-4 w-16 h-16 mx-auto mb-4 bg-red-50 flex items-center justify-center">
                <i class="pi pi-exclamation-triangle text-2xl text-red-600"></i>
              </div>
              <h3 class="text-2xl font-bold mb-4">Delete Media</h3>
              <p class="text-gray-600">Are you sure you want to delete this file? This action cannot be undone.</p>
            </div>

            <Card v-if="deletingMedia">
              <template #content>
                <div class="text-center p-0">
                  <p class="font-medium">{{ deletingMedia.name || deletingMedia.file_name }}</p>
                  <p class="text-sm text-gray-600">{{ deletingMedia.size_human }} • {{ deletingMedia.collection_name }}</p>
                </div>
              </template>
            </Card>

            <div class="flex flex-col sm:flex-row justify-center gap-3">
              <Button
                label="Cancel"
                icon="pi pi-times"
                @click="cancelDelete"
                outlined
                class="w-full sm:w-auto"
              />
              <Button
                label="Delete"
                icon="pi pi-trash"
                @click="deleteMedia"
                :loading="deleting"
                severity="danger"
                class="w-full sm:w-auto"
              />
            </div>
          </div>
        </template>
      </Card>
    </Dialog>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import apiService from '@/services/apiService';
import type { 
  MediaItem, 
  MediaPaginatedData, 
  MediaUploadData, 
  MediaUpdateData,
  MediaFilterParams,
  CustomProperty,
  MediaCollectionOption,
  FileUploadEvent,
  FileRemoveEvent,
  ApiResponse,
  ApiPaginatedResponse
} from '@/types/media';

export default defineComponent({
  name: 'MediaManager',
  props: {
    visible: {
      type: Boolean,
      required: true
    },
    conferenceId: {
      type: Number,
      required: true
    },
    isLocked: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:visible'],
  data() {
    return {
      media: [] as MediaItem[],
      loading: false,
      loadingMore: false,
      currentPage: 1,
      hasMorePages: false,
      searchTerm: '',
      selectedCollection: '',
      
      collectionOptions: [
        { label: 'All Collections', value: '' },
        { label: 'Images', value: 'images' },
        { label: 'Documents', value: 'documents' },
        { label: 'General', value: 'general' }
      ] as MediaCollectionOption[],
      
      uploadCollectionOptions: [
        { label: 'Images', value: 'images' },
        { label: 'Documents', value: 'documents' },
        { label: 'General', value: 'general' }
      ] as MediaCollectionOption[],
      
      // Upload Dialog
      showUploadDialog: false,
      uploading: false,
      selectedFiles: [] as File[],
      uploadData: {
        collection: 'general' as string,
        name: '',
        customProperties: [] as CustomProperty[]
      } as MediaUploadData & { customProperties: CustomProperty[] },
      
      // Edit Dialog
      showEditDialog: false,
      updating: false,
      editingMedia: null as MediaItem | null,
      editData: {
        name: '',
        customProperties: [] as CustomProperty[]
      },
      
      // View Dialog
      showViewDialog: false,
      viewingMedia: null as MediaItem | null,
      
      // Delete Dialog
      showDeleteDialog: false,
      deleting: false,
      deletingMedia: null as MediaItem | null,
    };
  },
  watch: {
    visible(newVal) {
      if (newVal) {
        this.fetchMedia();
      }
    }
  },
  methods: {
    async fetchMedia(reset = true) {
      if (reset) {
        this.currentPage = 1;
        this.media = [];
      }
      
      this.loading = reset;
      this.loadingMore = !reset;
      
      try {
        const params: MediaFilterParams = {
          page: this.currentPage,
          per_page: 20
        };
        
        if (this.selectedCollection) {
          params.collection = this.selectedCollection;
        }
        
        if (this.searchTerm) {
          params.search = this.searchTerm;
        }
        
        const response = await apiService.get(`/v1/conferences/${this.conferenceId}/media`, { params });
        
        // Debug: Log the response structure
        console.log('API Response:', response.data);
        
        // Handle Laravel pagination structure from your ApiResponse trait
        let newMedia: MediaItem[] = [];
        let paginationInfo: any = {};
        
        if (response.data.payload && Array.isArray(response.data.payload)) {
          // Direct array response
          newMedia = response.data.payload;
        } else if (response.data.payload && response.data.payload.data) {
          // Paginated response with data property
          newMedia = response.data.payload.data;
        } else if (response.data.payload) {
          // Paginated response where payload is the paginated collection
          newMedia = response.data.payload;
        }
        
        // Get pagination meta from response
        if (response.data.meta) {
          paginationInfo = response.data.meta;
        }
        
        // Debug: Log what we extracted
        console.log('Extracted media:', newMedia);
        console.log('Pagination info:', paginationInfo);
        
        if (reset) {
          this.media = newMedia || [];
        } else {
          this.media.push(...(newMedia || []));
        }
        
        // Update pagination state
        if (paginationInfo.current_page && paginationInfo.last_page) {
          this.hasMorePages = paginationInfo.current_page < paginationInfo.last_page;
        } else {
          this.hasMorePages = false;
        }
        
      } catch (error) {
        console.error('Error fetching media:', error);
        this.$toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to fetch media files',
          life: 3000
        });
      } finally {
        this.loading = false;
        this.loadingMore = false;
      }
    },
    
    async loadMore() {
      this.currentPage++;
      await this.fetchMedia(false);
    },
    
    onFileSelect(event: FileUploadEvent) {
      this.selectedFiles = Array.from(event.files);
    },
    
    onFileRemove(event: FileRemoveEvent) {
      this.selectedFiles = this.selectedFiles.filter(file => file !== event.file);
    },
    
    addCustomProperty() {
      this.uploadData.customProperties.push({ key: '', value: '' });
    },
    
    removeCustomProperty(index: number) {
      this.uploadData.customProperties.splice(index, 1);
    },
    
    async handleUpload() {
      if (this.selectedFiles.length === 0 || !this.uploadData.collection) return;
      
      this.uploading = true;
      
      try {
        for (const file of this.selectedFiles) {
          const formData = new FormData();
          formData.append('file', file);
          formData.append('collection', this.uploadData.collection);
          
          if (this.uploadData.name && this.selectedFiles.length === 1) {
            formData.append('name', this.uploadData.name);
          }
          
          if (this.uploadData.customProperties.length > 0) {
            const customProps: Record<string, string> = {};
            this.uploadData.customProperties
              .filter(prop => prop.key && prop.value)
              .forEach(prop => {
                customProps[prop.key] = prop.value;
              });
            
            if (Object.keys(customProps).length > 0) {
              formData.append('custom_properties', JSON.stringify(customProps));
            }
          }
          
          await apiService.post(`/v1/conferences/${this.conferenceId}/media`, formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          });
        }
        
        this.$toast.add({
          severity: 'success',
          summary: 'Success',
          detail: `${this.selectedFiles.length} file(s) uploaded successfully`,
          life: 3000
        });
        
        this.cancelUpload();
        this.fetchMedia();
        
      } catch (error: any) {
        console.error('Error uploading files:', error);
        this.$toast.add({
          severity: 'error',
          summary: 'Upload Failed',
          detail: error.response?.data?.message || 'Failed to upload files',
          life: 5000
        });
      } finally {
        this.uploading = false;
      }
    },
    
    cancelUpload() {
      this.showUploadDialog = false;
      this.selectedFiles = [];
      this.uploadData = {
        collection: 'general',
        name: '',
        customProperties: []
      };
      if (this.$refs.fileUpload) {
        (this.$refs.fileUpload as any).clear();
      }
    },
    
    viewMedia(media: MediaItem) {
      this.viewingMedia = media;
      this.showViewDialog = true;
    },
    
    downloadMedia(media: MediaItem) {
      const link = document.createElement('a');
      link.href = `/api/v1/conferences/${this.conferenceId}/media/${media.id}/download`;
      link.download = media.file_name;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    
    editMedia(media: MediaItem) {
      this.editingMedia = media;
      this.editData = {
        name: media.name || '',
        customProperties: Object.entries(media.custom_properties || {}).map(([key, value]) => ({
          key,
          value: String(value)
        }))
      };
      this.showEditDialog = true;
    },
    
    addEditCustomProperty() {
      this.editData.customProperties.push({ key: '', value: '' });
    },
    
    removeEditCustomProperty(index: number) {
      this.editData.customProperties.splice(index, 1);
    },
    
    async saveEdit() {
      if (!this.editingMedia) return;
      
      this.updating = true;
      
      try {
        const updateData: any = {
          name: this.editData.name
        };
        
        if (this.editData.customProperties.length > 0) {
          const customProps: Record<string, string> = {};
          this.editData.customProperties
            .filter(prop => prop.key && prop.value)
            .forEach(prop => {
              customProps[prop.key] = prop.value;
            });
          updateData.custom_properties = customProps;
        }
        
        await apiService.put(`/v1/conferences/${this.conferenceId}/media/${this.editingMedia.id}`, updateData);
        
        this.$toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Media updated successfully',
          life: 3000
        });
        
        this.cancelEdit();
        this.fetchMedia();
        
      } catch (error: any) {
        console.error('Error updating media:', error);
        this.$toast.add({
          severity: 'error',
          summary: 'Update Failed',
          detail: error.response?.data?.message || 'Failed to update media',
          life: 5000
        });
      } finally {
        this.updating = false;
      }
    },
    
    cancelEdit() {
      this.showEditDialog = false;
      this.editingMedia = null;
      this.editData = {
        name: '',
        customProperties: []
      };
    },
    
    confirmDeleteMedia(media: MediaItem) {
      this.deletingMedia = media;
      this.showDeleteDialog = true;
    },
    
    async deleteMedia() {
      if (!this.deletingMedia) return;
      
      this.deleting = true;
      
      try {
        await apiService.delete(`/v1/conferences/${this.conferenceId}/media/${this.deletingMedia.id}`);
        
        this.$toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Media deleted successfully',
          life: 3000
        });
        
        this.cancelDelete();
        this.fetchMedia();
        
      } catch (error: any) {
        console.error('Error deleting media:', error);
        this.$toast.add({
          severity: 'error',
          summary: 'Delete Failed',
          detail: error.response?.data?.message || 'Failed to delete media',
          life: 5000
        });
      } finally {
        this.deleting = false;
      }
    },
    
    cancelDelete() {
      this.showDeleteDialog = false;
      this.deletingMedia = null;
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
    },
    
    formatDate(dateString: string): string {
      return new Date(dateString).toLocaleDateString();
    }
  }
});
</script>