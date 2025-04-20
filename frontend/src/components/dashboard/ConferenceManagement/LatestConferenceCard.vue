<!-- components/dashboard/ConferenceManagement/LatestConferenceCard.vue -->
<template>
    <Card class="card rounded-md mb-4 latest-conference-card" v-if="latestConference">
      <template #header>
        <div class="flex justify-between items-center p-3 rounded-t-md bg-surface-800">
          <h3 class="m-0 text-primary-300 font-semibold flex items-center">
            <i class="pi pi-star-fill mr-2 text-yellow-500"></i>
            Latest Conference
          </h3>
          <Badge value="Featured" severity="info" />
        </div>
      </template>
      <template #content>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Conference info -->
          <div class="md:col-span-2">
            <div class="mb-3">
              <h4 class="m-0 text-xl font-bold truncate" :title="latestConference.name">{{ latestConference.name }}</h4>
              <p class="text-sm  truncate" :title="latestConference.title">{{ latestConference.title }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
              <div>
                <div class="flex items-center mb-2">
                  <i class="pi pi-calendar mr-2 text-blue-600"></i>
                  <div>
                    <div class="text-sm font-semibold">Start Date:</div>
                    <div>{{ formatDate(latestConference.startDate) }}</div>
                  </div>
                </div>
                
                <div class="flex items-center mb-2">
                  <i class="pi pi-calendar-times mr-2 text-blue-600"></i>
                  <div>
                    <div class="text-sm font-semibold">End Date:</div>
                    <div>{{ formatDate(latestConference.endDate) }}</div>
                  </div>
                </div>
              </div>
              
              <div>
                <div class="flex items-center mb-2">
                  <i class="pi pi-map-marker mr-2 text-blue-600"></i>
                  <div>
                    <div class="text-sm font-semibold">Location:</div>
                    <div class="truncate max-w-full" :title="latestConference.location">{{ latestConference.location }}</div>
                  </div>
                </div>
                
                <div class="flex items-center mb-2">
                  <i class="pi pi-building mr-2 text-blue-600"></i>
                  <div>
                    <div class="text-sm font-semibold">University:</div>
                    <div class="truncate max-w-full" :title="latestConference.university?.name">{{ latestConference.university?.name }}</div>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-if="latestConference.description" class="mb-3">
              <div class="text-sm font-semibold mb-1">Description:</div>
              <div class="description-container overflow-auto p-2  rounded" style="max-height: 100px;">
                <p class="m-0">{{ latestConference.description }}</p>
              </div>
            </div>
            
            <div class="flex items-center">
              <Tag :value="latestConference.isPublished ? 'Published' : 'Draft'" 
                   :severity="getStatusSeverity(latestConference.isPublished)" 
                   class="mr-2" />
              <div class="text-xs">Last updated: {{ formatDate(latestConference.updatedAt) }}</div>
            </div>
          </div>
          
          <!-- Theme colors and actions -->
          <div class="flex flex-col justify-between border-l-0 md:border-l pl-0 md:pl-4 pt-4 md:pt-0 border-gray-200">
            <div>
              <div class="text-sm font-semibold mb-2">Theme Colors:</div>
              <div class="flex space-x-2 mb-4">
                <div class="color-box" :style="{ backgroundColor: latestConference.primaryColor }"></div>
                <div class="color-box" :style="{ backgroundColor: latestConference.secondaryColor }"></div>
              </div>
              
              <div v-if="latestConference.editors.length" class="mb-4">
                <div class="text-sm font-semibold mb-2">Editors:</div>
                <div class="editors-container overflow-auto p-2 rounded" style="max-height: 80px;">
                  <div class="flex flex-wrap gap-1">
                    <Chip v-for="editor in latestConference.editors" 
                          :key="editor.id" 
                          :label="editor.name" 
                          class="text-xs text-primary" />
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex flex-col gap-2 mt-4">
              <Button label="Edit Pages" icon="pi pi-pencil" class="p-button-sm" @click="onEditPagesClick" />
              <Button label="Manage" icon="pi pi-cog" class="p-button-sm" @click="editConference" />
              <Button label="Remove" icon="pi pi-trash" severity="danger" class="p-button-sm" @click="confirmDeleteConference" />
            </div>
          </div>
        </div>
      </template>
    </Card>
  </template>
  
  <script>
  import { defineComponent } from 'vue';
  import { useConferenceStore } from '@/stores/conferenceManagement';
  import { useToast } from 'primevue/usetoast';
  
  export default defineComponent({
    name: 'LatestConferenceCard',
    data() {
      return {
        store: useConferenceStore(),
        toast: useToast()
      };
    },
    computed: {
      latestConference() {
        return this.store.conferences.find(conf => conf.isLatest) || null;
      }
    },
    methods: {
      formatDate(value) {
        if (value) {
          return new Date(value).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
          });
        }
        return '';
      },
      getStatusSeverity(isPublished) {
        return isPublished ? 'success' : 'warning';
      },
      editConference() {
        if (this.latestConference) {
          this.store.editConference(this.latestConference);
        }
      },
      confirmDeleteConference() {
        if (this.latestConference) {
          this.store.confirmDeleteConference(this.latestConference);
        }
      },
      onEditPagesClick() {
        this.toast.add({
          severity: 'info',
          summary: 'Info',
          detail: `Conference pages editor for "${this.latestConference.name}" will be implemented in future releases.`,
          life: 3000
        });
      }
    }
  });
  </script>
  
  <style scoped>
  .latest-conference-card {
    border-left: 4px solid #3B82F6;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  }
  
  .color-box {
    width: 36px;
    height: 36px;
    border-radius: 4px;
  }
  
  .truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .description-container, .editors-container {
    transition: all 0.2s ease;
  }
  
  .description-container:hover, .editors-container:hover {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  }
  
  .overflow-auto::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }
  
  .overflow-auto::-webkit-scrollbar-track {
    border-radius: 4px;
  }
  
  .overflow-auto::-webkit-scrollbar-thumb {
    border-radius: 4px;
  }
  
  </style>