<template>
  <div class="grid gap-4">
    <!-- Colors -->
    <div class="grid md:grid-cols-2 gap-4">
      <div class="field">
        <label for="primary_color" class="font-medium">Primary Color *</label>
        <TransparentColorPicker
          v-model="v$.formData.primary_color"

        />
        <small v-if="v$.formData.primary_color.$error" class="text-red-500">
          {{ v$.formData.primary_color.$errors[0]?.$message }}
        </small>
      </div>

      <div class="field">
        <label for="secondary_color" class="font-medium">Secondary Color *</label>
        <ColorPicker
          v-model="v$.formData.secondary_color.$model"
          class="w-full"
          :class="{ 'p-invalid': v$.formData.secondary_color.$error }"
          format="hex"
          @blur="v$.formData.secondary_color.$touch()"
        />
        <small v-if="v$.formData.secondary_color.$error" class="text-red-500">
          {{ v$.formData.secondary_color.$errors[0]?.$message }}
        </small>
      </div>
    </div>

    <!-- Color Preview -->
    <div class="mt-4">
      <h3 class="text-lg font-medium mb-2">Color Preview</h3>
      <div class="flex items-center gap-4">
        <div class="flex flex-col items-center">
          <div 
            class="w-16 h-16 rounded-full border border-gray-300" 
            :style="{ backgroundColor: formData.primary_color }"
          ></div>
          <span class="mt-2 text-sm">Primary</span>
        </div>
        <div class="flex flex-col items-center">
          <div 
            class="w-16 h-16 rounded-full border border-gray-300" 
            :style="{ backgroundColor: formData.secondary_color }"
          ></div>
          <span class="mt-2 text-sm">Secondary</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { required, helpers } from '@vuelidate/validators';

export default defineComponent({
  name: 'ThemeColorsTab',
  props: {
    formData: {
      type: Object,
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
        }
      }
    };
  }
});
</script>