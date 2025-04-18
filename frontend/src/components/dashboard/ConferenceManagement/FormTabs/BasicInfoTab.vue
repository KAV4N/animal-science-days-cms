<!-- components/dashboard/ConferenceFormTabs/BasicInfoTab.vue -->
<template>
  <div class="p-3 md:p-5">
    <div class="mb-6">
      <h3 class="text-xl font-semibold mb-3">Conference Identity</h3>
      <Divider />
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div class="col-span-1 md:col-span-2">
          <label for="name" class="block font-bold mb-2 ">Conference Name</label>
          <InputText 
            id="name" 
            v-model.trim="conference.name" 
            required="true" 
            autofocus 
            :class="{'p-invalid': submitted && !conference.name}" 
            class="w-full shadow-sm" 
          />
          <small v-if="submitted && !conference.name" class="p-error">Conference name is required.</small>
        </div>
        
        <div>
          <label for="slug" class="block font-bold mb-2">Slug</label>
          <InputText 
            id="slug" 
            v-model.trim="conference.slug" 
            required="true" 
            :class="{'p-invalid': submitted && !conference.slug}" 
            class="w-full shadow-sm" 
          />
          <small v-if="submitted && !conference.slug" class="p-error">Slug is required.</small>
        </div>
        
        <div>
          <label for="title" class="block font-bold mb-2 ">Title</label>
          <InputText 
            id="title" 
            v-model.trim="conference.title" 
            required="true" 
            :class="{'p-invalid': submitted && !conference.title}" 
            class="w-full shadow-sm" 
          />
          <small v-if="submitted && !conference.title" class="p-error">Title is required.</small>
        </div>
      </div>
    </div>
    
    <div class="mb-6">
      <h3 class="text-xl font-semibold mb-3 ">Description</h3>
      <Divider />
      <div class="mt-4">
        <Textarea 
          id="description" 
          v-model="conference.description" 
          rows="5" 
          autoResize 
          class="w-full shadow-sm" 
          placeholder="Enter a detailed description of the conference..." 
        />
        <small class="italic">Provide a comprehensive description to help attendees understand the conference goals and topics.</small>
      </div>
    </div>

    <div>
      <h3 class="text-xl font-semibold mb-3 ">Hosting Institution</h3>
      <Divider />
      <div class="mt-4">
        <label for="university" class="block font-bold mb-2">Hosting University</label>
        <Select
          id="university" 
          v-model="conference.university" 
          :options="store.universities" 
          optionLabel="name" 
          placeholder="Select a University" 
          :class="{'p-invalid': submitted && !conference.university}"
          class="w-full shadow-sm"
        />
        <small v-if="submitted && !conference.university" class="p-error">University is required.</small>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceManagement';

export default defineComponent({
  name: 'BasicInfoTab',
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
  }
});
</script>