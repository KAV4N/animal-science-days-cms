<template>
  <div class="conference-editor flex flex-row h-screen">
    <!-- Main Editor Area -->
    <div class="editor-main flex-1 p-4 border-r border-gray-300 flex flex-col h-full overflow-hidden">
      <h2 class="text-2xl font-bold mb-4">Page Editor</h2>
      
      <!-- Lock status message -->
      <div 
        v-if="pageMenuStore.lockStatus.isLocked" 
        class="bg-green-100 border border-green-300 text-green-700 px-4 py-2 rounded-md mb-4"
      >
        You have locked this conference for editing
      </div>
      
      <!-- Lock acquisition message -->
      <div 
        v-else-if="!pageMenuStore.loading" 
        class="bg-yellow-100 border border-yellow-300 text-yellow-700 px-4 py-2 rounded-md mb-4"
      >
        <p>This conference needs to be locked before editing</p>
        <Button 
          label="Lock Conference" 
          icon="pi pi-lock" 
          @click="acquireLock"
          class="p-button-warning p-button-sm mt-2"
        />
      </div>
      
      <!-- Loading state for overall editor -->
      <div v-if="pageMenuStore.loading && !loadingSelectedMenu" class="flex items-center justify-center py-4">
        <i class="pi pi-spin pi-spinner text-2xl"></i>
        <span class="ml-2">Loading...</span>
      </div>
      
      <!-- Error message -->
      <div 
        v-if="pageMenuStore.error" 
        class="bg-red-100 border border-red-300 text-red-700 px-4 py-2 rounded-md mb-4"
      >
        {{ pageMenuStore.error }}
      </div>

      <!-- Empty state when no menus exist -->
      <div v-if="!pageMenuStore.hasMenus && !pageMenuStore.loading" class="flex flex-col items-center justify-center h-64 bg-gray-100 rounded-lg p-4">
        <p class="text-gray-500 mb-4">No pages available</p>
        <Button 
          label="Create First Page" 
          icon="pi pi-plus" 
          @click="openAddMenuDialog"
          :disabled="!pageMenuStore.isLocked"
          class="p-button-sm"
        />
      </div>
      
      <!-- No menu selected message -->
      <div v-else-if="!pageMenuStore.selectedMenu && !pageMenuStore.loading" class="flex items-center justify-center h-64 bg-gray-100 rounded-lg">
        <p class="text-gray-500">Please select a page to start editing</p>
      </div>

      <!-- Loading state for selected menu only -->
      <div v-else-if="loadingSelectedMenu" class="flex items-center justify-center h-64 bg-gray-100 rounded-lg">
        <i class="pi pi-spin pi-spinner text-2xl"></i>
        <span class="ml-2">Loading page content...</span>
      </div>
      
      <!-- Editor for selected menu -->
      <div v-else-if="pageMenuStore.selectedMenu && !pageMenuStore.loading && !loadingSelectedMenu" class="selected-page-editor flex flex-col h-full overflow-hidden">
        <h3 class="text-xl font-bold mb-2">{{ pageMenuStore.selectedMenu.title }}</h3>
        
        <!-- Empty state for components -->
        <div v-if="!pageMenuStore.selectedMenu.page_data || pageMenuStore.selectedMenu.page_data.length === 0" class="flex flex-col items-center justify-center h-64 bg-gray-100 rounded-lg p-4">
          <p class="text-gray-500 mb-4">No components added to this page yet</p>
          <Button 
            label="Add Component" 
            icon="pi pi-plus" 
            @click="openAddComponentDialog"
            :disabled="!pageMenuStore.isLocked"
            class="p-button-sm"
          />
        </div>
        
        <!-- Component list for the selected menu - scrollable container -->
        <div v-else class="components-container overflow-y-auto flex-1">
          <TransitionGroup name="component-list" tag="div">
            <div 
              v-for="component in pageMenuStore.selectedMenu.page_data" 
              :key="component.id"
              class="component-item p-4 mb-4 border border-gray-300 rounded-lg bg-white"
            >
              <div class="component-header flex justify-between items-center">
                <div class="flex items-center">
                  <span class="font-bold">{{ component.component_type }}: {{ component.tag || 'Unnamed' }}</span>
                  <Badge v-if="component.is_published" value="Published" severity="success" class="ml-2" />
                  <Badge v-else value="Draft" severity="warning" class="ml-2" />
                </div>
                <div class="actions flex gap-2">
                  <Button 
                    icon="pi pi-arrow-up" 
                    severity="secondary" 
                    size="small"
                    :disabled="isFirstComponent(component.id) || !pageMenuStore.isLocked"
                    @click="moveComponentUp(component.id)"
                    class="p-button-sm p-button-outlined"
                  />
                  <Button 
                    icon="pi pi-arrow-down" 
                    severity="secondary" 
                    size="small"
                    :disabled="isLastComponent(component.id) || !pageMenuStore.isLocked"
                    @click="moveComponentDown(component.id)"
                    class="p-button-sm p-button-outlined"
                  />
                  <Button 
                    icon="pi pi-pencil" 
                    severity="secondary" 
                    size="small"
                    :disabled="!pageMenuStore.isLocked"
                    @click="editComponent(component.id)"
                    class="p-button-sm p-button-outlined"
                  />
                  <Button 
                    icon="pi pi-trash" 
                    severity="danger" 
                    size="small"
                    :disabled="!pageMenuStore.isLocked"
                    @click="confirmDeleteComponent(component.id)"
                    class="p-button-sm p-button-outlined"
                  />
                </div>
              </div>
            </div>
          </TransitionGroup>
        </div>
      </div>
    </div>
    
    <!-- Pages Sidebar -->
    <div class="pages-sidebar w-80 p-4 bg-gray-50 flex flex-col h-full overflow-hidden">
      <div class="pages-sidebar-header flex-none">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold">Pages</h3>
          <Button 
            icon="pi pi-plus" 
            severity="success" 
            size="small"
            @click="openAddMenuDialog"
            :disabled="!pageMenuStore.isLocked"
            class="p-button-sm"
            v-tooltip="'Add new page'"
          />
        </div>
        <div class="add-component-top mb-4">
          <Dropdown
            v-model="newComponentType"
            :options="availableComponentTypes"
            placeholder="Select component type"
            class="w-full"
            :disabled="!pageMenuStore.selectedMenu || !pageMenuStore.isLocked"
            @change="openAddComponentDialog"
          />
        </div>
      </div>
      <div class="pages-list-container flex-1 overflow-y-auto pr-1">
        <div v-if="pageMenuStore.loading && !loadingSelectedMenu" class="flex items-center justify-center h-32">
          <i class="pi pi-spin pi-spinner text-xl"></i>
        </div>
        <div v-else-if="pageMenuStore.menus.length === 0" class="flex items-center justify-center h-32 bg-gray-100 rounded-lg">
          <p class="text-gray-500">No pages yet</p>
        </div>
        <div v-else class="page-list">
          <TransitionGroup name="page-list" tag="div">
            <div 
              v-for="menu in pageMenuStore.menus" 
              :key="menu.id"
              :class="['page-item p-3 mb-2 border rounded-lg cursor-pointer', 
                pageMenuStore.selectedMenu?.id === menu.id ? 'bg-blue-100 border-blue-300' : 'bg-white border-gray-300']"
              @click="selectMenu(menu.id)"
            >
              <div class="flex justify-between items-center">
                <div class="flex items-center">
                  <span class="font-semibold">{{ menu.title }}</span>
                  <Badge v-if="menu.is_published" value="Published" severity="success" class="ml-2" />
                  <Badge v-else value="Draft" severity="warning" class="ml-2" />
                </div>
                <div class="page-actions flex gap-1">
                  <Button 
                    icon="pi pi-pencil" 
                    severity="secondary" 
                    size="small"
                    :disabled="!pageMenuStore.isLocked"
                    @click.stop="handleEditMenuTitle(menu.id)"
                    class="p-button-sm p-button-text"
                  />
                  <Button 
                    icon="pi pi-trash" 
                    severity="danger" 
                    size="small"
                    :disabled="!pageMenuStore.isLocked"
                    @click.stop="confirmDeleteMenu(menu.id)"
                    class="p-button-sm p-button-text"
                  />
                </div>
              </div>
              <div class="flex justify-between items-center mt-2">
                <div class="order-controls flex gap-1">
                  <Button 
                    icon="pi pi-arrow-up" 
                    severity="secondary" 
                    size="small"
                    :disabled="isFirstMenu(menu.id) || !pageMenuStore.isLocked"
                    @click.stop="moveMenuUp(menu.id)"
                    class="p-button-sm p-button-text p-button-rounded"
                  />
                  <Button 
                    icon="pi pi-arrow-down" 
                    severity="secondary" 
                    size="small"
                    :disabled="isLastMenu(menu.id) || !pageMenuStore.isLocked"
                    @click.stop="moveMenuDown(menu.id)"
                    class="p-button-sm p-button-text p-button-rounded"
                  />
                </div>
                <small class="text-gray-500">Order: {{ menu.order }}</small>
              </div>
            </div>
          </TransitionGroup>
        </div>
      </div>
    </div>
    
    <!-- Add Menu Dialog -->
    <Dialog v-model:visible="showAddMenuDialog" header="Add New Page" :style="{ width: '30rem' }" :modal="true">
      <div class="p-fluid">
        <div class="field mb-3">
          <label for="newMenuTitle" class="block mb-1">Page Title</label>
          <InputText id="newMenuTitle" v-model="newMenuTitle" autofocus class="w-full" />
          <small v-if="titleError" class="text-red-500">{{ titleError }}</small>
        </div>
        <div class="field">
          <label for="newMenuSlug" class="block mb-1">Page Slug</label>
          <InputText id="newMenuSlug" v-model="newMenuSlug" class="w-full" />
          <small class="text-gray-500">URL-friendly version of the title (auto-generated if left empty)</small>
          <small v-if="slugError" class="text-red-500 block mt-1">{{ slugError }}</small>
        </div>
        <div class="field mt-3">
          <div class="flex items-center">
            <Checkbox v-model="newMenuPublished" inputId="newMenuPublished" binary />
            <label for="newMenuPublished" class="ml-2">Publish immediately</label>
          </div>
        </div>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="cancelAddMenu" text />
        <Button label="Add" icon="pi pi-check" @click="handleAddMenu" autofocus />
      </template>
    </Dialog>
    
    <!-- Edit Menu Dialog -->
    <Dialog v-model:visible="showEditMenuDialog" header="Edit Page Title" :style="{ width: '30rem' }" :modal="true">
      <div class="p-fluid">
        <div class="field mb-3">
          <label for="editMenuTitle" class="block mb-1">Page Title</label>
          <InputText id="editMenuTitle" v-model="editMenuTitle" autofocus class="w-full" />
          <small v-if="titleError" class="text-red-500">{{ titleError }}</small>
        </div>
        <div class="field">
          <label for="editMenuSlug" class="block mb-1">Page Slug</label>
          <InputText id="editMenuSlug" v-model="editMenuSlug" class="w-full" />
          <small class="text-gray-500">URL-friendly version of the title</small>
          <small v-if="slugError" class="text-red-500 block mt-1">{{ slugError }}</small>
        </div>
        <div class="field mt-3">
          <div class="flex items-center">
            <Checkbox v-model="editMenuPublished" inputId="editMenuPublished" binary />
            <label for="editMenuPublished" class="ml-2">Published</label>
          </div>
        </div>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="cancelEditMenu" text />
        <Button label="Save" icon="pi pi-check" @click="saveEditMenu" autofocus />
      </template>
    </Dialog>
    
    <!-- Add Component Dialog -->
    <Dialog v-model:visible="showAddComponentDialog" header="Add Component" :style="{ width: '35rem' }" :modal="true">
      <div class="p-fluid">
        <div class="field mb-3">
          <label for="componentName" class="block mb-1">Component Name</label>
          <InputText id="componentName" v-model="newComponentName" class="w-full" />
        </div>
        <div class="field mb-3">
          <label for="componentType" class="block mb-1">Component Type</label>
          <Dropdown
            id="componentType"
            v-model="newComponentType"
            :options="availableComponentTypes"
            placeholder="Select component type"
            class="w-full"
          />
        </div>
        <div v-if="newComponentType === 'Editor'" class="field">
          <label for="initialContent" class="block mb-1">Initial Content</label>
          <Textarea
            id="initialContent"
            v-model="newComponentData.content"
            rows="5"
            class="w-full"
            placeholder="Enter initial content..."
          />
        </div>
        <div class="field mt-3">
          <div class="flex items-center">
            <Checkbox v-model="newComponentPublished" inputId="newComponentPublished" binary />
            <label for="newComponentPublished" class="ml-2">Publish immediately</label>
          </div>
        </div>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="cancelAddComponent" text />
        <Button label="Add" icon="pi pi-check" @click="handleAddComponent" autofocus />
      </template>
    </Dialog>
    
    <!-- Edit Component Dialog -->
    <Dialog v-model:visible="showEditComponentDialog" header="Edit Component" :style="{ width: '35rem' }" :modal="true">
      <div class="p-fluid">
        <div class="field mb-3">
          <label for="editComponentName" class="block mb-1">Component Name</label>
          <InputText id="editComponentName" v-model="editComponentName" class="w-full" />
        </div>
        <div v-if="editComponentType === 'Editor'" class="field">
          <label for="editContent" class="block mb-1">Content</label>
          <client-only>
            <editor
              v-model="editComponentData.content"
              :init="{
                height: 300,
                menubar: true,
                plugins: 'accordion advlist anchor autolink autoresize autosave charmap code codesample directionality fullscreen image insertdatetime link lists media nonbreaking pagebreak preview quickbars save searchreplace table template visualblocks visualchars wordcount emoticons help',
                toolbar:
                  'undo redo | formatselect | bold italic backcolor | \
                  alignleft aligncenter alignright alignjustify | \
                  bullist numlist outdent indent | removeformat | help',
              }"
            />
          </client-only>
        </div>
        <div class="field mt-3">
          <div class="flex items-center">
            <Checkbox v-model="editComponentPublished" inputId="editComponentPublished" binary />
            <label for="editComponentPublished" class="ml-2">Published</label>
          </div>
        </div>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="cancelEditComponent" text />
        <Button label="Save" icon="pi pi-check" @click="saveEditComponent" autofocus />
      </template>
    </Dialog>
    
    <!-- Confirm Delete Dialog -->
    <Dialog v-model:visible="showConfirmDeleteDialog" header="Confirm Delete" :style="{ width: '30rem' }" :modal="true">
      <div class="confirmation-content flex items-center gap-3">
        <i class="pi pi-exclamation-triangle" style="font-size: 2rem" />
        <span>{{ confirmDeleteMessage }}</span>
      </div>
      <template #footer>
        <Button label="No" icon="pi pi-times" @click="cancelDelete" text />
        <Button label="Yes" icon="pi pi-check" @click="confirmDelete" autofocus />
      </template>
    </Dialog>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { usePageMenuStore } from '@/stores/pageMenuStore';
