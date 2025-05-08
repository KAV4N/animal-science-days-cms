<template>
  <div class="editors-tab">
    <h3 class="text-lg font-semibold mb-4">Manage Conference Editors</h3>
    
    <div class="mb-4">
      <span class="p-float-label">
        <MultiSelect
          v-model="selectedEditors"
          :options="availableUsers"
          option-label="name"
          placeholder="Select Editors"
          class="w-full"
          :loading="userStore.loading"
          :filter="true"
          :virtual-scroller-options="{ itemSize: 38 }"
          @filter="onFilterUsers"
        >
          <template #option="slotProps">
            <div class="flex align-items-center">
              <Avatar
                :image="slotProps.option.avatar"
                shape="circle"
                class="mr-2"
                size="small"
                :alt="slotProps.option.name"
              />
              <div>
                <div>{{ slotProps.option.name }}</div>
                <small>{{ slotProps.option.email }}</small>
              </div>
            </div>
          </template>
          <template #value="slotProps">
            <div class="flex align-items-center">
              <Avatar
                v-if="slotProps.value.avatar"
                :image="slotProps.value.avatar"
                shape="circle"
                class="mr-2"
                size="small"
                :alt="slotProps.value.name"
              />
              <div>{{ slotProps.value.name }}</div>
            </div>
          </template>
        </MultiSelect>
        <label for="editors">Conference Editors</label>
      </span>
    </div>

    <div class="flex flex-col gap-2 mt-4">
      <div v-if="selectedEditors.length === 0" class="text-gray-500 italic">
        No editors have been assigned to this conference yet.
      </div>
      
      <DataTable
        v-else
        :value="selectedEditors"
        data-key="id"
        class="border-1 border-gray-200 dark:border-gray-700 rounded-md"
        responsive-layout="stack"
      >
        <Column field="name" header="Name">
          <template #body="slotProps">
            <div class="flex items-center">
              <Avatar
                :image="slotProps.data.avatar"
                shape="circle"
                class="mr-2"
                size="small"
                :alt="slotProps.data.name"
              />
              <span>{{ slotProps.data.name }}</span>
            </div>
          </template>
        </Column>
        <Column field="email" header="Email" />
        <Column header-style="width: 6rem">
          <template #body="slotProps">
            <Button
              icon="pi pi-trash"
              text
              rounded
              severity="danger"
              aria-label="Remove"
              @click="removeEditor(slotProps.data)"
            />
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useUserStore } from '@/stores/userStore';
import type { User } from '@/types/user';

export default defineComponent({
  name: 'EditorsTab',
  props: {
    formData: {
      type: Object,
      required: true
    }
  },
  data() {
    const userStore = useUserStore();
    return {
      searchQuery: '',
      availableUsers: [] as User[],
      userStore
    };
  },
  computed: {
    selectedEditors: {
      get(): User[] {
        return (this.formData as any).editors || [];
      },
      set(value: User[]) {
        (this.formData as any).editors = value;
      }
    }
  },
  created() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      await this.userStore.fetchUsers();
      this.availableUsers = this.userStore.users;
    },
    async onFilterUsers(event: any) {
      this.searchQuery = event.filter;
      await this.userStore.searchUsers(this.searchQuery);
      this.availableUsers = this.userStore.users;
    },
    removeEditor(editor: User) {
      this.selectedEditors = this.selectedEditors.filter((e: User) => e.id !== editor.id);
    }
  }
});
</script>