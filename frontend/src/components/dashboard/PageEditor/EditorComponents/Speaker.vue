<!-- Speaker.vue - Editor Component with Media Gallery -->
<template>
  <Dialog 
    v-model:visible="localVisible" 
    header="Edit Speaker Information" 
    :style="{ width: '95vw', maxWidth: '800px' }" 
    :modal="true"
    :maximizable="false"
    :closable="true"
    :breakpoints="{ '640px': '95vw' }"
  >
    <Card>
      <template #content>
        <div class="space-y-6 p-0">
          <div class="text-center mb-6">
            <div class="rounded-full p-4 w-16 h-16 mx-auto mb-4 flex items-center justify-center bg-blue-100">
              <i class="pi pi-user text-2xl text-blue-600"></i>
            </div>
            <h2 class="text-3xl font-bold mb-4">Edit Speaker Information</h2>
            <p class="text-gray-600">Configure speaker details for your conference</p>
          </div>
          
          <div class="space-y-6">
            <!-- Component Name -->
            <div>
              <label for="speakerComponentName" class="block text-sm font-medium mb-2">Component Name</label>
              <InputText 
                id="speakerComponentName" 
                v-model="localComponentName" 
                class="w-full" 
                placeholder="Enter component name..."
              />
            </div>

            <!-- Basic Information -->
            <Card>
              <template #title>
                <h4 class="text-lg font-semibold">Basic Information</h4>
              </template>
              <template #content>
                <div class="space-y-4 p-0">
                  <!-- Speaker Name -->
                  <div>
                    <label for="speakerName" class="block text-sm font-medium mb-2">Full Name *</label>
                    <InputText 
                      id="speakerName" 
                      v-model="localSpeakerData.name" 
                      class="w-full" 
                      placeholder="e.g., John Smith"
                      required
                    />
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Job Title -->
                    <div>
                      <label for="speakerTitle" class="block text-sm font-medium mb-2">Job Title</label>
                      <InputText 
                        id="speakerTitle" 
                        v-model="localSpeakerData.title" 
                        class="w-full" 
                        placeholder="e.g., Senior Developer, CEO"
                      />
                    </div>

                    <!-- Company -->
                    <div>
                      <label for="speakerCompany" class="block text-sm font-medium mb-2">Company</label>
                      <InputText 
                        id="speakerCompany" 
                        v-model="localSpeakerData.company" 
                        class="w-full" 
                        placeholder="e.g., Tech Corp, Startup Inc."
                      />
                    </div>
                  </div>

                  <!-- Avatar Section -->
                  <div>
                    <label class="block text-sm font-medium mb-2">Speaker Avatar</label>
                    
                    <!-- Avatar Preview -->
                    <div class="mb-4">
                      <div class="flex items-center gap-4">
                        <div class="relative">
                          <div 
                            v-if="localSpeakerData.avatar" 
                            class="w-20 h-20 rounded-full overflow-hidden border-2 border-gray-200"
                          >
                            <img 
                              :src="localSpeakerData.avatar" 
                              :alt="localSpeakerData.name || 'Speaker'"
                              class="w-full h-full object-cover"
                              @error="handleImageError"
                            />
                          </div>
                          <div 
                            v-else 
                            class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center border-2 border-gray-200"
                          >
                            <span class="text-2xl font-medium text-gray-500">
                              {{ getInitials(localSpeakerData.name) }}
                            </span>
                          </div>
                        </div>
                        
                        <div class="flex-1">
                          <div class="flex gap-2 mb-2">
                            <Button
                              label="Browse Gallery"
                              icon="pi pi-images"
                              @click="openMediaBrowser"
                              outlined
                              size="small"
                            />
                            <Button
                              v-if="localSpeakerData.avatar"
                              label="Remove"
                              icon="pi pi-times"
                              @click="removeAvatar"
                              outlined
                              severity="secondary"
                              size="small"
                            />
                          </div>
                          <p class="text-sm text-gray-500">
                            Select an image from the media gallery or enter a URL below
                          </p>
                        </div>
                      </div>
                    </div>

                    <!-- Avatar URL Input -->
                    <div>
                      <label for="speakerAvatar" class="block text-sm font-medium mb-2">Or enter Avatar URL</label>
                      <InputText 
                        id="speakerAvatar" 
                        v-model="localSpeakerData.avatar" 
                        class="w-full" 
                        placeholder="https://example.com/avatar.jpg"
                        type="url"
                      />
                      <small class="text-gray-500 mt-1">Leave empty to show initials instead</small>
                    </div>
                  </div>

                  <!-- Bio -->
                  <div>
                    <label for="speakerBio" class="block text-sm font-medium mb-2">Biography</label>
                    <Textarea
                      id="speakerBio"
                      v-model="localSpeakerData.bio"
                      :rows="4"
                      class="w-full"
                      placeholder="Brief biography about the speaker..."
                    />
                  </div>
                </div>
              </template>
            </Card>

            <!-- Contact Information -->
            <Card>
              <template #title>
                <h4 class="text-lg font-semibold">Contact Information</h4>
              </template>
              <template #content>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-0">
                  <!-- Email -->
                  <div>
                    <label for="speakerEmail" class="block text-sm font-medium mb-2">Email Address</label>
                    <InputText 
                      id="speakerEmail" 
                      v-model="localSpeakerData.email" 
                      class="w-full" 
                      placeholder="speaker@company.com"
                      type="email"
                    />
                  </div>

                  <!-- Phone -->
                  <div>
                    <label for="speakerPhone" class="block text-sm font-medium mb-2">Phone Number</label>
                    <InputText 
                      id="speakerPhone" 
                      v-model="localSpeakerData.phone" 
                      class="w-full" 
                      placeholder="+1 (555) 123-4567"
                      type="tel"
                    />
                  </div>

                  <!-- Website -->
                  <div class="md:col-span-2">
                    <label for="speakerWebsite" class="block text-sm font-medium mb-2">Personal Website</label>
                    <InputText 
                      id="speakerWebsite" 
                      v-model="localSpeakerData.website" 
                      class="w-full" 
                      placeholder="https://www.johndoe.com"
                      type="url"
                    />
                  </div>
                </div>
              </template>
            </Card>

            <!-- Social Media Section -->
            <Card>
              <template #title>
                <h4 class="text-lg font-semibold">Social Media Links</h4>
              </template>
              <template #content>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-0">
                  <div>
                    <label for="speakerLinkedin" class="block text-sm font-medium mb-2">LinkedIn</label>
                    <InputText 
                      id="speakerLinkedin" 
                      v-model="localSpeakerData.social.linkedin" 
                      class="w-full" 
                      placeholder="https://linkedin.com/in/johndoe"
                    />
                  </div>
                  
                  <div>
                    <label for="speakerTwitter" class="block text-sm font-medium mb-2">Twitter/X</label>
                    <InputText 
                      id="speakerTwitter" 
                      v-model="localSpeakerData.social.twitter" 
                      class="w-full" 
                      placeholder="https://twitter.com/johndoe"
                    />
                  </div>
                  
                  <div class="md:col-span-2">
                    <label for="speakerGithub" class="block text-sm font-medium mb-2">GitHub</label>
                    <InputText 
                      id="speakerGithub" 
                      v-model="localSpeakerData.social.github" 
                      class="w-full" 
                      placeholder="https://github.com/johndoe"
                    />
                  </div>
                </div>
              </template>
            </Card>
            
            <!-- Published Checkbox -->
            <Card>
              <template #content>
                <div class="flex items-center p-0">
                  <Checkbox v-model="localPublished" inputId="speakerPublished" binary />
                  <label for="speakerPublished" class="ml-4 text-sm font-medium">
                    Publish immediately
                  </label>
                </div>
                <p class="text-sm mt-3 ml-8 text-gray-600">Published components are visible to conference attendees</p>
              </template>
            </Card>
          </div>
          
          <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4">
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
      header="Select Avatar Image" 
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

          <!-- Media Grid - Only show images -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <Card 
              v-for="item in imageMedia" 
              :key="item.id"
              class="group hover:shadow-lg transition-all duration-200 cursor-pointer"
              @click="selectAvatarImage(item)"
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
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                      <Button
                        icon="pi pi-check"
                        class="rounded-full"
                        size="small"
                        severity="success"
                      />
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
import apiService from '@/services/apiService';
import type { MediaItem } from '@/types/media';

