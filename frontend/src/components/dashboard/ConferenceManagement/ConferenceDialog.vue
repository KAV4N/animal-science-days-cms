<template>
  <Dialog
    ref="dialogRef"
    v-model:visible="visible"
    modal
    maximizable
    :maximized="true"
    :style="{ width: '100vw', height: '100vh' }"
    :contentStyle="{ height: '100%', display: 'flex', flexDirection: 'column' }"
    :breakpoints="{ '960px': '100vw', '641px': '100vw' }"
    @show="handleDialogShow"
    @hide="handleDialogHide"
    class="fullscreen-dialog"
  >
    <template #header>
      <div class="flex items-center w-full">
        <i class="pi pi-calendar mr-2 text-xl"></i>
        <span class="font-bold text-xl">{{ dialogHeader }}</span>
      </div>
    </template>
    
    <div class="dialog-content flex-1 overflow-hidden">
      <Tabs v-model:value="activeTabIndex" scrollable class="h-full flex flex-col">
        <TabList class="sticky top-0 z-10 bg-surface-0 dark:bg-surface-900 border-b border-surface-200 dark:border-surface-700 flex-shrink-0">
          <Tab value="0" class="flex items-center">
            <i class="pi pi-info-circle mr-2"></i>
            Basic Information
          </Tab>
          <Tab value="1" class="flex items-center">
            <i class="pi pi-map-marker mr-2"></i>
            Location & Dates
          </Tab>
          <Tab value="2" class="flex items-center">
            <i class="pi pi-palette mr-2"></i>
            Theme & Colors
          </Tab>
          <Tab value="3" v-if="isEditing" class="flex items-center">
            <i class="pi pi-users mr-2"></i>
            Editors
          </Tab>
          <Tab value="4" class="flex items-center">
            <i class="pi pi-cog mr-2"></i>
            Settings
          </Tab>
        </TabList>
        
        <TabPanels class="flex-1 overflow-auto">
          <TabPanel value="0" class="h-full">
            <div class="p-6 h-full overflow-auto">
              <BasicInfoTab 
                :v$="v$" 
                :formData="formData" 
                :universities="universities" 
              />
            </div>
          </TabPanel>
          <TabPanel value="1" class="h-full">
            <div class="p-6 h-full overflow-auto">
              <LocationDatesTab 
                :v$="v$" 
                :formData="formData" 
              />
            </div>
          </TabPanel>
          <TabPanel value="2" class="h-full">
            <div class="p-6 h-full overflow-auto">
              <ThemeColorsTab 
                :v$="v$" 
                :formData="formData" 
              />
            </div>
          </TabPanel>
          <TabPanel value="3" v-if="isEditing" class="h-full">
            <div class="p-6 h-full overflow-auto">
              <EditorsTab 
                :conferenceId="currentConferenceId"
              />
            </div>
          </TabPanel>
          <TabPanel value="4" class="h-full">
            <div class="p-6 h-full overflow-auto">
              <SettingsTab 
                :formData="formData" 
                :currentConferenceId="currentConferenceId"
                @conference-deleted="handleConferenceDeleted"
              />
            </div>
          </TabPanel>
        </TabPanels>
      </Tabs>
    </div>

    <template #footer>
      <div class="dialog-footer flex-shrink-0">
        <Divider class="w-full m-0 mb-4" />
        <div class="footer-buttons flex justify-end gap-3 px-6 pb-4">
          <Button
            v-if="isEditing"
            type="button"
            label="OK"
            icon="pi pi-check"
            :loading="loading"
            severity="success"
            @click="handleSubmitAndClose"
            class="min-w-24"
          />
          <Button
            v-if="isEditing"
            type="button"
            label="Apply"
            icon="pi pi-check"
            :loading="loading"
            severity="info"
            @click="handleSubmit"
            class="min-w-24"
          />
          <Button
            v-if="!isEditing"
            type="button"
            label="Create"
            icon="pi pi-check"
            :loading="loading"
            severity="success"
            @click="handleSubmitAndClose"
            class="min-w-24"
          />
          <Button
            type="button"
            label="Cancel"
            icon="pi pi-times"
            severity="secondary"
            outlined
            @click="hideConferenceDialog"
            class="min-w-24"
          />
        </div>
      </div>
    </template>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';
