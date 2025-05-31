<template>
  <div class="grid gap-6 p-4">
    <!-- Dates -->
    <div class="grid md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label for="start_date" class="text-sm font-medium text-gray-700">Start Date *</label>
        <DatePicker 
          id="start_date"
          v-model="v$.formData.start_date.$model"
          dateFormat="yy-mm-dd"
          class="w-full"
          :class="{ 'p-invalid': v$.formData.start_date.$error }"
          placeholder="Select Start Date"
          :manualInput="false"
          @blur="v$.formData.start_date.$touch()"
        >
          <template #input="slotProps">
            <div class="p-inputtext w-full p-3 border rounded-lg flex items-center" :class="{'border-red-500': v$.formData.start_date.$error}">
              <input
                ref="slotProps.inputRef"
                type="text"
                :value="slotProps.value"
                :placeholder="slotProps.placeholder"
                class="flex-grow border-none p-0 focus:outline-none focus:ring-0 bg-transparent"
                v-bind="slotProps.inputProps"
              />
              <i class="pi pi-calendar text-gray-500 ml-2"></i>
            </div>
          </template>
        </DatePicker>
        <small v-if="v$.formData.start_date.$error" class="text-red-500 text-xs flex items-center gap-1 mt-1">
          <i class="pi pi-info-circle text-xs"></i>
          {{ v$.formData.start_date.$errors[0]?.$message }}
        </small>
      </div>

      <div class="space-y-2">
        <label for="end_date" class="text-sm font-medium text-gray-700">End Date *</label>
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
        >
          <template #input="slotProps">
            <div class="p-inputtext w-full p-3 border rounded-lg flex items-center" :class="{'border-red-500': v$.formData.end_date.$error}">
              <input
                ref="slotProps.inputRef"
                type="text"
                :value="slotProps.value"
                :placeholder="slotProps.placeholder"
                class="flex-grow border-none p-0 focus:outline-none focus:ring-0 bg-transparent"
                v-bind="slotProps.inputProps"
              />
              <i class="pi pi-calendar text-gray-500 ml-2"></i>
            </div>
          </template>
        </DatePicker >
        <small v-if="v$.formData.end_date.$error" class="text-red-500 text-xs flex items-center gap-1 mt-1">
          <i class="pi pi-info-circle text-xs"></i>
          {{ v$.formData.end_date.$errors[0]?.$message }}
        </small>
      </div>
    </div>

    <!-- Location and Venue -->
    <div class="grid md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label for="location" class="text-sm font-medium text-gray-700">Location *</label>
        <InputText
          id="location"
          v-model.trim="v$.formData.location.$model"
          class="w-full p-3 border rounded-lg"
          :class="{ 'border-red-500': v$.formData.location.$error }"
          placeholder="e.g. New York, USA"
          @blur="v$.formData.location.$touch()"
        />
        <small v-if="v$.formData.location.$error" class="text-red-500 text-xs flex items-center gap-1 mt-1">
          <i class="pi pi-info-circle text-xs"></i>
          {{ v$.formData.location.$errors[0]?.$message }}
        </small>
      </div>

      <div class="space-y-2">
        <label for="venue_details" class="text-sm font-medium text-gray-700">Venue Details</label>
        <InputText
          id="venue_details"
          v-model="formData.venue_details"
          class="w-full p-3 border rounded-lg"
          placeholder="e.g. Conference Hall A, Building 5"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'LocationDatesTab',
  props: {
    v$: {
      type: Object,
      required: true
    },
    formData: {
      type: Object,
      required: true
    }
  }
});
</script>