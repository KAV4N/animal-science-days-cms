<template>
  <div class="p-3 md:p-5">
    <!-- Latest Conference -->
    <div class="mb-6">
      <h3 class="text-xl font-semibold mb-3">Latest Conference</h3>
      <Divider />
      <div class="mt-4 p-4 rounded-lg border">
        <div class="flex items-center">
          <div class="mr-4">
            <ToggleSwitch id="isLatest" v-model="formData.is_latest" />
          </div>
          <div>
            <label for="isLatest" class="block font-bold mb-1">
              {{ formData.is_latest ? 'Latest Conference' : 'Not Latest' }}
            </label>
            <span class="text-sm">
              {{ formData.is_latest 
                 ? 'Conference will be featured as the latest event.' 
                 : 'Conference will not be highlighted as the latest event.' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Publication Status -->
    <div class="mb-6">
      <h3 class="text-xl font-semibold mb-3">Publication Status</h3>
      <Divider />
      <div class="mt-4 p-4 rounded-lg border">
        <div class="flex items-center">
          <div class="mr-4">
            <ToggleSwitch id="isPublished" v-model="formData.is_published" />
          </div>
          <div>
            <label for="isPublished" class="block font-bold mb-1">
              {{ formData.is_published ? 'Published' : 'Draft' }}
            </label>
            <span class="text-sm">
              {{ formData.is_published 
                 ? 'Conference is visible to the public.' 
                 : 'Conference is only visible to editors and administrators.' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Advanced Settings -->
    <div v-if="currentConferenceId">
      <h3 class="text-xl font-semibold mb-3">Advanced Settings</h3>
      <Divider />
      <div class="mt-4 space-y-4">
        <div class="p-4 rounded-lg border border-gray-200 flex flex-column md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <h4 class="font-medium">Edit Conference Pages</h4>
            <p class="text-sm">Modify pages and content for this conference</p>
          </div>
          <Button 
            label="Manage Pages" 
            icon="pi pi-file-edit" 
            severity="secondary" 
            class="w-full md:w-auto" 
            @click="goToConferenceEdit"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useConferenceStore } from '@/stores/conferenceStore';
import { useToast } from 'primevue/usetoast';
import { useRouter } from 'vue-router';
import ToggleSwitch from 'primevue/toggleswitch';
import Divider from 'primevue/divider';
import Button from 'primevue/button';

export default {
  name: 'SettingsTab',
  components: {
    ToggleSwitch,
    Divider,
    Button
  },
  props: {
    formData: {
      type: Object,
      required: true
    },
    currentConferenceId: {
      type: Number,
      default: null
    }
  },
  emits: ['configure-registration'],
  data() {
    return {
      conferenceStore: useConferenceStore(),
      toast: useToast()
    };
  },
  setup() {
    const router = useRouter();
    return { router };
  },
  methods: {
    formatDate(value) {
      if (value) {
        return new Date(value).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      }
      return '';
    },
    formatTime(value) {
      if (value) {
        return new Date(value).toLocaleTimeString('en-US', {
          hour: '2-digit',
          minute: '2-digit'
        });
      }
      return '';
    },
    goToConferenceEdit() {
      if (this.currentConferenceId) {
        this.router.push({ name: 'ConferenceEdit', params: { id: this.currentConferenceId } });
      }
    }
  }
};
</script>

<style scoped>
.confirmation-content {
  display: flex;
  align-items: center;
}
</style>