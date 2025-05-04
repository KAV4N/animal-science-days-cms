<!-- components/dashboard/ConferenceManagement/ConferenceDialog.vue -->
<template>
  <Dialog
    v-model:visible="visible"
    modal
    :header="dialogHeader"
    :style="{ width: '50vw' }"
    :breakpoints="{ '960px': '75vw', '641px': '90vw' }"
    @hide="resetForm"
  >
    <form @submit.prevent="handleSubmit">
      <div class="grid gap-4">
        <!-- Name and Title -->
        <div class="grid md:grid-cols-2 gap-4">
          <div class="field">
            <label for="name" class="font-medium">Conference Name *</label>
            <InputText
              id="name"
              v-model="formData.name"
              class="w-full"
              :class="{ 'p-invalid': errors.name }"
              placeholder="e.g. International Conference on Science"
            />
            <small v-if="errors.name" class="p-error">{{ errors.name }}</small>
          </div>

          <div class="field">
            <label for="title" class="font-medium">Conference Title *</label>
            <InputText
              id="title"
              v-model="formData.title"
              class="w-full"
              :class="{ 'p-invalid': errors.title }"
              placeholder="e.g. Advancing Scientific Research"
            />
            <small v-if="errors.title" class="p-error">{{ errors.title }}</small>
          </div>
        </div>

        <!-- Slug and University -->
        <div class="grid md:grid-cols-2 gap-4">
          <div class="field">
            <label for="slug" class="font-medium">Slug *</label>
            <InputText
              id="slug"
              v-model="formData.slug"
              class="w-full"
              :class="{ 'p-invalid': errors.slug }"
              placeholder="e.g. icsr-2024"
            />
            <small v-if="errors.slug" class="p-error">{{ errors.slug }}</small>
          </div>

          <div class="field">
            <label for="university" class="font-medium">University *</label>
            <Dropdown
              id="university"
              v-model="formData.university_id"
              :options="universities"
              option-label="full_name"
              option-value="id"
              class="w-full"
              :class="{ 'p-invalid': errors.university_id }"
              placeholder="Select University"
            />
            <small v-if="errors.university_id" class="p-error">{{ errors.university_id }}</small>
          </div>
        </div>

        <!-- Dates -->
        <div class="grid md:grid-cols-2 gap-4">
          <div class="field">
            <label for="start_date" class="font-medium">Start Date *</label>
            <Calendar
              id="start_date"
              v-model="formData.start_date"
              date-format="yy-mm-dd"
              class="w-full"
              :class="{ 'p-invalid': errors.start_date }"
              placeholder="Select Start Date"
            />
            <small v-if="errors.start_date" class="p-error">{{ errors.start_date }}</small>
          </div>

          <div class="field">
            <label for="end_date" class="font-medium">End Date *</label>
            <Calendar
              id="end_date"
              v-model="formData.end_date"
              date-format="yy-mm-dd"
              class="w-full"
              :class="{ 'p-invalid': errors.end_date }"
              placeholder="Select End Date"
              :min-date="formData.start_date ? new Date(formData.start_date) : null"
            />
            <small v-if="errors.end_date" class="p-error">{{ errors.end_date }}</small>
          </div>
        </div>

        <!-- Location and Venue -->
        <div class="grid md:grid-cols-2 gap-4">
          <div class="field">
            <label for="location" class="font-medium">Location *</label>
            <InputText
              id="location"
              v-model="formData.location"
              class="w-full"
              :class="{ 'p-invalid': errors.location }"
              placeholder="e.g. New York, USA"
            />
            <small v-if="errors.location" class="p-error">{{ errors.location }}</small>
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

        <!-- Colors -->
        <div class="grid md:grid-cols-2 gap-4">
          <div class="field">
            <label for="primary_color" class="font-medium">Primary Color *</label>
            <ColorPicker
              v-model="formData.primary_color"
              class="w-full"
              :class="{ 'p-invalid': errors.primary_color }"
              format="hex"
            />
            <small v-if="errors.primary_color" class="p-error">{{ errors.primary_color }}</small>
          </div>

          <div class="field">
            <label for="secondary_color" class="font-medium">Secondary Color *</label>
            <ColorPicker
              v-model="formData.secondary_color"
              class="w-full"
              :class="{ 'p-invalid': errors.secondary_color }"
              format="hex"
            />
            <small v-if="errors.secondary_color" class="p-error">{{ errors.secondary_color }}</small>
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

      <div class="flex justify-end gap-2 mt-4">
        <Button
          type="button"
          label="Cancel"
          icon="pi pi-times"
          class="p-button-text"
          @click="visible = false"
        />
        <Button
          type="submit"
          :label="isEditing ? 'Update' : 'Create'"
          icon="pi pi-check"
          :loading="loading"
        />
      </div>
    </form>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';
