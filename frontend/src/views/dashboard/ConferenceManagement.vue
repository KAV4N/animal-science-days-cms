<!-- components/dashboard/ConferenceManagement.vue -->
<template>
  <div>
    <Toast />
    <LatestConferenceCard 
      v-if="authStore.hasAdminAccess" 
      @edit-conference="openConferenceDialog" 
    />
    <ConferenceTable @edit-conference="openConferenceDialog" />
    <ConferenceDialog 
      ref="conferenceDialog" 
      @conference-updated="onConferenceUpdated" 
      @conference-created="onConferenceCreated" 
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';
import ConferenceTable from '@/components/dashboard/ConferenceManagement/ConferenceTable.vue';
import ConferenceDialog from '@/components/dashboard/ConferenceManagement/ConferenceDialog.vue';
import type { Conference } from '@/types/conference';
import LatestConferenceCard from '@/components/dashboard/ConferenceManagement/LatestConferenceCard.vue';
import { useAuthStore } from '@/stores/authStore';

export default defineComponent({
  name: 'ConferenceManagement',
  components: {
    LatestConferenceCard,
    ConferenceTable,
    ConferenceDialog,
    Toast
  },
  data() {
    return {
      conferenceStore: useConferenceStore(),
      authStore: useAuthStore(),
      toast: useToast()
    };
  },
  mounted() {
    this.loadData();
  },
  methods: {
    loadData() {
      this.loadConferences();
      if (this.authStore.hasAdminAccess) {
        this.loadLatestConference();
      }
    },

    async loadConferences() {
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
    async loadLatestConference() {
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
    },
    openConferenceDialog(conference?: Conference) {
      (this.$refs.conferenceDialog as any).openDialog(conference);
    },
    onConferenceUpdated(conference: Conference) {
      this.toast.add({
        severity: 'success',
        summary: 'Success',
        detail: `Conference "${conference.name}" updated successfully`,
        life: 3000
      });
      this.loadConferences();
      if (this.authStore.hasAdminAccess) {
        this.loadLatestConference();
      }
    },
    onConferenceCreated(conference: Conference) {
      this.toast.add({
        severity: 'success',
        summary: 'Success',
        detail: `Conference "${conference.name}" created successfully`,
        life: 3000
      });
      this.loadConferences();
      if (this.authStore.hasAdminAccess) {
        this.loadLatestConference();
      }
    }
  }
});
</script>