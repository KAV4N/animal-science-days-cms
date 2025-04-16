<!-- components/dashboard/ConferenceFormTabs/SettingsTab.vue -->
<template>
  <div class="p-3 md:p-5">
    <div class="mb-6">
      <h3 class="text-xl font-semibold mb-3 text-gray-800">Publication Status</h3>
      <Divider />
      <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <div class="flex items-center">
          <div class="mr-4">
            <ToggleSwitch id="isPublished" v-model="isPublished" />
          </div>
          <div>
            <label for="isPublished" class="block font-bold mb-1 text-gray-700">
              {{ isPublished ? 'Published' : 'Draft' }}
            </label>
            <span class="text-sm text-gray-600">
              {{ isPublished 
                 ? 'Conference is visible to the public.' 
                 : 'Conference is only visible to editors and administrators.' }}
            </span>
          </div>
        </div>
      </div>
    </div>
    
    <div class="mb-6" v-if="conference.id">
      <h3 class="text-xl font-semibold mb-3 text-gray-800">Conference Information</h3>
      <Divider />
      <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="p-4 rounded-lg border border-gray-200">
          <h4 class="font-medium text-lg mb-2 text-gray-700">Created</h4>
          <div class="flex items-center">
            <i class="pi pi-calendar text-blue-500 mr-2"></i>
            <span class="text-gray-800">{{ formatDate(conference.createdAt) }}</span>
          </div>
          <div class="flex items-center mt-2">
            <i class="pi pi-clock text-blue-500 mr-2"></i>
            <span class="text-gray-800">{{ formatTime(conference.createdAt) }}</span>
          </div>
        </div>
        <div class="p-4 rounded-lg border border-gray-200">
          <h4 class="font-medium text-lg mb-2 text-gray-700">Last Updated</h4>
          <div class="flex items-center">
            <i class="pi pi-calendar text-blue-500 mr-2"></i>
            <span class="text-gray-800">{{ formatDate(conference.updatedAt) }}</span>
          </div>
          <div class="flex items-center mt-2">
            <i class="pi pi-clock text-blue-500 mr-2"></i>
            <span class="text-gray-800">{{ formatTime(conference.updatedAt) }}</span>
          </div>
        </div>
      </div>
    </div>
    
    <div>
      <h3 class="text-xl font-semibold mb-3 text-gray-800">Advanced Settings</h3>
      <Divider />
      <div class="mt-4 space-y-4">
        <div class="p-4 rounded-lg border border-gray-200 flex flex-column md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <h4 class="font-medium text-gray-700">Edit Conference Pages</h4>
            <p class="text-sm text-gray-600">Modify pages and content for this conference</p>
          </div>
          <Button label="Manage Pages" icon="pi pi-file-edit" severity="secondary" class="w-full md:w-auto" @click="$emit('manage-pages')" />
        </div>
        
        <div class="p-4 rounded-lg border border-gray-200 flex flex-column md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <h4 class="font-medium text-gray-700">Registration Settings</h4>
            <p class="text-sm text-gray-600">Configure registration options and pricing</p>
          </div>
          <Button label="Configure" icon="pi pi-cog" severity="secondary" class="w-full md:w-auto" @click="$emit('configure-registration')" />
        </div>
        
        <div class="p-4 rounded-lg border border-red-100 flex flex-column md:flex-row md:items-center md:justify-between gap-3" v-if="conference.id">
          <div>
            <h4 class="font-medium text-red-700">Danger Zone</h4>
            <p class="text-sm text-red-600">Permanently delete this conference</p>
          </div>
          <Button 
            label="Delete" 
            icon="pi pi-trash" 
            severity="danger" 
            class="w-full md:w-auto" 
            @click="$emit('delete')" 
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';

export default defineComponent({
  name: 'SettingsTab',
  emits: ['delete', 'manage-pages', 'configure-registration'],
  data() {
    return {
      store: useConferenceStore()
    };
  },
  computed: {
    conference() {
      return this.store.currentConference || this.store.getEmptyConference();
    },
    isPublished: {
      get() {
        return this.conference.isPublished;
      },
      set(value: boolean) {
        this.store.setPublishedStatus(value);
      }
    }
  },
  methods: {
    formatDate(value: Date): string {
      if (value) {
        return new Date(value).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      }
      return '';
    },
    formatTime(value: Date): string {
      if (value) {
        return new Date(value).toLocaleTimeString('en-US', {
          hour: '2-digit',
          minute: '2-digit'
        });
      }
      return '';
    }
  }
});
</script>