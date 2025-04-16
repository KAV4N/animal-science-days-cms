<!-- components/dashboard/ConferenceFormTabs/EditorsTab.vue -->
<template>
  <div class="p-3 md:p-5">
    <div class="mb-6">
      <div class="flex flex-column md:flex-row md:justify-between md:items-center gap-3 mb-3">
        <h3 class="text-xl font-semibold text-gray-800">Conference Editors</h3>
        <Button label="Add Editors" icon="pi pi-plus" severity="success" @click="store.openEditorSelector" class="w-full md:w-auto" />
      </div>
      <Divider />
      <p class="text-gray-600 mb-4">
        Editors have permission to modify content for this conference. 
        Admin and superadmin users automatically have edit access.
      </p>
      
      <DataTable 
        :value="conference.editors" 
        :paginator="true" 
        :rows="5" 
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown" 
        :rowsPerPageOptions="[5, 10, 25]" 
        :globalFilterFields="['name', 'email', 'role']"
        responsiveLayout="stack"
        breakpoint="960px"
        class="shadow-sm border border-gray-200 rounded-lg overflow-hidden"
      >
        <template #header>
          <div class="flex justify-between">
            <span class="p-input-icon-left w-full">
              <i class="pi pi-search" />
              <InputText v-model="editorsTableFilter" placeholder="Search editors..." class="w-full" />
            </span>
          </div>
        </template>
        <template #empty>
          <div class="text-center p-4">
            <i class="pi pi-users text-gray-300 text-5xl mb-3 block"></i>
            <p class="text-gray-500">No editors have been assigned to this conference.</p>
            <Button label="Add Editors" icon="pi pi-plus" severity="secondary" 
                    class="mt-3" outlined @click="store.openEditorSelector" />
          </div>
        </template>
        <Column>
          <template #body="slotProps">
            <Avatar 
              :label="getInitials(slotProps.data.name)" 
              :style="{ backgroundColor: getAvatarColor(slotProps.data.id) }" 
              shape="circle" 
            />
          </template>
        </Column>
        <Column field="name" header="Name" sortable>
          <template #body="slotProps">
            <span class="font-medium">{{ slotProps.data.name }}</span>
          </template>
        </Column>
        <Column field="email" header="Email" sortable></Column>
        <Column field="role" header="Role" sortable>
          <template #body="slotProps">
            <Tag :value="slotProps.data.role" severity="info" />
          </template>
        </Column>
        <Column header="Actions">
          <template #body="slotProps">
            <Button 
              icon="pi pi-trash" 
              outlined 
              rounded 
              severity="danger" 
              @click="store.removeEditor(slotProps.index)" 
              v-tooltip.top="'Remove Editor'" 
            />
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
  
  <EditorSelectorDialog />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';
import EditorSelectorDialog from '@/components/dashboard/ConferenceManager/EditorSelectorDialog.vue';

export default defineComponent({
  name: 'EditorsTab',
  components: {
    EditorSelectorDialog
  },
  data() {
    return {
      store: useConferenceStore(),
      editorsTableFilter: ''
    };
  },
  computed: {
    conference() {
      return this.store.currentConference || this.store.getEmptyConference();
    }
  },
  methods: {
    getInitials(name: string): string {
      return name.split(' ')
        .map(part => part.charAt(0))
        .join('')
        .toUpperCase();
    },
    getAvatarColor(id: string): string {
      const colors = [
        '#3B82F6', '#10B981', '#F59E0B', '#EF4444', 
        '#8B5CF6', '#EC4899', '#06B6D4', '#F97316'
      ];
      const index = id.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0) % colors.length;
      return colors[index];
    }
  }
});
</script>