import { useUniversityStore } from '@/stores/universityStore';
import { useToast } from 'primevue/usetoast';
import useVuelidate from '@vuelidate/core';
import { required, minLength, helpers } from '@vuelidate/validators';

import type { University } from '@/types/university';
import type { Conference, Editor, ConferenceStoreRequest, ConferenceUpdateRequest } from '@/types/conference';

import BasicInfoTab from './tabs/BasicInfoTab.vue';
import LocationDatesTab from './tabs/LocationDatesTab.vue';
import ThemeColorsTab from './tabs/ThemeColorsTab.vue';
import SettingsTab from './tabs/SettingsTab.vue';
import EditorsTab from './tabs/EditorsTab.vue';

export default defineComponent({
  name: 'ConferenceDialog',
  components: {
    BasicInfoTab,
    LocationDatesTab,
    ThemeColorsTab,
    SettingsTab,
    EditorsTab
  },
  emits: ['conference-updated', 'conference-created', 'conference-deleted'],
  
  data() {
    return {
      visible: false,
      isEditing: false,
      loading: false,
      activeTabIndex: '0',
      conferenceStore: useConferenceStore(),
      universityStore: useUniversityStore(),
      toast: useToast(),
      v$: useVuelidate(),
      dialogRef: null as any,
      currentConferenceId: null as number | null,
      universities: [] as University[],
      formData: {
        university_id: null as number | null,
        name: '',
        title: '',
        slug: '',
        description: '' as string | null,
        location: '',
        venue_details: '' as string | null,
        start_date: null as Date | string | null,
        end_date: null as Date | string | null,
        editors: [] as Editor[],
        primary_color: '#3B82F6',
        secondary_color: '#10B981',
        is_latest: false,
        is_published: false,
      },
    };
  },
  
  computed: {
    dialogHeader(): string {
      return this.isEditing ? 'Edit Conference' : 'Create New Conference';
    }
  },
  
  validations() {
    return {
      formData: {
        name: {
          required: helpers.withMessage('Conference name is required', required),
        },
        title: {
          required: helpers.withMessage('Conference title is required', required),
        },
        slug: {
          required: helpers.withMessage('Slug is required', required),
          minLength: helpers.withMessage('Slug must be at least 3 characters', minLength(3)),
        },
        university_id: {
          required: helpers.withMessage('University is required', required),
        },
        start_date: {
          required: helpers.withMessage('Start date is required', required),
          isValidDate: helpers.withMessage(
            'Invalid start date',
            (value: Date | string | null) => !!value
          ),
        },
        end_date: {
          required: helpers.withMessage('End date is required', required),
          isValidDate: helpers.withMessage(
            'Invalid end date',
            (value: Date | string | null) => !!value
          ),
          afterStartDate: helpers.withMessage(
            'End date must be after start date',
            (value: Date | string | null) => {
              if (!value || !this.formData.start_date) return true;
              const startDate = new Date(this.formData.start_date);
              const endDate = new Date(value);
              return endDate >= startDate;
            }
          ),
        },
        location: {
          required: helpers.withMessage('Location is required', required),
        },
        primary_color: {
          required: helpers.withMessage('Primary color is required', required),
          isValidColor: helpers.withMessage(
            'Invalid color format',
            (value: string) => /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
          ),
        },
        secondary_color: {
          required: helpers.withMessage('Secondary color is required', required),
          isValidColor: helpers.withMessage(
            'Invalid color format',
            (value: string) => /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
          ),
        },
      }
    };
  },
  
  async mounted() {
    await this.loadUniversities();
  },
  
  methods: {
    hideConferenceDialog() {
      this.visible = false;
    },

    async loadUniversities() {
      try {
        await this.universityStore.fetchUniversities();
        this.universities = this.universityStore.universities;
      } catch (error: any) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.message || 'Failed to load universities',
          life: 3000,
        });
      }
    },

    openDialog(conference?: Conference) {
      this.isEditing = !!conference;
      this.currentConferenceId = conference?.id || null;

      if (conference) {
        const startDate = conference.start_date ? new Date(conference.start_date) : null;
        const endDate = conference.end_date ? new Date(conference.end_date) : null;

        this.formData = {
          university_id: conference.university?.id || null,
          name: conference.name || '',
          title: conference.title || '',
          slug: conference.slug || '',
          description: conference.description,
          location: conference.location || '',
          venue_details: conference.venue_details || null,
          start_date: startDate,
          end_date: endDate,
          primary_color: conference.primary_color || '#3B82F6',
          secondary_color: conference.secondary_color || '#10B981',
          is_latest: conference.is_latest || false,
          is_published: conference.is_published || false,
        };
      } else {
        this.resetForm();
      }

      this.visible = true;
      this.v$.$reset();
    },

    resetForm() {
      this.formData = {
        university_id: null,
        name: '',
        title: '',
        slug: '',
        description: '',
        location: '',
        venue_details: '',
        start_date: null,
        end_date: null,
        editors: [],
        primary_color: '#3B82F6',
        secondary_color: '#10B981',
        is_latest: false,
        is_published: false,
      };
      this.currentConferenceId = null;
      this.isEditing = false;
      this.activeTabIndex = '0';
      this.v$.$reset();
    },

    async handleDialogShow() {
      // No lock-related logic needed
    },

    async handleDialogHide() {
      this.resetForm();
    },

    async handleSubmit() {
      const isFormValid = await this.v$.$validate();

      if (!isFormValid) {
        this.showTabWithErrors();
        this.toast.add({
          severity: 'warn',
          summary: 'Validation Error',
          detail: 'Please fill in all required fields correctly',
          life: 3000,
        });
        return;
      }

      this.loading = true;

      try {
        const formattedData = this.formatDataForApi();

        if (this.isEditing && this.currentConferenceId) {
          const payload: ConferenceUpdateRequest = formattedData;
          const response = await this.conferenceStore.updateConference(this.currentConferenceId, payload);
          
          this.$emit('conference-updated', response.payload);
          this.toast.add({
            severity: 'success',
            summary: 'Success',
            detail: response.message || 'Conference updated successfully',
            life: 3000,
          });
        } else {
          const payload: ConferenceStoreRequest = formattedData;
          const response = await this.conferenceStore.createConference(payload);
          this.$emit('conference-created', response.payload);
          this.toast.add({
            severity: 'success',
            summary: 'Success',
            detail: response.message || 'Conference created successfully',
            life: 3000,
          });
          
          this.visible = false;
          this.resetForm();
        }
      } catch (error: any) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.message || error instanceof Error ? error.message : 'Failed to save conference',
          life: 3000,
        });
      } finally {
        this.loading = false;
      }
    },
    
    async handleSubmitAndClose() {
      const isFormValid = await this.v$.$validate();

      if (!isFormValid) {
        this.showTabWithErrors();
        this.toast.add({
          severity: 'warn',
          summary: 'Validation Error',
          detail: 'Please fill in all required fields correctly',
          life: 3000,
        });
        return;
      }

      this.loading = true;

      try {
        const formattedData = this.formatDataForApi();

        if (this.isEditing && this.currentConferenceId) {
          const payload: ConferenceUpdateRequest = formattedData;
          const response = await this.conferenceStore.updateConference(this.currentConferenceId, payload);
          this.$emit('conference-updated', response.payload);
          this.toast.add({
            severity: 'success',
            summary: 'Success',
            detail: response.message || 'Conference updated successfully',
            life: 3000,
          });
        } else {
          const payload: ConferenceStoreRequest = formattedData;
          const response = await this.conferenceStore.createConference(payload);
          this.$emit('conference-created', response.payload);
          this.toast.add({
            severity: 'success',
            summary: 'Success',
            detail: response.message || 'Conference created successfully',
            life: 3000,
          });
        }

        this.visible = false;
        this.resetForm();
      } catch (error: any) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.message || error instanceof Error ? error.message : 'Failed to save conference',
          life: 3000,
        });
      } finally {
        this.loading = false;
      }
    },

    formatDataForApi(): ConferenceStoreRequest {
      const formatDate = (date: Date | string | null): string => {
        if (!date) return '';
        if (date instanceof Date) {
          return date.toISOString().split('T')[0];
        }
        return date;
      };

      return {
        university_id: this.formData.university_id as number,
        name: this.formData.name.trim(),
        title: this.formData.title.trim(),
        slug: this.formData.slug.trim(),
        description: this.formData.description || null,
        location: this.formData.location.trim(),
        venue_details: this.formData.venue_details || null,
        start_date: formatDate(this.formData.start_date),
        end_date: formatDate(this.formData.end_date),
        primary_color: this.formData.primary_color,
        secondary_color: this.formData.secondary_color,
        is_latest: this.formData.is_latest,
        is_published: this.formData.is_published,
      };
    },

    showTabWithErrors() {
      if (this.hasBasicInfoErrors()) {
        this.activeTabIndex = '0';
      } else if (this.hasLocationDateErrors()) {
        this.activeTabIndex = '1';
      } else if (this.hasColorErrors()) {
        this.activeTabIndex = '2';
      }
    },

    hasBasicInfoErrors() {
      return this.v$.formData.name.$error ||
             this.v$.formData.title.$error ||
             this.v$.formData.slug.$error ||
             this.v$.formData.university_id.$error;
    },

    hasLocationDateErrors() {
      return this.v$.formData.start_date.$error ||
             this.v$.formData.end_date.$error ||
             this.v$.formData.location.$error;
    },

    hasColorErrors() {
      return this.v$.formData.primary_color.$error ||
             this.v$.formData.secondary_color.$error;
    },

    handleConferenceDeleted() {
      this.visible = false;
      this.resetForm();
      this.$emit('conference-deleted');
    }
  },
});
</script>

