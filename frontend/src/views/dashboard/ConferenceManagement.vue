<!-- components/dashboard/ConferenceManagement.vue -->
<template>
  <div>
    <Toast />
    <LatestConferenceCard 
      v-if="authStore.hasAdminAccess" 
      @edit-conference="openConferenceDialog" 
    />
    <ConferenceTable 
      ref="conferenceTable"
      @edit-conference="openConferenceDialog" 
    />
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
import { useAuthStore } from '@/stores/authStore';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';
import ConferenceTable from '@/components/dashboard/ConferenceManagement/ConferenceTable.vue';
import ConferenceDialog from '@/components/dashboard/ConferenceManagement/ConferenceDialog.vue';
import LatestConferenceCard from '@/components/dashboard/ConferenceManagement/LatestConferenceCard.vue';
import type { Conference } from '@/types/conference';

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
      // Only load latest conference if user has admin access
      // ConferenceTable will handle its own data loading with proper filtering
      if (this.authStore.hasAdminAccess) {
        this.loadLatestConference();
      }
    },

    async loadLatestConference() {
      try {
        await this.conferenceStore.fetchLatestConference();
      } catch (error: any) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.data?.message || error.message || 'Failed to load latest conference',
          life: 3000
        });
      }
    },

    openConferenceDialog(conference?: Conference) {
      (this.$refs.conferenceDialog as any).openDialog(conference);
    },

    onConferenceUpdated(conference: Conference) {
      this.refreshConferenceTable();
      if (this.authStore.hasAdminAccess) {
        this.loadLatestConference();
      }
    },

    onConferenceCreated(conference: Conference) {
      this.refreshConferenceTable();
      
      if (this.authStore.hasAdminAccess) {
        this.loadLatestConference();
      }
    },

    refreshConferenceTable() {
      const conferenceTable = this.$refs.conferenceTable as any;
      if (conferenceTable && conferenceTable.resetPaginationAndReload) {
        conferenceTable.resetPaginationAndReload();
      }
    }
  }
});
</script>