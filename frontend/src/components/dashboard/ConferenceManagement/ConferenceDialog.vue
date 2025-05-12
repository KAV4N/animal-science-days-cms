<template>
  <Dialog
    ref="dialogRef"
    v-model:visible="visible"
    modal
    maximizable
    @hide="resetForm"
    @show="maximizeDialog"
    class="p-fluid"
    :style="{ width: '90vw' }" 
  >
    <template #header>
      <div class="flex items-center">
        <i class="pi pi-calendar mr-2"></i>
        <span class="font-bold text-lg">{{ dialogHeader }}</span>
      </div>
    </template>
    
    <Tabs v-model:value="activeTabIndex" scrollable>
      <TabList class="sticky top-0 z-10 bg-white dark:bg-gray-900">
        <Tab value="0">
          <i class="pi pi-info-circle mr-2"></i>
          Basic Information
        </Tab>
        <Tab value="1">
          <i class="pi pi-map-marker mr-2"></i>
          Location & Dates
        </Tab>
        <Tab value="2">
          <i class="pi pi-palette mr-2"></i>
          Theme & Colors
        </Tab>
        <Tab value="3">
          <i class="pi pi-users mr-2"></i>
          Editors
        </Tab>
        <Tab value="4">
          <i class="pi pi-cog mr-2"></i>
          Settings
        </Tab>
      </TabList>
      <TabPanels>
        <TabPanel value="0">
          <div class="p-4">
            <BasicInfoTab 
              :v$="v$" 
              :formData="formData" 
              :universities="universities" 
            />
          </div>
        </TabPanel>
        <TabPanel value="1">
          <div class="p-4">
            <LocationDatesTab 
              :v$="v$" 
              :formData="formData" 
            />
          </div>
        </TabPanel>
        <TabPanel value="2">
          <div class="p-4">
            <ThemeColorsTab 
              :v$="v$" 
              :formData="formData" 
            />
          </div>
        </TabPanel>


        <TabPanel value="3">
          <div class="p-4">
            <EditorsTab 
              :conferenceId="currentConferenceId"
            />
          </div>
        </TabPanel>

        <TabPanel value="4">
          <div class="p-4">
            <SettingsTab 
              :formData="formData" 
              :currentConferenceId="currentConferenceId"
              @conference-deleted="handleConferenceDeleted"
            />
          </div>
        </TabPanel>
      </TabPanels>
    </Tabs>

    <template #footer>
      <div class="flex flex-col w-full">
        <Divider class="w-full m-0" />
        <div class="flex flex-nowrap justify-end gap-2 my-4 w-full">
          <Button
            type="button"
            :label="isEditing ? 'Apply' : 'Create'"
            icon="pi pi-check"
            :loading="loading"
            @click="handleSubmit"
          />
          <Button
            type="button"
            label="Cancel"
            icon="pi pi-times"
            text
            @click="hideConferenceDialog"
          />
        </div>
      </div>
    </template>
  </Dialog>
</template>
<script lang="ts">
import { defineComponent, ref } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';
import { useUniversityStore } from '@/stores/universityStore';
import { useToast } from 'primevue/usetoast';
import useVuelidate from '@vuelidate/core';
import { required, minLength, helpers } from '@vuelidate/validators';

import type { University } from '@/types/university';
import type { User } from '@/types/user';
import type {
  Conference,
  Editor,
  ConferenceStoreRequest,
  ConferenceUpdateRequest,
} from '@/types/conference';

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
  setup() {
    const dialogRef = ref();
    const v$ = useVuelidate();

    const maximizeDialog = () => {
      if (dialogRef.value && !dialogRef.value.maximized) {
        dialogRef.value.maximize();
      }
    };

    return {
      dialogRef,
      v$,
      maximizeDialog,
    };
  },
  data() {
    return {
      visible: false,
      isEditing: false,
      loading: false,
      activeTabIndex: '0',
      conferenceStore: useConferenceStore(),
      universityStore: useUniversityStore(),
      toast: useToast(),
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
      }
    };
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
  computed: {
    dialogHeader(): string {
      return this.isEditing ? 'Edit Conference' : 'Create New Conference';
    },
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
      } catch (error) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to load universities',
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
          editors: conference.editors || [],
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
            detail: 'Conference updated successfully',
            life: 3000,
          });
        } else {
          const payload: ConferenceStoreRequest = formattedData;
          const response = await this.conferenceStore.createConference(payload);
          this.$emit('conference-created', response.payload);
          this.toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Conference created successfully',
            life: 3000,
          });
        }

        this.visible = false;
        this.resetForm();
      } catch (error) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error instanceof Error ? error.message : 'Failed to save conference',
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