interface SpeakerData {
  name: string;
  title: string;
  company: string;
  email: string;
  phone: string;
  website: string;
  bio: string;
  avatar: string;
  social: {
    linkedin: string;
    twitter: string;
    github: string;
  };
}

export default defineComponent({
  name: 'SpeakerEditor',
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
      type: Object as PropType<SpeakerData>,
      default: () => ({
        name: '',
        title: '',
        company: '',
        email: '',
        phone: '',
        website: '',
        bio: '',
        avatar: '',
        social: {
          linkedin: '',
          twitter: '',
          github: ''
        }
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
      localSpeakerData: {
        name: '',
        title: '',
        company: '',
        email: '',
        phone: '',
        website: '',
        bio: '',
        avatar: '',
        social: {
          linkedin: '',
          twitter: '',
          github: ''
        }
      } as SpeakerData,
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
      
      const speakerData = this.componentData || {};
      
      this.localSpeakerData = { 
        name: speakerData.name || '',
        title: speakerData.title || '',
        company: speakerData.company || '',
        email: speakerData.email || '',
        phone: speakerData.phone || '',
        website: speakerData.website || '',
        bio: speakerData.bio || '',
        avatar: speakerData.avatar || '',
        social: {
          linkedin: speakerData.social?.linkedin || '',
          twitter: speakerData.social?.twitter || '',
          github: speakerData.social?.github || ''
        }
      };
      
      this.localPublished = this.isPublished;
    },
    
    handleSave() {
      if (!this.localSpeakerData.name.trim()) {
        this.$toast?.add({
          severity: 'warn',
          summary: 'Validation Error',
          detail: 'Speaker name is required',
          life: 3000
        });
        return;
      }
      
      this.$emit('save', {
        name: this.localComponentName,
        data: this.localSpeakerData,
        isPublished: this.localPublished
      });
    },
    
    handleCancel() {
      this.$emit('cancel');
      this.$emit('update:visible', false);
    },

    getInitials(name: string): string {
      if (!name) return '';
      
      return name
        .split(' ')
        .map(word => word.charAt(0).toUpperCase())
        .slice(0, 2)
        .join('');
    },

    handleImageError() {
      // Reset avatar if image fails to load
      this.localSpeakerData.avatar = '';
    },

    removeAvatar() {
      this.localSpeakerData.avatar = '';
    },

    // Media browser methods
    openMediaBrowser() {
      this.showMediaBrowser = true;
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
    
    selectAvatarImage(media: MediaItem) {
      // Use the full download URL for the avatar
      const avatarUrl = media.download_url || `/v1/conferences/${this.conferenceId}/media/${media.id}/download`;
      this.localSpeakerData.avatar = avatarUrl;
      this.showMediaBrowser = false;
      
      this.$toast?.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Avatar image selected successfully',
        life: 3000
      });
    },
    
    isImage(mimeType: string): boolean {
      return mimeType.startsWith('image/');
    }
  }
});
</script>