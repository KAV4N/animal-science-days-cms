<template>
  <div class="grid gap-4">
    <!-- Status Toggles -->
    <div class="grid md:grid-cols-2 gap-4">
      <div class="field-checkbox flex items-center">
        <Checkbox
          id="is_published"
          v-model="formData.is_published"
          :binary="true"
          class="mr-2"
        />
        <label for="is_published" class="cursor-pointer">Published</label>
      </div>

      <div class="field-checkbox flex items-center">
        <Checkbox
          id="is_latest"
          v-model="formData.is_latest"
          :binary="true"
          class="mr-2"
        />
        <label for="is_latest" class="cursor-pointer">Mark as Latest Conference</label>
      </div>
    </div>

    <!-- Information Cards -->
    <div class="mt-4">
      <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="pi pi-info-circle text-blue-500 mr-2"></i>
          </div>
          <div>
            <p class="text-sm text-blue-700">
              <strong>Published:</strong> Published conferences are visible to the public. Unpublished conferences are only visible to administrators.
            </p>
          </div>
        </div>
      </div>

      <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <i class="pi pi-exclamation-triangle text-yellow-500 mr-2"></i>
          </div>
          <div>
            <p class="text-sm text-yellow-700">
              <strong>Latest Conference:</strong> Only one conference can be marked as latest. Marking this conference as latest will remove the status from any other conference.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'SettingsTab',
  props: {
    formData: {
      type: Object,
      required: true
    }
  }
});
</script>