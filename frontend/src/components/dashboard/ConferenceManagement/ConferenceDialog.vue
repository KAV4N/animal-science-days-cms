<template>
  <Dialog
    v-model:visible="visible"
    modal
    :header="dialogHeader"
    :style="{ width: '50vw' }"
    :breakpoints="{ '960px': '75vw', '641px': '90vw' }"
    @hide="resetForm"
    class="conference-dialog"
  >
  <template #header>
      <div class="dialog-header">
        <div class="header-title">{{ dialogHeader }}</div>
        <Tabs v-model:value="activeTabIndex" class="header-tabs">
          <TabList>
            <Tab value="0">Basic Info</Tab>
            <Tab value="1">Location & Dates</Tab>
            <Tab value="2">Theme & Colors</Tab>
            <Tab value="3">Settings</Tab>
          </TabList>
        </Tabs>
      </div>
    </template>
    <div class="dialog-content">
      <!-- Tab Panels in separate Tabs component that's linked to the same model -->
      <Tabs v-model:value="activeTabIndex" class="tab-panels">
        <TabPanels>
          <!-- Basic Information Tab -->
          <TabPanel value="0">
            <div class="grid gap-4 pt-4">
              <!-- Name and Title -->
              <div class="grid md:grid-cols-2 gap-4">
                <div class="field">
                  <label for="name" class="font-medium">Conference Name *</label>
                  <InputText
                    id="name"
                    v-model.trim="v$.formData.name.$model"
                    class="w-full"
                    :class="{ 'p-invalid': v$.formData.name.$error }"
                    placeholder="e.g. International Conference on Science"
                    @blur="v$.formData.name.$touch()"
                  />
                  <small v-if="v$.formData.name.$error" class="p-error">
                    {{ v$.formData.name.$errors[0]?.$message }}
                  </small>
                </div>

                <div class="field">
                  <label for="title" class="font-medium">Conference Title *</label>
                  <InputText
                    id="title"
                    v-model.trim="v$.formData.title.$model"
                    class="w-full"
                    :class="{ 'p-invalid': v$.formData.title.$error }"
                    placeholder="e.g. Advancing Scientific Research"
                    @blur="v$.formData.title.$touch()"
                  />
                  <small v-if="v$.formData.title.$error" class="p-error">
                    {{ v$.formData.title.$errors[0]?.$message }}
                  </small>
                </div>
              </div>

              <!-- Slug and University -->
              <div class="grid md:grid-cols-2 gap-4">
                <div class="field">
                  <label for="slug" class="font-medium">Slug *</label>
                  <InputText
                    id="slug"
                    v-model.trim="v$.formData.slug.$model"
                    class="w-full"
                    :class="{ 'p-invalid': v$.formData.slug.$error }"
                    placeholder="e.g. icsr-2024"
                    @blur="v$.formData.slug.$touch()"
                  />
                  <small v-if="v$.formData.slug.$error" class="p-error">
                    {{ v$.formData.slug.$errors[0]?.$message }}
                  </small>
                </div>

                <div class="field">
                  <label for="university" class="font-medium">University *</label>
                  <Dropdown
                    id="university"
                    v-model="v$.formData.university_id.$model"
                    :options="universities"
                    option-label="full_name"
                    option-value="id"
                    class="w-full"
                    :class="{ 'p-invalid': v$.formData.university_id.$error }"
                    placeholder="Select University"
                    @blur="v$.formData.university_id.$touch()"
                  />
                  <small v-if="v$.formData.university_id.$error" class="p-error">
                    {{ v$.formData.university_id.$errors[0]?.$message }}
                  </small>
                </div>
              </div>

              <!-- Description -->
              <div class="field">
                <label for="description" class="font-medium">Description</label>
                <Textarea
                  id="description"
                  v-model="formData.description"
                  rows="3"
                  class="w-full"
                  placeholder="Enter conference description..."
                />
              </div>
            </div>
          </TabPanel>

          <!-- Location & Dates Tab -->
          <TabPanel value="1">
            <div class="grid gap-4 pt-4">
              <!-- Dates -->
              <div class="grid md:grid-cols-2 gap-4">
                <div class="field">
                  <label for="start_date" class="font-medium">Start Date *</label>
                  <Calendar
                    id="start_date"
                    v-model="v$.formData.start_date.$model"
                    dateFormat="yy-mm-dd"
                    class="w-full"
                    :class="{ 'p-invalid': v$.formData.start_date.$error }"
                    placeholder="Select Start Date"
                    :manualInput="false"
                    @blur="v$.formData.start_date.$touch()"
                  />
                  <small v-if="v$.formData.start_date.$error" class="p-error">
                    {{ v$.formData.start_date.$errors[0]?.$message }}
                  </small>
                </div>

                <div class="field">
                  <label for="end_date" class="font-medium">End Date *</label>
                  <DatePicker
                    id="end_date"
                    v-model="v$.formData.end_date.$model"
                    dateFormat="yy-mm-dd"
                    class="w-full"
                    :class="{ 'p-invalid': v$.formData.end_date.$error }"
                    placeholder="Select End Date"
                    :minDate="formData.start_date ? new Date(formData.start_date) : null"
                    :manualInput="false"
                    @blur="v$.formData.end_date.$touch()"
                  />
                  <small v-if="v$.formData.end_date.$error" class="p-error">
                    {{ v$.formData.end_date.$errors[0]?.$message }}
                  </small>
                </div>
              </div>

              <!-- Location and Venue -->
              <div class="grid md:grid-cols-2 gap-4">
                <div class="field">
                  <label for="location" class="font-medium">Location *</label>
                  <InputText
                    id="location"
                    v-model.trim="v$.formData.location.$model"
                    class="w-full"
                    :class="{ 'p-invalid': v$.formData.location.$error }"
                    placeholder="e.g. New York, USA"
                    @blur="v$.formData.location.$touch()"
                  />
                  <small v-if="v$.formData.location.$error" class="p-error">
                    {{ v$.formData.location.$errors[0]?.$message }}
                  </small>
                </div>

                <div class="field">
                  <label for="venue_details" class="font-medium">Venue Details</label>
                  <InputText
                    id="venue_details"
                    v-model="formData.venue_details"
                    class="w-full"
                    placeholder="e.g. Conference Hall A, Building 5"
                  />
                </div>
              </div>
            </div>
          </TabPanel>

          <!-- Theme & Colors Tab -->
          <TabPanel value="2">
            <div class="grid gap-4 pt-4">
              <!-- Colors -->
              <div class="grid md:grid-cols-2 gap-4">
                <div class="field">
                  <label for="primary_color" class="font-medium">Primary Color *</label>
                  <TransparentColorPicker
                    v-model="v$.formData.primary_color.$model"
                    class="w-full"
                    :class="{ 'p-invalid': v$.formData.primary_color.$error }"
                    format="hex"
                    @blur="v$.formData.primary_color.$touch()"
                  />
                  <small v-if="v$.formData.primary_color.$error" class="p-error">
                    {{ v$.formData.primary_color.$errors[0]?.$message }}
                  </small>
                </div>

                <div class="field">
                  <label for="secondary_color" class="font-medium">Secondary Color *</label>
                  <TransparentColorPicker
                    v-model="v$.formData.secondary_color.$model"
                    class="w-full"
                    :class="{ 'p-invalid': v$.formData.secondary_color.$error }"
                    format="hex"
                    @blur="v$.formData.secondary_color.$touch()"
                  />
                  <small v-if="v$.formData.secondary_color.$error" class="p-error">
                    {{ v$.formData.secondary_color.$errors[0]?.$message }}
                  </small>
                </div>
              </div>
            </div>
          </TabPanel>

          <!-- Settings Tab -->
          <TabPanel value="3">
            <div class="grid gap-4 pt-4">
              <!-- Status Toggles -->
              <div class="grid md:grid-cols-2 gap-4">
                <div class="field-checkbox">
                  <Checkbox
                    id="is_published"
                    v-model="formData.is_published"
                    :binary="true"
                  />
                  <label for="is_published">Published</label>
                </div>

                <div class="field-checkbox">
                  <Checkbox
                    id="is_latest"
                    v-model="formData.is_latest"
                    :binary="true"
                  />
                  <label for="is_latest">Mark as Latest Conference</label>
                </div>
              </div>
            </div>
          </TabPanel>
        </TabPanels>
      </Tabs>
    </div>
  

    <template #footer>
      <div class="flex justify-end gap-2">
        <Button
          type="button"
          label="Cancel"
          icon="pi pi-times"
          class="p-button-text"
          @click="hideConferenceDialog"
        />
        <Button
          type="button"
          :label="isEditing ? 'Apply' : 'Create'"
          icon="pi pi-check"
          :loading="loading"
          @click="handleSubmit"
        />
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
import type { Conference, CreateConferencePayload, UpdateConferencePayload } from '@/types/conference';
import type { University } from '@/types/university';
import TransparentColorPicker from './TransparentColorPicker.vue'; 

