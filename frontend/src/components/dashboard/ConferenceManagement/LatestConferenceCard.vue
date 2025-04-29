<template>
  <Card class="card rounded-md mb-4 latest-conference-card">
    <template #header>
      <div class="flex justify-between items-center p-3 rounded-t-md bg-surface-800">
        <h3 class="m-0 text-primary-300 font-semibold flex items-center">
          <i class="pi pi-star-fill mr-2 text-yellow-500"></i>
          Latest Conference
        </h3>
        <Badge value="Featured" severity="info" v-if="store.getLatestConference" />
      </div>
    </template>

    <template #content>
      <div v-if="store.getLatestConference" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
          <div class="mb-3">
            <h4 class="m-0 text-xl font-bold truncate" :title="store.getLatestConference?.name">
              {{ store.getLatestConference?.name }}
            </h4>
            <p class="text-sm truncate" :title="store.getLatestConference?.title">
              {{ store.getLatestConference?.title }}
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
            <div>
              <div class="flex items-center mb-2">
                <i class="pi pi-calendar mr-2 text-blue-600"></i>
                <div>
                  <div class="text-sm font-semibold">Start Date:</div>
                  <div>{{ formatDate(store.getLatestConference?.start_date) }}</div>
                </div>
              </div>

              <div class="flex items-center mb-2">
                <i class="pi pi-calendar-times mr-2 text-blue-600"></i>
                <div>
                  <div class="text-sm font-semibold">End Date:</div>
                  <div>{{ formatDate(store.getLatestConference?.end_date) }}</div>
                </div>
              </div>
            </div>

            <div>
              <div class="flex items-center mb-2">
                <i class="pi pi-map-marker mr-2 text-blue-600"></i>
                <div>
                  <div class="text-sm font-semibold">Location:</div>
                  <div class="truncate max-w-full" :title="store.getLatestConference?.location">
                    {{ store.getLatestConference?.location }}
                  </div>
                </div>
              </div>

              <div class="flex items-center mb-2">
                <i class="pi pi-building mr-2 text-blue-600"></i>
                <div>
                  <div class="text-sm font-semibold">University:</div>
                  <div class="truncate max-w-full" :title="store.getLatestConference?.university?.name">
                    {{ store.getLatestConference?.university?.name }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="store.getLatestConference?.description" class="mb-3">
            <div class="text-sm font-semibold mb-1">Description:</div>
            <div class="description-container overflow-auto p-2 rounded" style="max-height: 100px;">
              <p class="m-0">{{ store.getLatestConference?.description }}</p>
            </div>
          </div>

          <div class="flex items-center">
            <Tag
              :value="store.getLatestConference?.is_published ? 'Published' : 'Draft'"
              :severity="getStatusSeverity(store.getLatestConference?.is_published)"
              class="mr-2"
            />
            <div class="text-xs">Last updated: {{ formatDate(store.getLatestConference?.updated_at) }}</div>
          </div>
        </div>

        <div class="flex flex-col justify-between border-l-0 md:border-l pl-0 md:pl-4 pt-4 md:pt-0 border-gray-200">
          <div>
            <div class="text-sm font-semibold mb-2">Theme Colors:</div>
            <div class="flex space-x-2 mb-4">
              <div class="color-box" :style="{ backgroundColor: store.getLatestConference?.primary_color }"></div>
              <div class="color-box" :style="{ backgroundColor: store.getLatestConference?.secondary_color }"></div>
            </div>

            <div v-if="store.getLatestConference?.editors?.length" class="mb-4">
              <div class="text-sm font-semibold mb-2">Editors:</div>
              <div class="editors-container overflow-auto p-2 rounded" style="max-height: 80px;">
                <div class="flex flex-wrap gap-1">
                  <Chip
                    v-for="editor in store.getLatestConference?.editors"
                    :key="editor.id"
                    :label="editor.name"
                    class="text-xs text-blue-700"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="flex flex-col gap-2 mt-4">
            <Button label="Edit Pages" icon="pi pi-pencil" class="p-button-sm" @click="onEditPagesClick" />
            <div v-if="authStore.hasAdminAccess">
              <Button label="Manage" icon="pi pi-cog" class="p-button-sm" @click="editConference" />
              <Button label="Remove" icon="pi pi-trash" severity="danger" class="p-button-sm" @click="confirmDeleteConference"  />
            </div>
           
          </div>
        </div>
      </div>

      <div v-else class="p-4 text-center">
        <i class="pi pi-info-circle text-3xl text-blue-500 mb-2"></i>
        <p class="text-lg font-semibold">No Latest Conference Set</p>
        <p class="text-sm text-gray-500">Please select a conference to mark as latest in the conference management settings.</p>
      </div>
    </template>
  </Card>
</template>

<script>
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Badge from 'primevue/badge';
import Tag from 'primevue/tag';
import Chip from 'primevue/chip';
import Button from 'primevue/button';
import { useAuthStore } from '@/stores/authStore';

export default defineComponent({
  name: 'LatestConferenceCard',
  components: {
    Card,
    Badge,
    Tag,
    Chip,
    Button
  },
  setup() {
    const store = useConferenceStore();
    const authStore = useAuthStore();
    const toast = useToast();
    return { store,authStore, toast };
  },
  async mounted() {
    try {
      await this.store.fetchLatestConference();
    } catch (error) {
      this.toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Failed to load latest conference',
        life: 3000
      });
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
      if (this.store.getLatestConference) {
        this.$router.push({
          name: 'conference-edit',
          params: { id: this.store.getLatestConference.id }
        });
      }
    },
    async confirmDeleteConference() {
      if (this.store.getLatestConference) {
        if (confirm(`Are you sure you want to delete "${this.store.getLatestConference.name}"?`)) {
          try {
            await this.store.deleteConference(this.store.getLatestConference.id);
            this.toast.add({
              severity: 'success',
              summary: 'Success',
              detail: 'Latest conference deleted successfully',
              life: 3000
            });
          } catch (error) {
            this.toast.add({
              severity: 'error',
              summary: 'Error',
              detail: 'Failed to delete latest conference',
              life: 3000
            });
          }
        }
      }
    },
    onEditPagesClick() {
      this.toast.add({
        severity: 'info',
        summary: 'Info',
        detail: `Conference pages editor for "${this.store.getLatestConference?.name}" will be implemented in future releases.`,
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
