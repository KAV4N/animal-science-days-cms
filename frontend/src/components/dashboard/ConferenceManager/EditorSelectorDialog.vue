<!-- components/dashboard/EditorSelectorDialog.vue -->
<template>
  <Dialog 
    v-model:visible="store.editorSelectorDialog" 
    :style="{ width: '90vw', maxWidth: '650px' }" 
    header="Add Editors" 
    :modal="true" 
    :contentStyle="{ 'max-height': '70vh', 'overflow': 'auto' }"
  >
    <div class="flex flex-col gap-4 p-3">
      <div class="bg-gray-50 p-4 rounded-lg mb-2 border-l-4 border-blue-500">
        <div class="flex items-start">
          <i class="pi pi-info-circle text-blue-500 mr-2 mt-1"></i>
          <p class="text-gray-700">
            Select editors to grant access to this conference. Only users with the 'editor' role can be added.
            Administrators automatically have edit access.
          </p>
        </div>
      </div>
      
      <DataTable 
        :value="filteredEditors" 
        v-model:selection="store.selectedEditorsForDialog" 
        selectionMode="multiple"
        dataKey="id" 
        :paginator="true" 
        :rows="5" 
        :globalFilterFields="['name', 'email', 'role']"
        responsiveLayout="stack"
        breakpoint="960px"
        class="shadow-sm border border-gray-200 rounded-lg overflow-hidden"
        emptyMessage="No additional editors available"
      >
        <template #header>
          <div class="flex justify-between">
            <span class="p-input-icon-left w-full">
              <i class="pi pi-search" />
              <InputText v-model="searchTerm" placeholder="Search editors..." class="w-full" />
            </span>
          </div>
        </template>
        <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
        <Column field="name" header="Name" sortable>
          <template #body="slotProps">
            <div class="flex items-center">
              <Avatar :label="getInitials(slotProps.data.name)" 
                     :style="{ backgroundColor: getAvatarColor(slotProps.data.id) }" 
                     shape="circle" class="mr-2" />
              <span>{{ slotProps.data.name }}</span>
            </div>
          </template>
        </Column>
        <Column field="email" header="Email" sortable></Column>
        <Column field="role" header="Role" sortable>
          <template #body="slotProps">
            <Tag :value="slotProps.data.role" severity="info" />
          </template>
        </Column>
      </DataTable>
    </div>
    
    <template #footer>
      <div class="flex flex-column sm:flex-row justify-between gap-3">
        <Button label="Cancel" icon="pi pi-times" text @click="store.closeEditorSelector" class="w-full sm:w-auto order-2 sm:order-1" />
        <div class="flex flex-column sm:flex-row items-center gap-3 order-1 sm:order-2">
          <span class="text-gray-700" v-if="store.selectedEditorsForDialog.length > 0">
            <b>{{ store.selectedEditorsForDialog.length }}</b> editor(s) selected
          </span>
          <Button 
            label="Add Selected" 
            icon="pi pi-check" 
            severity="success" 
            @click="store.addSelectedEditors"
            :disabled="store.selectedEditorsForDialog.length === 0" 
            class="w-full sm:w-auto" 
          />
        </div>
      </div>
    </template>
  </Dialog>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useConferenceStore } from '@/stores/conferenceStore';

export default defineComponent({
  name: 'EditorSelectorDialog',
  data() {
    return {
      store: useConferenceStore(),
      searchTerm: ''
    };
  },
  computed: {
    filteredEditors() {
      const term = this.searchTerm.toLowerCase();
      return this.store.availableEditors.filter(editor => 
        editor.name.toLowerCase().includes(term) || 
        editor.email.toLowerCase().includes(term) || 
        editor.role.toLowerCase().includes(term)
      );
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