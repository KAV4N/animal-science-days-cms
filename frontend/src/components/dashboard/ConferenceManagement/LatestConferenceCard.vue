<template>
  <Card class="rounded-md mb-4 border-l-4 border-blue-500 shadow-lg">
    <template #header>
      <div class="flex flex-wrap justify-between items-center p-2 sm:p-3 rounded-t-md bg-surface-800 gap-2">
        <h3 class="m-0 text-primary-300 font-semibold flex items-center text-base sm:text-lg">
          <i class="pi pi-star-fill mr-2 text-yellow-500"></i>
          Latest Conference
        </h3>
        <div class="flex items-center gap-1 sm:gap-2">
          <Badge value="Featured" severity="info" v-if="conferenceStore.getLatestConference" class="hidden sm:inline-flex" />
          <Button 
            :icon="isExpanded ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"
            @click="isExpanded = !isExpanded"
            text 
            rounded 
            class="p-1"
          />
        </div>
      </div>
    </template>

    <template #content>
      <div v-if="conferenceStore.getLatestConference">
        <div v-if="!isExpanded" class="p-2 sm:p-3">
          <div class="flex flex-col sm:grid sm:grid-cols-3 gap-2 sm:gap-4">
            <div class="sm:col-span-2 space-y-1">
              <h4 class="m-0 text-base sm:text-lg font-bold truncate">
                {{ conferenceStore.getLatestConference.name }}
              </h4>
              <p class="text-xs sm:text-sm text-gray-600 truncate">
                {{ formatDate(conferenceStore.getLatestConference.start_date) }} - 
                {{ formatDate(conferenceStore.getLatestConference.end_date) }}
              </p>
              <p class="text-xs sm:text-sm text-gray-600 truncate">
                {{ conferenceStore.getLatestConference.location }}
              </p>
            </div>
            <div class="sm:col-span-1 flex items-center justify-start sm:justify-end">
              <Tag
                :value="conferenceStore.getLatestConference.is_published ? 'Published' : 'Draft'"
                :severity="getStatusSeverity(conferenceStore.getLatestConference.is_published)"
                class="text-xs sm:text-sm"
              />
            </div>
          </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4 p-2 sm:p-3">
          <div class="md:col-span-2 space-y-3">
            <div class="mb-3">
              <h4 class="m-0 text-lg sm:text-xl font-bold truncate">
                {{ conferenceStore.getLatestConference.name }}
              </h4>
              <p class="text-sm truncate">
                {{ conferenceStore.getLatestConference.title }}
              </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
              <div class="space-y-2">
                <div class="flex items-center">
                  <i class="pi pi-calendar mr-2 text-blue-600"></i>
                  <div>
                    <div class="text-sm font-semibold">Start Date:</div>
                    <div class="text-xs sm:text-sm">{{ formatDate(conferenceStore.getLatestConference.start_date) }}</div>
                  </div>
                </div>

                <div class="flex items-center">
                  <i class="pi pi-calendar-times mr-2 text-blue-600"></i>
                  <div>
                    <div class="text-sm font-semibold">End Date:</div>
                    <div class="text-xs sm:text-sm">{{ formatDate(conferenceStore.getLatestConference.end_date) }}</div>
                  </div>
                </div>
              </div>

              <div class="space-y-2">
                <div class="flex items-center">
                  <i class="pi pi-map-marker mr-2 text-blue-600"></i>
                  <div>
                    <div class="text-sm font-semibold">Location:</div>
                    <div class="truncate max-w-full text-xs sm:text-sm">
                      {{ conferenceStore.getLatestConference.location }}
                    </div>
                  </div>
                </div>

                <div class="flex items-center">
                  <i class="pi pi-building mr-2 text-blue-600"></i>
                  <div>
                    <div class="text-sm font-semibold">University:</div>
                    <div class="truncate max-w-full text-xs sm:text-sm">
                      {{ conferenceStore.getLatestConference.university?.full_name }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="conferenceStore.getLatestConference.description" class="mb-3">
              <div class="text-sm font-semibold mb-1">Description:</div>
              <div class="overflow-auto p-2 rounded max-h-[80px] sm:max-h-[100px] border border-surface-200">
                <p class="m-0 text-xs sm:text-sm">{{ conferenceStore.getLatestConference.description }}</p>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
              <Tag
                :value="conferenceStore.getLatestConference.is_published ? 'Published' : 'Draft'"
                :severity="getStatusSeverity(conferenceStore.getLatestConference.is_published)"
                class="text-xs sm:text-sm"
              />
              <div class="text-xs text-gray-500">
                Last updated: {{ formatDate(conferenceStore.getLatestConference.updated_at) }}
              </div>
            </div>
          </div>

          <div class="flex flex-col justify-between border-t pt-4 md:border-t-0 md:border-l md:pl-4 md:pt-0 border-gray-200 space-y-4">
            <div class="space-y-4">
              <div>
                <div class="text-sm font-semibold mb-2">Theme Colors:</div>
                <div class="flex gap-2">
                  <div 
                    class="w-8 h-8 sm:w-9 sm:h-9 rounded shadow-sm"
                    :style="{ backgroundColor: conferenceStore.getLatestConference.primary_color }"
                  />
                  <div 
                    class="w-8 h-8 sm:w-9 sm:h-9 rounded shadow-sm"
                    :style="{ backgroundColor: conferenceStore.getLatestConference.secondary_color }"
                  />
                </div>
              </div>

              <div v-if="conferenceEditors.length">
                <div class="text-sm font-semibold mb-2">Editors:</div>
                <div class="overflow-x-auto p-2 rounded border border-surface-200">
                  <div class="flex gap-1 flex-nowrap sm:flex-wrap">
                    <Chip
                      v-for="editor in conferenceEditors"
                      :key="editor.id"
                      :label="editor.name"
                      class="text-xs text-blue-700 shrink-0"
                    />
                  </div>
                </div>
              </div>
            </div>

            <div class="flex flex-col gap-2">
              <Button 
                label="Edit Pages" 
                icon="pi pi-pencil" 
                class="p-button-sm w-full sm:w-auto" 
                @click="onEditPagesClick" 
                v-tooltip.top="'Edit conference pages'"
              />
              
              <div v-if="authStore.hasAdminAccess" class="flex flex-col gap-2">
                <Divider class="m-0" />
                <Button 
                  label="Manage" 
                  icon="pi pi-cog" 
                  class="p-button-sm w-full sm:w-auto" 
                  @click="editConference" 
                  v-tooltip.top="'Edit conference settings'"
                />
                <Button 
                  label="Remove" 
                  icon="pi pi-trash" 
                  severity="danger" 
                  class="p-button-sm w-full sm:w-auto" 
                  @click="confirmDeleteConference" 
                  v-tooltip.top="'Delete conference'"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="p-4 text-center">
        <i class="pi pi-info-circle text-2xl sm:text-3xl text-blue-500 mb-2"></i>
        <p class="text-base sm:text-lg font-semibold">No Latest Conference Set</p>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">
          Please select a conference to mark as latest in the conference management settings.
        </p>
      </div>
    </template>
  </Card>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Badge from 'primevue/badge';
import Tag from 'primevue/tag';
import Chip from 'primevue/chip';
import Button from 'primevue/button';
import Divider from 'primevue/divider';
import Tooltip from 'primevue/tooltip';
import apiService from '@/services/apiService';
import type { Editor } from '@/types';

export default defineComponent({
  name: 'LatestConferenceCard',
  components: {
    Card,
    Badge,
    Tag,
    Chip,
    Button,
    Divider
  },
  directives: {
    tooltip: Tooltip
  },
  emits: ['edit-conference'],
  
  data() {
    return {
      isExpanded: false,
      conferenceStore: useConferenceStore(),
      authStore: useAuthStore(),
      toast: useToast(),
      conferenceEditors: [] as Editor[],
      loadingEditors: false
    };
  },

  watch: {
    'conferenceStore.getLatestConference': {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.loadConferenceEditors(newVal.id);
        }
      }
    }
  },
  
  methods: {
    formatDate(value: string): string {
      if (value) {
        return new Date(value).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      }
      return '';
    },
    
    getStatusSeverity(isPublished: boolean): string {
      return isPublished ? 'success' : 'warning';
    },
    
    async loadConferenceEditors(conferenceId: number): Promise<void> {
      if (!conferenceId) return;
      
      this.loadingEditors = true;
      try {
        const queryParams = new URLSearchParams();
        queryParams.append('per_page', '50'); // Load a reasonable number of editors
        
        const url = `/v1/conferences/${conferenceId}/editors?${queryParams.toString()}`;
        const response = await apiService.get(url);
        
        if (response.data.success) {
          this.conferenceEditors = response.data.payload;
        }
      } catch (error: any) {
        console.error('Failed to load conference editors:', error);
        // Silent error handling - we don't want to show an error toast for this background operation
      } finally {
        this.loadingEditors = false;
      }
    },
    
    async editConference() {
      const latestConference = this.conferenceStore.getLatestConference;
      if (!latestConference) return;
      
      // Emit event to open edit dialog
      this.$emit('edit-conference', latestConference);
    },
    
    async confirmDeleteConference() {
      const latestConference = this.conferenceStore.getLatestConference;
      if (!latestConference) return;
      
      if (confirm(`Are you sure you want to delete "${latestConference.name}"?`)) {
        await this.deleteConference(latestConference.id);
      }
    },
    
    async deleteConference(id: number): Promise<void> {
      try {
        await this.conferenceStore.deleteConference(id);
        this.toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Latest conference deleted successfully',
          life: 3000
        });
      } catch (error: any) {
        // Show the exact error message from the response if available
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: error.response?.message || (error instanceof Error ? error.message : 'Failed to delete conference'),
          life: 3000
        });
      }
    },
    
    onEditPagesClick() {
      const latestConference = this.conferenceStore.getLatestConference;
      if (!latestConference) return;
      
      // Navigate to the conference edit page
      this.$router.push({
        name: 'ConferenceEdit',
        params: { id: latestConference.id.toString() }
      });
    }
  }
});
</script>