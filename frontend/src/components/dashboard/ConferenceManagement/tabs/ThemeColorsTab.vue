<template>
  <div class="flex flex-col items-center p-4 gap-4">
    <!-- Colors -->
    <div class="flex flex-wrap justify-center gap-4 w-full max-w-2xl">
      <!-- Primary Color -->
      <div class="flex-1 min-w-[280px] space-y-1">
        <label class="text-sm font-semibold text-gray-800">Primary Color *</label>
        <div class="relative flex justify-center">
          <TransparentColorPicker
            v-model="v$.formData.primary_color.$model"
            format="hex"
            @blur="v$.formData.primary_color.$touch()"
          >
            <template #input="slotProps">
              <div 
                class="p-inputtext w-full max-w-[280px] px-3 py-2 border border-gray-300 rounded-md flex items-center gap-2 transition-all"
                :class="{'ring-2 ring-red-500 border-red-500': v$.formData.primary_color.$error}"
              >
                <div 
                  class="w-5 h-5 rounded-md shadow-sm border border-gray-200"
                  :style="{ backgroundColor: v$.formData.primary_color.$model || '#f8fafc' }"
                />
                <input
                  ref="slotProps.inputRef"
                  type="text"
                  :value="slotProps.value"
                  class="flex-1 text-sm text-gray-800 bg-transparent border-none focus:ring-0 p-0"
                  placeholder="#FFFFFF"
                  v-bind="slotProps.inputProps"
                />
                <i class="pi pi-palette text-gray-400 hover:text-gray-600 transition-colors"/>
              </div>
            </template>
          </TransparentColorPicker>
        </div>
        <small v-if="v$.formData.primary_color.$error" class="text-red-600 text-xs flex items-center gap-1 justify-center">
          <i class="pi pi-info-circle"/>
          {{ v$.formData.primary_color.$errors[0]?.$message }}
        </small>
      </div>

      <!-- Secondary Color -->
      <div class="flex-1 min-w-[280px] space-y-1">
        <label class="text-sm font-semibold text-gray-800">Secondary Color *</label>
        <div class="relative flex justify-center">
          <TransparentColorPicker
            v-model="v$.formData.secondary_color.$model"
            format="hex"
            @blur="v$.formData.secondary_color.$touch()"
          >
            <template #input="slotProps">
              <div 
                class="p-inputtext w-full max-w-[280px] px-3 py-2 border border-gray-300 rounded-md flex items-center gap-2 transition-all"
                :class="{'ring-2 ring-red-500 border-red-500': v$.formData.secondary_color.$error}"
              >
                <div 
                  class="w-5 h-5 rounded-md shadow-sm border border-gray-200"
                  :style="{ backgroundColor: v$.formData.secondary_color.$model || '#f8fafc' }"
                />
                <input
                  ref="slotProps.inputRef"
                  type="text"
                  :value="slotProps.value"
                  class="flex-1 text-sm text-gray-800 bg-transparent border-none focus:ring-0 p-0"
                  placeholder="#FFFFFF"
                  v-bind="slotProps.inputProps"
                />
                <i class="pi pi-palette text-gray-400 hover:text-gray-600 transition-colors"/>
              </div>
            </template>
          </TransparentColorPicker>
        </div>
        <small v-if="v$.formData.secondary_color.$error" class="text-red-600 text-xs flex items-center gap-1 justify-center">
          <i class="pi pi-info-circle"/>
          {{ v$.formData.secondary_color.$errors[0]?.$message }}
        </small>
      </div>
    </div>

    <!-- Color Preview -->
    <div class="mt-2 w-full max-w-2xl p-4 border border-gray-200 rounded-xl bg-white">
      <h3 class="text-sm font-semibold text-gray-800 mb-3">Color Preview</h3>
      <div class="flex gap-3">
        <div 
          class="flex-1 h-14 rounded-lg flex items-center justify-center shadow-md transition-all"
          :style="{ backgroundColor: v$.formData.primary_color.$model || '#f8fafc' }"
        >
          <span class="text-white font-semibold text-sm drop-shadow">Primary</span>
        </div>
        <div 
          class="flex-1 h-14 rounded-lg flex items-center justify-center shadow-md transition-all"
          :style="{ backgroundColor: v$.formData.secondary_color.$model || '#f8fafc' }"
        >
          <span class="text-white font-semibold text-sm drop-shadow">Secondary</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import TransparentColorPicker from '../TransparentColorPicker.vue';

export default defineComponent({
  name: 'ThemeColorsTab',
  components: { TransparentColorPicker },
  props: {
    v$: { type: Object, required: true },
    formData: { type: Object, required: true }
  }
});
</script>

<style scoped>
.text-shadow {
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}
</style>