import { useUniversityStore } from '@/stores/universityStore';
import { useToast } from 'primevue/usetoast';
import type { Conference, CreateConferencePayload, UpdateConferencePayload } from '@/types/conference';
import type { University } from '@/types/university';

export default defineComponent({
  name: 'ConferenceDialog',
  emits: ['conference-updated', 'conference-created'],
  data() {
    return {
      visible: false,
      isEditing: false,
      loading: false,
      conferenceStore: useConferenceStore(),
      universityStore: useUniversityStore(),
      toast: useToast(),
      currentConferenceId: null as number | null,
      universities: [] as University[],
      formData: {
        university_id: undefined as number | undefined,
        name: '',
        title: '',
        slug: '',
        description: undefined as string | undefined,
        location: '',
        venue_details: undefined as string | undefined,
        start_date: '',
        end_date: '',
        primary_color: '#3B82F6', // Default blue color
        secondary_color: '#10B981', // Default green color
        is_latest: false,
        is_published: false,
      },
      errors: {} as Record<string, string>,
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
        this.formData = {
          university_id: conference.university?.id,
          name: conference.name,
          title: conference.title,
          slug: conference.slug,
          description: conference.description || undefined,
          location: conference.location,
          venue_details: conference.venue_details || undefined,
          start_date: conference.start_date,
          end_date: conference.end_date,
          primary_color: conference.primary_color,
          secondary_color: conference.secondary_color,
          is_latest: conference.is_latest,
          is_published: conference.is_published,
        };
      }
      
      this.visible = true;
    },
    resetForm() {
      this.formData = {
        university_id: undefined,
        name: '',
        title: '',
        slug: '',
        description: undefined,
        location: '',
        venue_details: undefined,
        start_date: '',
        end_date: '',
        primary_color: '#3B82F6',
        secondary_color: '#10B981',
        is_latest: false,
        is_published: false,
      };
      this.errors = {};
      this.currentConferenceId = null;
      this.isEditing = false;
    },
    validateForm(): boolean {
      this.errors = {};
      let isValid = true;

      if (!this.formData.name) {
        this.errors.name = 'Conference name is required';
        isValid = false;
      }

      if (!this.formData.title) {
        this.errors.title = 'Conference title is required';
        isValid = false;
      }

      if (!this.formData.slug) {
        this.errors.slug = 'Slug is required';
        isValid = false;
      }

      if (!this.formData.university_id) {
        this.errors.university_id = 'University is required';
        isValid = false;
      }

      if (!this.formData.start_date) {
        this.errors.start_date = 'Start date is required';
        isValid = false;
      }

      if (!this.formData.end_date) {
        this.errors.end_date = 'End date is required';
        isValid = false;
      } else if (this.formData.start_date && new Date(this.formData.end_date) < new Date(this.formData.start_date)) {
        this.errors.end_date = 'End date must be after start date';
        isValid = false;
      }

      if (!this.formData.location) {
        this.errors.location = 'Location is required';
        isValid = false;
      }

      if (!this.formData.primary_color) {
        this.errors.primary_color = 'Primary color is required';
        isValid = false;
      }

      if (!this.formData.secondary_color) {
        this.errors.secondary_color = 'Secondary color is required';
        isValid = false;
      }

      return isValid;
    },
    async handleSubmit() {
      if (!this.validateForm()) return;

      this.loading = true;

      try {
        if (this.isEditing && this.currentConferenceId) {
          const payload: UpdateConferencePayload = {
            university_id: this.formData.university_id,
            name: this.formData.name,
            title: this.formData.title,
            slug: this.formData.slug,
            description: this.formData.description,
            location: this.formData.location,
            venue_details: this.formData.venue_details,
            start_date: this.formData.start_date,
            end_date: this.formData.end_date,
            primary_color: this.formData.primary_color,
            secondary_color: this.formData.secondary_color,
            is_latest: this.formData.is_latest,
            is_published: this.formData.is_published,
          };

          const response = await this.conferenceStore.updateConference(this.currentConferenceId, payload);
          this.$emit('conference-updated', response.data);
        } else {
          const payload: CreateConferencePayload = {
            university_id: this.formData.university_id as number,
            name: this.formData.name,
            title: this.formData.title,
            slug: this.formData.slug,
            description: this.formData.description,
            location: this.formData.location,
            venue_details: this.formData.venue_details,
            start_date: this.formData.start_date,
            end_date: this.formData.end_date,
            primary_color: this.formData.primary_color,
            secondary_color: this.formData.secondary_color,
            is_latest: this.formData.is_latest,
            is_published: this.formData.is_published,
          };

          const response = await this.conferenceStore.createConference(payload);
          this.$emit('conference-created', response.data);
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
  },
});
</script>

<style scoped>
.field {
  margin-bottom: 1rem;
}

.field label {
  display: block;
  margin-bottom: 0.5rem;
}

.field-checkbox {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.field-checkbox label {
  margin-left: 0.5rem;
  margin-bottom: 0;
}
</style>