<style scoped>
.fullscreen-dialog :deep(.p-dialog) {
  width: 100vw !important;
  height: 100vh !important;
  max-width: none !important;
  max-height: none !important;
  margin: 0 !important;
  border-radius: 0 !important;
}

.fullscreen-dialog :deep(.p-dialog-content) {
  height: 100% !important;
  display: flex !important;
  flex-direction: column !important;
  padding: 0 !important;
  overflow: hidden !important;
}

.fullscreen-dialog :deep(.p-dialog-header) {
  flex-shrink: 0;
  border-radius: 0 !important;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--p-surface-200);
}

.fullscreen-dialog :deep(.p-dialog-footer) {
  flex-shrink: 0;
  border-radius: 0 !important;
  padding: 0 !important;
  border-top: 1px solid var(--p-surface-200);
}

.fullscreen-dialog :deep(.p-tabview) {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.fullscreen-dialog :deep(.p-tabview-nav-container) {
  flex-shrink: 0;
}

.fullscreen-dialog :deep(.p-tabview-panels) {
  flex: 1;
  overflow: hidden;
}

.fullscreen-dialog :deep(.p-tabview-panel) {
  height: 100%;
  padding: 0 !important;
}

@media (max-width: 960px) {
  .fullscreen-dialog :deep(.p-dialog-header) {
    padding: 1rem 1.5rem;
  }
  
  .footer-buttons {
    padding: 0 1.5rem 1rem 1.5rem !important;
  }
}

@media (max-width: 640px) {
  .fullscreen-dialog :deep(.p-dialog-header) {
    padding: 0.75rem 1rem;
  }
  
  .footer-buttons {
    padding: 0 1rem 0.75rem 1rem !important;
    flex-direction: column;
    gap: 0.5rem !important;
  }
  
  .footer-buttons .min-w-24 {
    min-width: 100%;
  }
}
</style>