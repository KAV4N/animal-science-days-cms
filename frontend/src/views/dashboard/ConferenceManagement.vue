<!-- components/dashboard/ConferenceManagement.vue -->
<template>
  <div>
    <!-- 
    TODO: Add LATEST conference. The user selects one conference to be latest and vissible on the main home page.
      When the latest conference is delete, set the latest conference to be always the latest date/to be up to date.
      New field in setting set latest, new field in database is_latest.
  
    <LatestConferenceCard />

    <ConferenceToolbar 
      :selectedConferences="selectedConferences" 
      @new-conference="openNewConference" 
      @confirm-delete-selected="confirmDeleteSelected" 
    />
      <ConferenceForm />
          <ConfirmationDialog
      v-model:visible="deleteConferenceDialog"
      :message="'Are you sure you want to delete <b>' + (currentConference?.name || '') + '</b>?'"
      @confirm="deleteConference"
    />
    
    <ConfirmationDialog
      v-model:visible="deleteConferencesDialog"
      message="Are you sure you want to delete the selected conferences?"
      @confirm="deleteSelectedConferences"
    />
  -->
    <Toast />
    <LatestConferenceCard />
    <ConferenceTable/>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore'; // Correct import
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';
import ConferenceTable from '@/components/dashboard/ConferenceManagement/ConferenceTable.vue';
import LatestConferenceCard from '@/components/dashboard/ConferenceManagement/LatestConferenceCard.vue';

export default defineComponent({
  name: 'ConferenceManagement',
  components: {
    ConferenceTable,
    LatestConferenceCard,
    Toast
  },
  data() {
    return {
      conferenceStore: useConferenceStore(),
      toast: useToast()
    };
  },
  mounted() {
    this.loadData();
  },
  methods: {
    loadData() {
      this.loadConference();
      this.loadLatestConference();
    },
    async loadConference(){
      try {
        await this.conferenceStore.fetchConferences();
      } catch (error) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to load conferences',
          life: 3000
        });
      }
    },

    async loadLatestConference(){
      try {
        await this.conferenceStore.fetchLatestConference();
      } catch (error) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to load latest conference',
          life: 3000
        });
      }
    }
    

  }
});
</script>


