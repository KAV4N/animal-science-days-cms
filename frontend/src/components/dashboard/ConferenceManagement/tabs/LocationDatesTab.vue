<template>
  <div class="grid gap-4">
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
        <small v-if="v$.formData.start_date.$error" class="text-red-500">
          {{ v$.formData.start_date.$errors[0]?.$message }}
        </small>
      </div>

      <div class="field">
        <label for="end_date" class="font-medium">End Date *</label>
        <Calendar
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
        <small v-if="v$.formData.end_date.$error" class="text-red-500">
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
        <small v-if="v$.formData.location.$error" class="text-red-500">
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
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { required, helpers } from '@vuelidate/validators';

export default defineComponent({
  name: 'LocationDatesTab',
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
        }
      }
    };
  }
});
</script>