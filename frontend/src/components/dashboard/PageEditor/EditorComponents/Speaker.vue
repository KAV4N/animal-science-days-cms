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
                              @click="openMediaManager"
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
    
    <!-- MediaManager Integration -->
    <MediaManager
      v-model:visible="showMediaManager"
      selectionMode="single"
      :allowedMimeTypes="['image/*']"
      @select="handleMediaSelect"
      :conferenceId="conferenceId"
    />
  </Dialog>
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import MediaManager from '@/components/dashboard/PageEditor/MediaManager.vue'; // Adjust path as needed
import apiService from '@/services/apiService';

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
  components: {
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
      showMediaManager: false
    };
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
      this.localSpeakerData.avatar = '';
    },

    removeAvatar() {
      this.localSpeakerData.avatar = '';
    },

    openMediaManager() {
      this.showMediaManager = true;
    },
    
    handleMediaSelect(selectedItem: any) {
      if (selectedItem) {
        const avatarUrl = selectedItem.download_url || `/v1/conferences/${this.conferenceId}/media/${selectedItem.id}/download`;
        this.localSpeakerData.avatar = avatarUrl;
        this.$toast?.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Avatar image selected successfully',
          life: 3000
        });
      }
      this.showMediaManager = false;
    }
  }
});
</script>