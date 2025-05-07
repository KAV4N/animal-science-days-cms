<template>
  <div class="grid gap-4">
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
        <small v-if="v$.formData.name.$error" class="text-red-500">
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
        <small v-if="v$.formData.title.$error" class="text-red-500">
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
        <small v-if="v$.formData.slug.$error" class="text-red-500">
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
        <small v-if="v$.formData.university_id.$error" class="text-red-500">
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
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { required, minLength, helpers } from '@vuelidate/validators';
import type { University } from '@/types/university';

export default defineComponent({
  name: 'BasicInfoTab',
  props: {
    formData: {
      type: Object,
      required: true
    },
    universities: {
      type: Array as () => University[],
      required: true
    },
    v$: {
      type: Object,
      required: true
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
        }
      }
    };
  }
});
</script>