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
        <div class="p-4 rounded-lg border border-red-700 flex flex-column md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <h4 class="font-medium text-red-700">Danger Zone</h4>
            <p class="text-sm">Permanently delete this conference</p>
          </div>
          <Button 
            label="Delete" 
            icon="pi pi-trash" 
            severity="danger" 
            class="w-full md:w-auto" 
            @click="confirmDelete" 
          />
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <Dialog 
      v-model:visible="deleteDialogVisible" 
      header="Confirm Deletion" 
      :modal="true"
      :style="{ width: '450px' }"
    >
      <div class="confirmation-content flex items-center">
        <i class="pi pi-exclamation-triangle mr-3 text-yellow-500" style="font-size: 2rem"></i>
        <span>Are you sure you want to delete this conference?</span>
      </div>
      <template #footer>
        <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteDialogVisible = false" />
        <Button label="Yes" icon="pi pi-check" severity="danger" @click="deleteConference" />
      </template>
    </Dialog>
  </div>
</template>

<script>
import { useConferenceStore } from '@/stores/conferenceStore';
import { useToast } from 'primevue/usetoast';
import { useRouter } from 'vue-router';
import ToggleSwitch from 'primevue/toggleswitch';
import Divider from 'primevue/divider';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

export default {
  name: 'SettingsTab',
  components: {
    ToggleSwitch,
    Divider,
    Button,
    Dialog
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
  emits: ['conference-deleted', 'configure-registration'],
  data() {
    return {
      deleteDialogVisible: false,
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
    },
    confirmDelete() {
      this.deleteDialogVisible = true;
    },
    async deleteConference() {
      if (!this.currentConferenceId) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'No conference selected for deletion',
          life: 3000
        });
        this.deleteDialogVisible = false;
        return;
      }

      try {
        await this.conferenceStore.deleteConference(this.currentConferenceId);
        this.deleteDialogVisible = false;
        
        this.toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Conference deleted successfully',
          life: 3000
        });
        
        this.$emit('conference-deleted');
      } catch (error) {
        console.error('Failed to delete conference:', error);
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to delete conference',
          life: 3000
        });
        this.deleteDialogVisible = false;
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