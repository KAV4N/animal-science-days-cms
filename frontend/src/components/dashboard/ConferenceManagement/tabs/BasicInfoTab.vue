<template>
  <div class="grid gap-6 p-4">
    <!-- Name and Title -->
    <div class="grid md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label for="name" class="text-sm font-medium text-gray-700">Conference Name *</label>
        <InputText
          id="name"
          v-model.trim="v$.formData.name.$model"
          class="w-full p-3 border rounded-lg"
          :class="{ 'border-red-500': v$.formData.name.$error }"
          placeholder="e.g. International Conference on Science"
          @blur="v$.formData.name.$touch()"
        />
        <small v-if="v$.formData.name.$error" class="text-red-500 text-xs flex items-center gap-1 mt-1">
          <i class="pi pi-info-circle text-xs"></i>
          {{ v$.formData.name.$errors[0]?.$message }}
        </small>
      </div>

      <div class="space-y-2">
        <label for="title" class="text-sm font-medium text-gray-700">Conference Title *</label>
        <InputText
          id="title"
          v-model.trim="v$.formData.title.$model"
          class="w-full p-3 border rounded-lg"
          :class="{ 'border-red-500': v$.formData.title.$error }"
          placeholder="e.g. Advancing Scientific Research"
          @blur="v$.formData.title.$touch()"
        />
        <small v-if="v$.formData.title.$error" class="text-red-500 text-xs flex items-center gap-1 mt-1">
          <i class="pi pi-info-circle text-xs"></i>
          {{ v$.formData.title.$errors[0]?.$message }}
        </small>
      </div>
    </div>

    <!-- Slug and University -->
    <div class="grid md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label for="slug" class="text-sm font-medium text-gray-700">Slug *</label>
        <InputText
          id="slug"
          v-model.trim="v$.formData.slug.$model"
          class="w-full p-3 border rounded-lg"
          :class="{ 'border-red-500': v$.formData.slug.$error }"
          placeholder="e.g. icsr-2024"
          @blur="v$.formData.slug.$touch()"
        />
        <small v-if="v$.formData.slug.$error" class="text-red-500 text-xs flex items-center gap-1 mt-1">
          <i class="pi pi-info-circle text-xs"></i>
          {{ v$.formData.slug.$errors[0]?.$message }}
        </small>
      </div>

      <div class="space-y-2">
        <label for="university" class="text-sm font-medium text-gray-700">University *</label>
        <Select
          id="university"
          v-model="v$.formData.university_id.$model"
          :options="universities"
          option-label="full_name"
          option-value="id"
          class="w-full"
          :class="{ 'border-red-500': v$.formData.university_id.$error }"
          placeholder="Select University"
          @blur="v$.formData.university_id.$touch()"
          :filter="true"
          filter-placeholder="Search universities..."
          show-clear
        >
          <template #option="slotProps">
            <div class="flex items-center gap-3">
              <span class="pi pi-building text-gray-500"></span>
              <span>{{ slotProps.option.full_name }}</span>
            </div>
          </template>
        </Select>
        <small v-if="v$.formData.university_id.$error" class="text-red-500 text-xs flex items-center gap-1 mt-1">
          <i class="pi pi-info-circle text-xs"></i>
          {{ v$.formData.university_id.$errors[0]?.$message }}
        </small>
      </div>
    </div>

    <!-- Description -->
    <div class="space-y-2">
      <label for="description" class="text-sm font-medium text-gray-700">Description</label>
      <Textarea
        id="description"
        v-model="formData.description"
        rows="3"
        class="w-full p-3 border rounded-lg resize-none"
        placeholder="Enter a brief description of the conference..."
      />
      <small class="text-gray-500 text-xs">Maximum 500 characters</small>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import type { University } from '@/types/university';

export default defineComponent({
  name: 'BasicInfoTab',
  props: {
    v$: {
      type: Object,
      required: true
    },
    formData: {
      type: Object,
      required: true
    },
    universities: {
      type: Array as PropType<University[]>,
      required: true
    }
  }
});
</script>