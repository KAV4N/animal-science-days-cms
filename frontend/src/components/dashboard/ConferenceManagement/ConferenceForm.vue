<!-- components/dashboard/ConferenceForm.vue -->
<template>
  <Dialog 
    v-model:visible="store.conferenceDialog" 
    :style="{ width: '90vw', maxWidth: '800px' }" 
    header="Conference Details" 
    :modal="true" 
    class="p-fluid" 
    :contentStyle="{ 'max-height': '80vh', 'overflow': 'auto' }"
    @hide="onHide"
  >
    <Tabs v-model:value="store.activeTabIndex">
      <TabList>
        <Tab value="0">Basic Information</Tab>
        <Tab value="1">Location & Dates</Tab>
        <Tab value="2">Theme & Colors</Tab>
        <Tab value="3">Editors</Tab>
        <Tab value="4">Settings</Tab>
      </TabList>
      <TabPanels>
        <!-- Basic Information Tab -->
        <TabPanel value="0">
          <BasicInfoTab />
        </TabPanel>
        
        <!-- Location & Dates Tab -->
        <TabPanel value="1">
          <LocationDatesTab />
        </TabPanel>

        <!-- Theme & Colors Tab -->
        <TabPanel value="2">
          <ThemeColorsTab />
        </TabPanel>
        
        <!-- Editors Tab -->
        <TabPanel value="3">
          <EditorsTab />
        </TabPanel>
        
        <!-- Settings Tab -->
        <TabPanel value="4">
          <SettingsTab
            @delete="store.deleteConferenceDialog = true"
            @manage-pages="onManagePages"
            @configure-registration="onConfigureRegistration"
          />
        </TabPanel>
      </TabPanels>
    </Tabs>

    <template #footer>
      <div class="flex flex-wrap justify-between gap-2">
        <Button label="Cancel" icon="pi pi-times" text @click="store.hideConferenceDialog" class="w-full sm:w-auto order-2 sm:order-1" />
        <Button label="Save" icon="pi pi-check" @click="saveConference" class="w-full sm:w-auto order-1 sm:order-2" />
      </div>
    </template>
  </Dialog>
  
  <ConfirmationDialog
    v-model:visible="store.deleteConferenceDialog"
    :message="'Are you sure you want to delete <b>' + (store.currentConference?.name || '') + '</b>?'"
    @confirm="confirmDeleteConference"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceManagement';
import { useToast } from 'primevue/usetoast';

// Import the new Tabs components
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';

import BasicInfoTab from '@/components/dashboard/ConferenceManagement/FormTabs/BasicInfoTab.vue';
import LocationDatesTab from '@/components/dashboard/ConferenceManagement/FormTabs/LocationDatesTab.vue';
import ThemeColorsTab from '@/components/dashboard/ConferenceManagement/FormTabs/ThemeColorsTab.vue';
import EditorsTab from '@/components/dashboard/ConferenceManagement/FormTabs/EditorsTab.vue';
import SettingsTab from '@/components/dashboard/ConferenceManagement/FormTabs/SettingsTab.vue';
import ConfirmationDialog from '@/components/dashboard/ConferenceManagement/ConfirmationDialog.vue';

export default defineComponent({
  name: 'ConferenceForm',
  components: {
    Tabs,
    TabList,
    Tab,
    TabPanels,
    TabPanel,
    BasicInfoTab,
    LocationDatesTab,
    ThemeColorsTab,
    EditorsTab,
    SettingsTab,
    ConfirmationDialog
  },
  data() {
    return {
      store: useConferenceStore(),
      toast: useToast()
    };
  },
  methods: {
    onHide() {
      this.store.submitted = false;
    },
    
    saveConference() {
      if (this.store.saveConference()) {
        this.toast.add({
          severity: 'success', 
          summary: 'Successful', 
          detail: this.store.currentConference?.id 
                  ? 'Conference Updated' 
                  : 'Conference Created', 
          life: 3000
        });
      } else {
        this.toast.add({ 
          severity: 'error', 
          summary: 'Error', 
          detail: 'Please check all required fields', 
          life: 3000 
        });
      }
    },
    
    confirmDeleteConference() {
      if (this.store.currentConference?.id) {
        this.store.deleteConference(this.store.currentConference.id);
        this.toast.add({ severity: 'success', summary: 'Successful', detail: 'Conference Deleted', life: 3000 });
      }
    },
    
    onManagePages() {
      this.toast.add({
        severity: 'info',
        summary: 'Info',
        detail: 'Conference pages editor will be implemented in future releases.',
        life: 3000
      });
    },
    
    onConfigureRegistration() {
      this.toast.add({
        severity: 'info',
        summary: 'Info',
        detail: 'Registration configuration will be implemented in future releases.',
        life: 3000
      });
    }
  }
});
</script>