import { useRoute } from 'vue-router';
import Editor from '@hugerte/hugerte-vue';
import apiService from '@/services/apiService';

export default defineComponent({
  name: 'ConferenceEditor',
  components: {
    'editor': Editor
  },
  data() {
    return {
      availableComponentTypes: ['Editor'],
      
      showAddMenuDialog: false,
      newMenuTitle: '',
      newMenuSlug: '',
      newMenuPublished: false,
      
      showEditMenuDialog: false,
      editMenuId: 0,
      editMenuTitle: '',
      editMenuSlug: '',
      editMenuPublished: false,
      
      showAddComponentDialog: false,
      newComponentType: '',
      newComponentName: '',
      newComponentData: {
        content: '<p>Enter content here...</p>'
      },
      newComponentPublished: false,
      
      showEditComponentDialog: false,
      editComponentId: 0,
      editComponentType: '',
      editComponentName: '',
      editComponentData: {} as any,
      editComponentPublished: false,
      
      titleError: '',
      slugError: '',
      
      showConfirmDeleteDialog: false,
      deleteType: '',
      deleteId: 0,
      confirmDeleteMessage: '',
      
      loadingSelectedMenu: false,
    };
  },
  computed: {
    pageMenuStore() {
      return usePageMenuStore();
    },
    conferenceId(): number {
      return Number(this.$route.params.id);
    },
  },
  created() {
    this.pageMenuStore.setConferenceId(this.conferenceId);
    this.fetchMenus();
  },
  mounted() {
    window.addEventListener('beforeunload', this.handleBeforeUnload);
  },
  beforeUnmount() {
    window.removeEventListener('beforeunload', this.handleBeforeUnload);
    if (this.pageMenuStore.isLocked) {
      this.pageMenuStore.releaseLock();
    }
  },
  methods: {
    async fetchMenus() {
      await this.pageMenuStore.fetchMenus();
      if (!this.pageMenuStore.isLocked) {
        await this.checkLock();
      }
      if (this.pageMenuStore.menus.length > 0 && !this.pageMenuStore.selectedMenu) {
        await this.selectMenu(this.pageMenuStore.menus[0].id);
      }
    },
    async checkLock() {
      try {
        const response = await apiService.get(`/v1/conferences/${this.conferenceId}/lock`);
        if (response.data.payload.is_locked) {
          const currentUser = await apiService.get('/v1/users/me');
          const userId = currentUser.data.payload.id;
          this.pageMenuStore.lockStatus = {
            isLocked: response.data.payload.lock_info.user_id === userId,
            lockInfo: response.data.payload.lock_info
          };
        }
      } catch (error) {
        console.error('Error checking lock:', error);
      }
    },
    async acquireLock() {
      await this.pageMenuStore.acquireLock();
    },
    handleBeforeUnload(event: BeforeUnloadEvent) {
      if (this.pageMenuStore.isLocked) {
        event.preventDefault();
        event.returnValue = 'You are currently editing this conference. Are you sure you want to leave?';
        return event.returnValue;
      }
    },
    async handleExit() {
      if (this.pageMenuStore.isLocked) {
        await this.pageMenuStore.releaseLock();
      }
      this.$router.push('/conferences');
    },
    async saveCurrentMenu() {
      if (!this.pageMenuStore.selectedMenu) return;
      try {
        const components = this.pageMenuStore.selectedMenu.page_data;
        if (components && components.length > 0) {
          for (const component of components) {
            await this.pageMenuStore.updatePageData(
              this.pageMenuStore.selectedMenu.id,
              component.id,
              {
                tag: component.tag,
                data: component.data,
                is_published: component.is_published
              }
            );
          }
        }
        await this.pageMenuStore.refreshLock();
      } catch (error) {
        console.error('Error saving page:', error);
      }
    },
    generateSlug(title: string): string {
      return title
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/--+/g, '-')
        .trim();
    },
    openAddMenuDialog() {
      this.newMenuTitle = '';
      this.newMenuSlug = '';
      this.newMenuPublished = false;
      this.titleError = '';
      this.slugError = '';
      this.showAddMenuDialog = true;
    },
    cancelAddMenu() {
      this.showAddMenuDialog = false;
    },
    async handleAddMenu() {
      this.titleError = '';
      this.slugError = '';
      if (!this.newMenuTitle.trim()) {
        this.titleError = 'Title is required';
        return;
      }
      const slug = this.newMenuSlug.trim() || this.generateSlug(this.newMenuTitle);
      try {
        const currentMenus = this.pageMenuStore.menus;
        const maxOrder = currentMenus.length > 0
          ? Math.max(...currentMenus.map(m => m.order))
          : -1;
        await this.pageMenuStore.createMenu({
          title: this.newMenuTitle.trim(),
          slug: slug,
          is_published: this.newMenuPublished,
          order: maxOrder + 1
        });
        this.showAddMenuDialog = false;
      } catch (error: any) {
        if (error.response && error.response.data && error.response.data.errors) {
          const errors = error.response.data.errors;
          if (errors.title) this.titleError = errors.title[0];
          if (errors.slug) this.slugError = errors.slug[0];
        }
      }
    },
    handleEditMenuTitle(menuId: number) {
      const menu = this.pageMenuStore.getMenuById(menuId);
      if (!menu) return;
      this.editMenuId = menuId;
      this.editMenuTitle = menu.title;
      this.editMenuSlug = menu.slug;
      this.editMenuPublished = menu.is_published;
      this.titleError = '';
      this.slugError = '';
      this.showEditMenuDialog = true;
    },
    cancelEditMenu() {
      this.showEditMenuDialog = false;
    },
    async saveEditMenu() {
      this.titleError = '';
      this.slugError = '';
      if (!this.editMenuTitle.trim()) {
        this.titleError = 'Title is required';
        return;
      }
      try {
        await this.pageMenuStore.updateMenu(this.editMenuId, {
          title: this.editMenuTitle.trim(),
          slug: this.editMenuSlug.trim(),
          is_published: this.editMenuPublished
        });
        this.showEditMenuDialog = false;
      } catch (error: any) {
        if (error.response && error.response.data && error.response.data.errors) {
          const errors = error.response.data.errors;
          if (errors.title) this.titleError = errors.title[0];
          if (errors.slug) this.slugError = errors.slug[0];
        }
      }
    },
    async selectMenu(menuId: number) {
      this.loadingSelectedMenu = true;
      try {
        await this.pageMenuStore.fetchMenu(menuId);
      } catch (error) {
        console.error('Error loading menu data:', error);
      } finally {
        this.loadingSelectedMenu = false;
      }
    },
    confirmDeleteMenu(menuId: number) {
      this.deleteType = 'menu';
      this.deleteId = menuId;
      this.confirmDeleteMessage = 'Are you sure you want to delete this page? This action cannot be undone.';
      this.showConfirmDeleteDialog = true;
    },
    openAddComponentDialog() {
      if (!this.pageMenuStore.selectedMenu) return;
      this.newComponentType = 'Editor';
      this.newComponentName = '';
      this.newComponentData = { content: '<p>Enter content here...</p>' };
      this.newComponentPublished = false;
      this.showAddComponentDialog = true;
    },
    cancelAddComponent() {
      this.showAddComponentDialog = false;
      this.newComponentType = '';
    },
    async handleAddComponent() {
      if (!this.pageMenuStore.selectedMenu || !this.newComponentType) return;
      try {
        const currentComponents = this.pageMenuStore.selectedMenu.page_data || [];
        const maxOrder = currentComponents.length > 0
          ? Math.max(...currentComponents.map(c => c.order))
          : -1;
        await this.pageMenuStore.createPageData(
          this.pageMenuStore.selectedMenu.id,
          {
            component_type: this.newComponentType,
            order: maxOrder + 1,
            tag: this.newComponentName,
            data: { content: this.newComponentData.content },
            is_published: this.newComponentPublished
          }
        );
        this.showAddComponentDialog = false;
        this.newComponentType = '';
      } catch (error) {
        console.error('Error adding component:', error);
      }
    },
    editComponent(componentId: number) {
      const component = this.pageMenuStore.selectedMenu?.page_data.find(c => c.id === componentId);
      if (component) {
        this.editComponentId = componentId;
        this.editComponentType = component.component_type;
        this.editComponentName = component.tag || '';
        this.editComponentData = { ...component.data };
        this.editComponentPublished = component.is_published;
        this.showEditComponentDialog = true;
      }
    },
    cancelEditComponent() {
      this.showEditComponentDialog = false;
    },
    async saveEditComponent() {
      if (!this.pageMenuStore.selectedMenu) return;
      try {
        await this.pageMenuStore.updatePageData(
          this.pageMenuStore.selectedMenu.id,
          this.editComponentId,
          {
            tag: this.editComponentName,
            data: { content: this.editComponentData.content },
            is_published: this.editComponentPublished
          }
        );
        this.showEditComponentDialog = false;
      } catch (error) {
        console.error('Error updating component:', error);
      }
    },
    confirmDeleteComponent(componentId: number) {
      this.deleteType = 'component';
      this.deleteId = componentId;
      this.confirmDeleteMessage = 'Are you sure you want to delete this component? This action cannot be undone.';
      this.showConfirmDeleteDialog = true;
    },
    cancelDelete() {
      this.showConfirmDeleteDialog = false;
      this.deleteType = '';
      this.deleteId = 0;
    },
    async confirmDelete() {
      if (this.deleteType === 'menu') {
        await this.pageMenuStore.deleteMenu(this.deleteId);
      } else if (this.deleteType === 'component' && this.pageMenuStore.selectedMenu) {
        await this.pageMenuStore.deletePageData(
          this.pageMenuStore.selectedMenu.id,
          this.deleteId
        );
      }
      this.showConfirmDeleteDialog = false;
      this.deleteType = '';
      this.deleteId = 0;
    },
    async moveMenuUp(menuId: number) {
      try {
        await this.pageMenuStore.moveMenuUp(menuId);
      } catch (error) {
        console.error('Error moving menu up:', error);
      }
    },
    async moveMenuDown(menuId: number) {
      try {
        await this.pageMenuStore.moveMenuDown(menuId);
      } catch (error) {
        console.error('Error moving menu down:', error);
      }
    },
    async moveComponentUp(componentId: number) {
      if (!this.pageMenuStore.selectedMenu) return;
      try {
        await this.pageMenuStore.movePageDataUp(this.pageMenuStore.selectedMenu.id, componentId);
      } catch (error) {
        console.error('Error moving component up:', error);
      }
    },
    async moveComponentDown(componentId: number) {
      if (!this.pageMenuStore.selectedMenu) return;
      try {
        await this.pageMenuStore.movePageDataDown(this.pageMenuStore.selectedMenu.id, componentId);
      } catch (error) {
        console.error('Error moving component down:', error);
      }
    },
    isFirstComponent(componentId: number): boolean {
      if (!this.pageMenuStore.selectedMenu || !this.pageMenuStore.selectedMenu.page_data) return false;
      const components = this.pageMenuStore.selectedMenu.page_data;
      if (components.length === 0) return false;
      return componentId === components[0].id;
    },
    isLastComponent(componentId: number): boolean {
      if (!this.pageMenuStore.selectedMenu || !this.pageMenuStore.selectedMenu.page_data) return false;
      const components = this.pageMenuStore.selectedMenu.page_data;
      if (components.length === 0) return false;
      return componentId === components[components.length - 1].id;
    },
    isFirstMenu(menuId: number): boolean {
      const menus = this.pageMenuStore.menus;
      if (menus.length === 0) return false;
      return menuId === menus[0].id;
    },
    isLastMenu(menuId: number): boolean {
      const menus = this.pageMenuStore.menus;
      if (menus.length === 0) return false;
      return menuId === menus[menus.length - 1].id;
    }
  }
});
</script>