<!-- views/ConferenceManagement.vue -->
<template>
  <div>
    <ConferenceTable />
    
    <ConferenceForm />
    
    <ConfirmationDialog
      v-model:visible="store.deleteConferenceDialog"
      :message="'Are you sure you want to delete <b>' + (store.currentConference?.name || '') + '</b>?'"
      @confirm="deleteConference"
    />
    
    <ConfirmationDialog
      v-model:visible="store.deleteConferencesDialog"
      message="Are you sure you want to delete the selected conferences?"
      @confirm="deleteSelectedConferences"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceManagement';
import { useToast } from 'primevue/usetoast';

import ConferenceTable from '@/components/dashboard/ConferenceManagement/ConferenceTable.vue';
import ConferenceForm from '@/components/dashboard/ConferenceManagement/ConferenceForm.vue';
import ConfirmationDialog from '@/components/dashboard/ConferenceManagement/ConfirmationDialog.vue';

export default defineComponent({
  name: 'ConferenceManagement',
  components: {
    ConferenceTable,
    ConferenceForm,
    ConfirmationDialog
  },
  data() {
    return {
      store: useConferenceStore(),
      toast: useToast()
    };
  },
  methods: {
    deleteConference() {
      if (this.store.currentConference?.id) {
        this.store.deleteConference(this.store.currentConference.id);
        this.toast.add({ severity: 'success', summary: 'Successful', detail: 'Conference Deleted', life: 3000 });
      }
    },
    
    deleteSelectedConferences() {
      this.store.deleteSelectedConferences();
      this.toast.add({ severity: 'success', summary: 'Successful', detail: 'Conferences Deleted', life: 3000 });
    }
  }
});
</script>