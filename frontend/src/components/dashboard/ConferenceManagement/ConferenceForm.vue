<!-- components/dashboard/ConferenceForm.vue -->
<template>
  <Dialog 
    v-model:visible="visible" 
    maximizable modal
    :style="{ width: '90vw', maxWidth: '800px' }" 
    header="Conference Details" 

    class="p-fluid" 
    :contentStyle="{ 'max-height': '80vh', 'overflow': 'auto' }"
    @hide="onHide"
  >
    <Tabs v-model:value="activeTabIndex">
      <TabList>
        <Tab value="0">Basic Information</Tab>
        <Tab value="1">Location & Dates</Tab>
        <Tab value="2">Theme & Colors</Tab>
        <Tab value="3">Editors</Tab>
        <Tab value="4">Settings</Tab>
      </TabList>
      <TabPanels>
        <!-- Basic Information Tab 
        <TabPanel value="0">
          <BasicInfoTab 
            :current-conference="currentConference"
            :submitted="submitted"
            @update:current-conference="updateConference"
          />
        </TabPanel>

        <TabPanel value="1">
          <LocationDatesTab 
            :current-conference="currentConference"
            :submitted="submitted"
            @update:current-conference="updateConference"
          />
        </TabPanel>


        <TabPanel value="2">
          <ThemeColorsTab 
            :current-conference="currentConference"
            :submitted="submitted"
            @update:current-conference="updateConference"
          />
        </TabPanel>
        

        <TabPanel value="3">
          <EditorsTab 
            :current-conference="currentConference"
            :submitted="submitted"
            @update:current-conference="updateConference"
          />
        </TabPanel>
        

        <TabPanel value="4">
          <SettingsTab
            :current-conference="currentConference"
            @delete="deleteConferenceDialog = true"
            @manage-pages="onManagePages"
            @configure-registration="onConfigureRegistration"
          />
        </TabPanel>
      -->
      </TabPanels>
    </Tabs>

    <template #footer>
      <div class="flex flex-wrap justify-between gap-2">
        <Button label="Cancel" icon="pi pi-times" text @click="hideConferenceDialog" class="w-full sm:w-auto order-2 sm:order-1" />
        <Button label="Save" icon="pi pi-check" @click="saveConference" class="w-full sm:w-auto order-1 sm:order-2" />
      </div>
    </template>
  </Dialog>
  
  <ConfirmationDialog
    v-model:visible="deleteConferenceDialog"
    :message="'Are you sure you want to delete <b>' + (currentConference?.name || '') + '</b>?'"
    @confirm="confirmDeleteConference"
  />
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';
import { useToast } from 'primevue/usetoast';

// Import the Tabs components
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
/*
import BasicInfoTab from '@/components/dashboard/ConferenceManagement/FormTabs/BasicInfoTab.vue';
import LocationDatesTab from '@/components/dashboard/ConferenceManagement/FormTabs/LocationDatesTab.vue';
import ThemeColorsTab from '@/components/dashboard/ConferenceManagement/FormTabs/ThemeColorsTab.vue';
import EditorsTab from '@/components/dashboard/ConferenceManagement/FormTabs/EditorsTab.vue';
import SettingsTab from '@/components/dashboard/ConferenceManagement/FormTabs/SettingsTab.vue';
*/
import ConfirmationDialog from '@/components/dashboard/ConferenceManagement/ConfirmationDialog.vue';

// Define conference interface
interface Conference {
  id?: string;
  name: string;
  // Add other conference properties here
  [key: string]: any;
}

export default defineComponent({
  name: 'ConferenceForm',
  components: {
    Tabs,
    TabList,
    Tab,
    TabPanels,
    TabPanel,
    /*
    BasicInfoTab,
    LocationDatesTab,
    ThemeColorsTab,
    EditorsTab,
    SettingsTab,
    */
    ConfirmationDialog
  },
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    conferenceData: {
      type: Object as PropType<Conference | null>,
      default: null
    }
  },
  emits: ['update:visible', 'save', 'delete'],
  data() {
    return {
      conferenceDialog: this.visible,
      activeTabIndex: '0',
      currentConference: this.conferenceData ? { ...this.conferenceData } : null,
      deleteConferenceDialog: false,
      submitted: false,
      toast: useToast()
    };
  },
  watch: {
    visible(newValue) {
      this.conferenceDialog = newValue;
    },
    conferenceDialog(newValue) {
      this.$emit('update:visible', newValue);
    },
    conferenceData(newValue) {
      if (newValue) {
        this.currentConference = { ...newValue };
      } else {
        this.currentConference = null;
      }
    }
  },
  methods: {
    onHide() {
      this.submitted = false;
    },
    
    hideConferenceDialog() {
      this.conferenceDialog = false;
    },
    
    updateConference(updatedData: Partial<Conference>) {
      if (this.currentConference) {
        this.currentConference = { ...this.currentConference, ...updatedData };
      } else {
        this.currentConference = { name: '', ...updatedData };
      }
    },
    
    validateConference(): boolean {
      // Implement validation logic here
      this.submitted = true;
      
      // Check if name is provided (as a basic validation example)
      if (!this.currentConference?.name) {
        return false;
      }
      
      return true;
    },
    
    saveConference() {
      if (this.validateConference()) {
        this.$emit('save', this.currentConference);
        this.toast.add({
          severity: 'success', 
          summary: 'Successful', 
          detail: this.currentConference?.id 
                  ? 'Conference Updated' 
                  : 'Conference Created', 
          life: 3000
        });
        this.hideConferenceDialog();
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
      if (this.currentConference?.id) {
        this.$emit('delete', this.currentConference.id);
        this.toast.add({ 
          severity: 'success', 
          summary: 'Successful', 
          detail: 'Conference Deleted', 
          life: 3000 
        });
        this.hideConferenceDialog();
        this.deleteConferenceDialog = false;
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