export default defineComponent({
  name: 'ConferenceDialog',
  components: {
    TransparentColorPicker  
  },
  emits: ['conference-updated', 'conference-created'],
  setup() {
    return { v$: useVuelidate() };
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
        description: '' as string | undefined,
        location: '',
        venue_details: '' as string | undefined,
        start_date: null as Date | string | null,
        end_date: null as Date | string | null,
        primary_color: '#3B82F6', // Default blue color
        secondary_color: '#10B981', // Default green color
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
        // Ensure dates are properly formatted as Date objects
        const startDate = conference.start_date ? new Date(conference.start_date) : null;
        const endDate = conference.end_date ? new Date(conference.end_date) : null;
        
        this.formData = {
          university_id: conference.university?.id || null,
          name: conference.name || '',
          title: conference.title || '',
          slug: conference.slug || '',
          description: conference.description || '',
          location: conference.location || '',
          venue_details: conference.venue_details || '',
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
      console.log(this.formData.primary_color)
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
        // Format dates properly for API
        const formattedData = this.formatDataForApi();
        
        if (this.isEditing && this.currentConferenceId) {
          const payload: UpdateConferencePayload = formattedData;

          const response = await this.conferenceStore.updateConference(this.currentConferenceId, payload);
          this.$emit('conference-updated', response.data);
          
          this.toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Conference updated successfully',
            life: 3000,
          });
        } else {
          const payload: CreateConferencePayload = formattedData as CreateConferencePayload;

          const response = await this.conferenceStore.createConference(payload);
          this.$emit('conference-created', response.data);
          
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
    
    formatDataForApi() {
      // Format dates for API - convert Date objects to ISO strings
      const formatDate = (date: Date | string | null): string => {
        if (!date) return '';
        if (date instanceof Date) {
          return date.toISOString().split('T')[0]; // YYYY-MM-DD
        }
        return date;
      };
      
      return {
        university_id: this.formData.university_id as number,
        name: this.formData.name.trim(),
        title: this.formData.title.trim(),
        slug: this.formData.slug.trim(),
        description: this.formData.description || undefined,
        location: this.formData.location.trim(),
        venue_details: this.formData.venue_details || undefined,
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
    }
  },
});
</script>

<style scoped>
.conference-dialog :deep(.p-dialog-content) {
  overflow-y: auto;
  max-height: 80vh;
}

.sticky-tab-container {
  position: sticky;
  top: 0;
  background-color: white;
  z-index: 10;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #f0f0f0;
}

/* Add spacing between tabs and content */
.tab-panels :deep(.p-tabview-panels) {
  padding-top: 0.5rem;
}

/* Hide the duplicate tab headers from the second Tabs component */
.tab-panels :deep(.p-tabview-nav) {
  display: none;
}
</style>