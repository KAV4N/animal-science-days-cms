<!-- components/dashboard/ConferenceFormTabs/LocationDatesTab.vue -->
<template>
  <div class="p-3 md:p-5">
    <div class="mb-6">
      <h3 class="text-xl font-semibold mb-3 ">Location Information</h3>
      <Divider />
      <div class="mt-4">
        <label for="location" class="block font-bold mb-2 ">Location</label>
        <InputText 
          id="location" 
          v-model.trim="conference.location" 
          required="true" 
          :class="{'p-invalid': submitted && !conference.location}" 
          class="w-full shadow-sm" 
          placeholder="City, Country" 
        />
        <small v-if="submitted && !conference.location" class="p-error">Location is required.</small>
        <small class=" italic block mt-1">Specify the city and country where the conference will be held.</small>
      </div>
      
      <div class="mt-4">
        <label for="venue" class="block font-bold mb-2 ">Venue Details (Optional)</label>
        <Textarea 
          id="venue" 
          v-model="conference.venueDetails" 
          rows="3" 
          autoResize 
          class="w-full shadow-sm" 
          placeholder="Conference hall, address, etc." 
        />
      </div>
    </div>

    <div>
      <h3 class="text-xl font-semibold mb-3 ">Event Schedule</h3>
      <Divider />
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
        <div>
          <label for="startDate" class="block font-bold mb-2 ">Start Date</label>
          <Datepicker 
            id="startDate" 
            v-model="conference.startDate" 
            dateFormat="mm/dd/yy" 
            :showIcon="true" 
            :class="{'p-invalid': submitted && !conference.startDate}" 
            class="w-full shadow-sm" 
            touchUI 
          />
          <small v-if="submitted && !conference.startDate" class="p-error">Start date is required.</small>
        </div>
        <div>
          <label for="endDate" class="block font-bold mb-2">End Date</label>
          <Datepicker 
            id="endDate" 
            v-model="conference.endDate" 
            dateFormat="mm/dd/yy" 
            :showIcon="true" 
            :class="{'p-invalid': submitted && !conference.endDate}" 
            class="w-full shadow-sm" 
            touchUI 
          />
          <small v-if="submitted && !conference.endDate" class="p-error">End date is required.</small>
          <small v-if="conference.startDate && conference.endDate && conference.startDate > conference.endDate" class="p-error block mt-1">
            End date must be after start date.
          </small>
        </div>
      </div>
      
      <div class="mt-5 p-4 rounded border ">
        <div class="flex items-center mb-2">
          <i class="pi pi-info-circle  mr-2"></i>
          <span class="font-medium">Date Summary</span>
        </div>
        <p  v-if="conference.startDate && conference.endDate">
          This conference will run for 
          <span class="font-bold">{{ calculateDuration(conference.startDate, conference.endDate) }} days</span>, 
          from <span class="font-medium">{{ formatDate(conference.startDate) }}</span> 
          to <span class="font-medium">{{ formatDate(conference.endDate) }}</span>.
        </p>
        <p v-else>
          Please select start and end dates to see conference duration.
        </p>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceManagement';

export default defineComponent({
  name: 'LocationDatesTab',
  data() {
    return {
      store: useConferenceStore()
    };
  },
  computed: {
    conference() {
      return this.store.currentConference || this.store.getEmptyConference();
    },
    submitted() {
      return this.store.submitted;
    }
  },
  methods: {
    formatDate(value: Date | null): string {
      if (value) {
        return new Date(value).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      }
      return '';
    },
    calculateDuration(startDate: Date | null, endDate: Date | null): number {
      if (!startDate || !endDate) return 0;
      
      // Calculate days between dates (inclusive of start and end days)
      const start = new Date(startDate);
      start.setHours(0, 0, 0, 0);
      
      const end = new Date(endDate);
      end.setHours(0, 0, 0, 0);
      
      const diffTime = Math.abs(end.getTime() - start.getTime());
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    }
  }
});
</script>