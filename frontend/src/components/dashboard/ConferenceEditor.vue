<template>
  <div class="conference-editor flex flex-row h-screen">
    <!-- Main Editor Area -->
    <div class="editor-main flex-1 p-4 border-r border-gray-300 flex flex-col h-full overflow-hidden">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Page Editor</h2>
        <div class="editor-actions flex gap-2">
          <Button 
            label="Save" 
            icon="pi pi-save" 
            @click="saveCurrentMenu"
            :disabled="!pageMenuStore.isLocked"
            class="p-button-success p-button-sm"
          />
          <Button 
            label="Exit" 
            icon="pi pi-sign-out" 
            @click="handleExit"
            class="p-button-danger p-button-sm"
          />
        </div>
      </div>
      
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
      
      <!-- No menu selected message (when menus exist but none selected) -->
      <div v-else-if="!pageMenuStore.selectedMenu && !pageMenuStore.loading" class="flex items-center justify-center h-64 bg-gray-100 rounded-lg">
        <p class="text-gray-500">Please select a page from the right panel</p>
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
              <!-- Component header with actions -->
              <div class="component-header flex justify-between items-center mb-2">
                <div class="flex items-center">
                  <span class="font-bold">{{ component.component_type }}</span>
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
                    icon="pi pi-trash" 
                    severity="danger" 
                    size="small"
                    :disabled="!pageMenuStore.isLocked"
                    @click="confirmDeleteComponent(component.id)"
                    class="p-button-sm p-button-outlined"
                  />
                </div>
              </div>
              
              <!-- Component content -->
              <div class="component-content mt-3">
                <!-- TinyMCE Editor Component -->
                <div v-if="component.component_type === 'Editor'" class="wysiwyg-component">
                  <client-only>
                    <editor
                      v-model="component.data.content"
                      :init="{
                        height: 300,
                        menubar: true,
                        plugins: 'accordion advlist anchor autolink autoresize autosave charmap code codesample directionality fullscreen image insertdatetime link lists media nonbreaking pagebreak preview quickbars save searchreplace table template visualblocks visualchars wordcount emoticons help',
                        toolbar:
                          'undo redo | formatselect | bold italic backcolor | \
                          alignleft aligncenter alignright alignjustify | \
                          bullist numlist outdent indent | removeformat | help',
                        readonly: !pageMenuStore.isLocked
                      }"
                    />
                  </client-only>
                </div>
                
                <!-- Other component types can be added here -->
                <div v-else>
                  <pre class="bg-gray-100 p-3 rounded">{{ JSON.stringify(component.data, null, 2) }}</pre>
                </div>
              </div>
              
              <!-- Publishing toggle -->
              <div class="publish-toggle mt-3 flex justify-end">
                <ToggleButton
                  v-model="component.is_published"
                  onLabel="Published"
                  offLabel="Draft"
                  onIcon="pi pi-check"
                  offIcon="pi pi-times"
                  :disabled="!pageMenuStore.isLocked"
                  @change="toggleComponentPublished(component.id, $event)"
                />
              </div>
            </div>
          </TransitionGroup>
        </div>
      </div>
    </div>
    
    <!-- Pages Sidebar -->
    <div class="pages-sidebar w-80 p-4 bg-gray-50 flex flex-col h-full overflow-hidden">
      <!-- Fixed section at the top with title and controls -->
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

        <!-- Component type dropdown fixed at top of pages sidebar -->
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

      <!-- Scrollable pages list -->
      <div class="pages-list-container flex-1 overflow-y-auto pr-1">
        <!-- Loading state -->
        <div v-if="pageMenuStore.loading && !loadingSelectedMenu" class="flex items-center justify-center h-32">
          <i class="pi pi-spin pi-spinner text-xl"></i>
        </div>
        
        <!-- Empty state for menus -->
        <div v-else-if="pageMenuStore.menus.length === 0" class="flex items-center justify-center h-32 bg-gray-100 rounded-lg">
          <p class="text-gray-500">No pages yet</p>
        </div>
        
        <!-- Menu list -->
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
          <label for="componentType" class="block mb-1">Component Type</label>
          <Dropdown
            id="componentType"
            v-model="newComponentType"
            :options="availableComponentTypes"
            placeholder="Select component type"
            class="w-full"
          />
        </div>
        
        <!-- Editor component settings -->
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
        
        <!-- Can add more component type settings here -->
        
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

    <!-- Save Changes Dialog for Page Orders -->
    <Dialog v-model:visible="showSaveChangesDialog" header="Save Changes" :style="{ width: '30rem' }" :modal="true">
      <div class="confirmation-content">
        <p>You have unsaved page order changes. Would you like to save them now?</p>
      </div>
      <template #footer>
        <Button label="No" icon="pi pi-times" @click="cancelSaveChanges" text />
        <Button label="Yes" icon="pi pi-check" @click="saveChanges" autofocus class="p-button-success" />
      </template>
    </Dialog>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { usePageMenuStore } from '@/stores/pageMenuStore';
import { useRoute, useRouter } from 'vue-router';
import Editor from '@hugerte/hugerte-vue';
import apiService from '@/services/apiService';

export default defineComponent({
  name: 'ConferenceEditor',
  components: {
    'editor': Editor
  },
  data() {
    return {
      // Component selectors
      availableComponentTypes: ['Editor', 'Image', 'Table', 'Video', 'Gallery'],
      
      // Menu dialog
      showAddMenuDialog: false,
      newMenuTitle: '',
      newMenuSlug: '',
      newMenuPublished: false,
      
      // Edit menu dialog
      showEditMenuDialog: false,
      editMenuId: 0,
      editMenuTitle: '',
      editMenuSlug: '',
      editMenuPublished: false,
      
      // Component dialog
      showAddComponentDialog: false,
      newComponentType: '',
      newComponentData: {
        content: '<p>Enter content here...</p>'
      },
      newComponentPublished: false,
      
      // Validation errors
      titleError: '',
      slugError: '',
      
      // Delete confirmation
      showConfirmDeleteDialog: false,
      deleteType: '', // 'menu' or 'component'
      deleteId: 0,
      confirmDeleteMessage: '',
      
      // Helper properties
      orderChanges: {
        menus: [] as number[],
        components: [] as number[]
      },
      showSaveChangesDialog: false,
      hasUnsavedChanges: false,
      
      // Loading state for selected menu
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
    // Set conference ID from route
    this.pageMenuStore.setConferenceId(this.conferenceId);
    
    // Load page menus
    this.fetchMenus();
  },
  mounted() {
    // Setup beforeunload event to prevent accidental navigations
    window.addEventListener('beforeunload', this.handleBeforeUnload);
  },
  beforeUnmount() {
    // Clean up
    window.removeEventListener('beforeunload', this.handleBeforeUnload);
    
    // Release lock if we have one
    if (this.pageMenuStore.isLocked) {
      this.pageMenuStore.releaseLock();
    }
  },
  methods: {
    // Page load and setup
    async fetchMenus() {
      await this.pageMenuStore.fetchMenus();
      
      // Check if we have a lock already
      if (!this.pageMenuStore.isLocked) {
        // If we don't have a lock, check if someone else has it
        await this.checkLock();
      }
    },
    
    async checkLock() {
      try {
        const response = await apiService.get(`/v1/conferences/${this.conferenceId}/lock`);
        if (response.data.payload.is_locked) {
          // Check if the current user owns the lock
          const currentUser = await apiService.get('/v1/users/me');
          const userId = currentUser.data.payload.id;
          
          if (response.data.payload.lock_info.user_id === userId) {
            // Current user owns the lock
            this.pageMenuStore.lockStatus = {
              isLocked: true,
              lockInfo: response.data.payload.lock_info
            };
          } else {
            // Someone else has the lock
            this.pageMenuStore.lockStatus = {
              isLocked: false,
              lockInfo: response.data.payload.lock_info
            };
          }
        }
      } catch (error) {
        console.error('Error checking lock:', error);
      }
    },
    
    async acquireLock() {
      await this.pageMenuStore.acquireLock();
    },
    
    handleBeforeUnload(event: BeforeUnloadEvent) {
      if (this.pageMenuStore.isLocked || this.hasUnsavedChanges) {
        // Prevent accidental navigation while we have a lock or unsaved changes
        event.preventDefault();
        event.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return event.returnValue;
      }
    },
    
    // Lock and Exit
    async handleExit() {
      // Check for unsaved changes
      if (this.hasUnsavedChanges) {
        this.showSaveChangesDialog = true;
        return;
      }
      
      // If we have a lock, release it
      if (this.pageMenuStore.isLocked) {
        await this.pageMenuStore.releaseLock();
      }
      
      // Navigate back to conferences
      this.$router.push('/conferences');
    },
    
    // Save current menu
    async saveCurrentMenu() {
      if (!this.pageMenuStore.selectedMenu) return;
      
      try {
        // Save all component changes if they've been modified
        const components = this.pageMenuStore.selectedMenu.page_data;
        if (components && components.length > 0) {
          for (const component of components) {
            await this.pageMenuStore.updatePageData(
              this.pageMenuStore.selectedMenu.id,
              component.id,
              {
                data: component.data,
                is_published: component.is_published
              }
            );
          }
        }
        
        // Save any pending order changes
        if (this.hasUnsavedChanges) {
          await this.saveChanges();
        }
        
        // Refresh lock after save
        await this.pageMenuStore.refreshLock();
      } catch (error) {
        console.error('Error saving page:', error);
      }
    },
    
    // Generate a URL slug from a title
    generateSlug(title: string): string {
      return title
        .toLowerCase()
        .replace(/[^\w\s-]/g, '') // Remove special characters
        .replace(/\s+/g, '-') // Replace spaces with hyphens
        .replace(/--+/g, '-') // Replace multiple hyphens with a single one
        .trim();
    },
    
    // Menu operations
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
      
      // Validate input
      if (!this.newMenuTitle.trim()) {
        this.titleError = 'Title is required';
        return;
      }
      
      // Generate slug if not provided
      const slug = this.newMenuSlug.trim() || this.generateSlug(this.newMenuTitle);
      
      try {
        // Get highest order value to place new menu at the end
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
        // Handle validation errors
        if (error.response && error.response.data && error.response.data.errors) {
          const errors = error.response.data.errors;
          if (errors.title) {
            this.titleError = errors.title[0];
          }
          if (errors.slug) {
            this.slugError = errors.slug[0];
          }
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
      
      // Validate input
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
        // Handle validation errors
        if (error.response && error.response.data && error.response.data.errors) {
          const errors = error.response.data.errors;
          if (errors.title) {
            this.titleError = errors.title[0];
          }
          if (errors.slug) {
            this.slugError = errors.slug[0];
          }
        }
      }
    },
    
    async selectMenu(menuId: number) {
      // Check for unsaved changes first
      if (this.hasUnsavedChanges) {
        this.showSaveChangesDialog = true;
        return;
      }
      
      // Set loading state for selected menu
      this.loadingSelectedMenu = true;
      
      try {
        // Load the menu with its page data
        await this.pageMenuStore.fetchMenu(menuId);
      } catch (error) {
        console.error('Error loading menu data:', error);
      } finally {
        // Clear loading state
        this.loadingSelectedMenu = false;
      }
    },
    
    confirmDeleteMenu(menuId: number) {
      this.deleteType = 'menu';
      this.deleteId = menuId;
      this.confirmDeleteMessage = 'Are you sure you want to delete this page? This action cannot be undone.';
      this.showConfirmDeleteDialog = true;
    },
    
    // Component operations
    openAddComponentDialog() {
      if (!this.pageMenuStore.selectedMenu) return;
      
      // Reset component data based on type
      if (this.newComponentType === 'Editor') {
        this.newComponentData = { content: '<p>Enter content here...</p>' };
      } else if (this.newComponentType === 'Image') {
        this.newComponentData = { url: '', alt: '', caption: '' };
      } else if (this.newComponentType === 'Table') {
        this.newComponentData = { rows: [['', '', ''], ['', '', ''], ['', '', '']] };
      } else if (this.newComponentType === 'Video') {
        this.newComponentData = { url: '', caption: '' };
      } else if (this.newComponentType === 'Gallery') {
        this.newComponentData = { images: [] };
      } else {
        this.newComponentData = {};
      }
      
      this.newComponentPublished = false;
      this.showAddComponentDialog = true;
    },
    
    cancelAddComponent() {
      this.showAddComponentDialog = false;
    },
    
    async handleAddComponent() {
      if (!this.pageMenuStore.selectedMenu || !this.newComponentType) return;
      
      try {
        // Get highest order value to place new component at the end
        const currentComponents = this.pageMenuStore.selectedMenu.page_data || [];
        const maxOrder = currentComponents.length > 0
          ? Math.max(...currentComponents.map(c => c.order))
          : -1;
        
        await this.pageMenuStore.createPageData(
          this.pageMenuStore.selectedMenu.id,
          {
            component_type: this.newComponentType,
            order: maxOrder + 1,
            data: this.newComponentData,
            is_published: this.newComponentPublished
          }
        );
        
        this.showAddComponentDialog = false;
      } catch (error: any) {
        console.error('Error adding component:', error);
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
    
    // Component order operations
    async moveComponentUp(componentId: number) {
      if (!this.pageMenuStore.selectedMenu) return;
      
      const components = this.pageMenuStore.selectedMenu.page_data;
      if (!components) return;
      
      const index = components.findIndex(c => c.id === componentId);
      if (index <= 0) return;
      
      // Swap orders with the component above
      const currentComponent = components[index];
      const prevComponent = components[index - 1];
      
      // Swap display positions locally first
      const tempOrder = currentComponent.order;
      currentComponent.order = prevComponent.order;
      prevComponent.order = tempOrder;
      
      // Add to order changes to be saved later
      this.orderChanges.components.push(currentComponent.id);
      this.orderChanges.components.push(prevComponent.id);
      this.hasUnsavedChanges = true;
      
      // Sort components by order to update the display
      this.pageMenuStore.selectedMenu.page_data.sort((a, b) => a.order - b.order);
    },
    
    async moveComponentDown(componentId: number) {
      if (!this.pageMenuStore.selectedMenu) return;
      
      const components = this.pageMenuStore.selectedMenu.page_data;
      if (!components) return;
      
      const index = components.findIndex(c => c.id === componentId);
      if (index === -1 || index >= components.length - 1) return;
      
      // Swap orders with the component below
      const currentComponent = components[index];
      const nextComponent = components[index + 1];
      
      // Swap display positions locally first
      const tempOrder = currentComponent.order;
      currentComponent.order = nextComponent.order;
      nextComponent.order = tempOrder;
      
      // Add to order changes to be saved later
      this.orderChanges.components.push(currentComponent.id);
      this.orderChanges.components.push(nextComponent.id);
      this.hasUnsavedChanges = true;
      
      // Sort components by order to update the display
      this.pageMenuStore.selectedMenu.page_data.sort((a, b) => a.order - b.order);
    },
    
    isFirstComponent(componentId: number): boolean {
      if (!this.pageMenuStore.selectedMenu || !this.pageMenuStore.selectedMenu.page_data) return false;
      
      const components = this.pageMenuStore.selectedMenu.page_data;
      if (components.length === 0) return false;
      
      // Sort components by order
      const sortedComponents = [...components].sort((a, b) => a.order - b.order);
      return componentId === sortedComponents[0].id;
    },
    
    isLastComponent(componentId: number): boolean {
      if (!this.pageMenuStore.selectedMenu || !this.pageMenuStore.selectedMenu.page_data) return false;
      
      const components = this.pageMenuStore.selectedMenu.page_data;
      if (components.length === 0) return false;
      
      // Sort components by order
      const sortedComponents = [...components].sort((a, b) => a.order - b.order);
      return componentId === sortedComponents[sortedComponents.length - 1].id;
    },
    
    async toggleComponentPublished(componentId: number, isPublished: boolean) {
      if (!this.pageMenuStore.selectedMenu) return;
      
      try {
        await this.pageMenuStore.updatePageData(
          this.pageMenuStore.selectedMenu.id,
          componentId,
          { is_published: isPublished }
        );
      } catch (error) {
        console.error('Error toggling component published state:', error);
      }
    },
    
    // Menu order operations
    async moveMenuUp(menuId: number) {
      const menus = this.pageMenuStore.menus;
      if (!menus) return;
      
      // Sort by order first
      const sortedMenus = [...menus].sort((a, b) => a.order - b.order);
      const index = sortedMenus.findIndex(m => m.id === menuId);
      if (index <= 0) return;
      
      // Swap orders with the menu above
      const currentMenu = sortedMenus[index];
      const prevMenu = sortedMenus[index - 1];
      
      // Swap display positions locally first
      const tempOrder = currentMenu.order;
      currentMenu.order = prevMenu.order;
      prevMenu.order = tempOrder;
      
      // Add to order changes to be saved later
      this.orderChanges.menus.push(currentMenu.id);
      this.orderChanges.menus.push(prevMenu.id);
      this.hasUnsavedChanges = true;
      
      // Re-sort menus to update the display
      this.pageMenuStore.sortMenusByOrder();
    },
    
    async moveMenuDown(menuId: number) {
      const menus = this.pageMenuStore.menus;
      if (!menus) return;
      
      // Sort by order first
      const sortedMenus = [...menus].sort((a, b) => a.order - b.order);
      const index = sortedMenus.findIndex(m => m.id === menuId);
      if (index === -1 || index >= sortedMenus.length - 1) return;
      
      // Swap orders with the menu below
      const currentMenu = sortedMenus[index];
      const nextMenu = sortedMenus[index + 1];
      
      // Swap display positions locally first
      const tempOrder = currentMenu.order;
      currentMenu.order = nextMenu.order;
      nextMenu.order = tempOrder;
      
      // Add to order changes to be saved later
      this.orderChanges.menus.push(currentMenu.id);
      this.orderChanges.menus.push(nextMenu.id);
      this.hasUnsavedChanges = true;
      
      // Re-sort menus to update the display
      this.pageMenuStore.sortMenusByOrder();
    },
    
    isFirstMenu(menuId: number): boolean {
      const menus = this.pageMenuStore.menus;
      if (menus.length === 0) return false;
      
      // Sort menus by order
      const sortedMenus = [...menus].sort((a, b) => a.order - b.order);
      return menuId === sortedMenus[0].id;
    },
    
    isLastMenu(menuId: number): boolean {
      const menus = this.pageMenuStore.menus;
      if (menus.length === 0) return false;
      
      // Sort menus by order
      const sortedMenus = [...menus].sort((a, b) => a.order - b.order);
      return menuId === sortedMenus[sortedMenus.length - 1].id;
    },
    
    // Save all pending changes
    async saveChanges() {
      if (!this.hasUnsavedChanges) return;
      
      try {
        // Save menu order changes
        for (const menuId of this.orderChanges.menus) {
          const menu = this.pageMenuStore.getMenuById(menuId);
          if (menu) {
            await this.pageMenuStore.updateMenuPosition(menuId, menu.order);
          }
        }
        
        // Save component order changes
        if (this.pageMenuStore.selectedMenu) {
          for (const componentId of this.orderChanges.components) {
            const component = this.pageMenuStore.selectedMenu.page_data?.find(c => c.id === componentId);
            if (component) {
              await this.pageMenuStore.updatePageDataPosition(
                this.pageMenuStore.selectedMenu.id,
                componentId,
                component.order
              );
            }
          }
        }
        
        // Reset changes tracker
        this.orderChanges.menus = [];
        this.orderChanges.components = [];
        this.hasUnsavedChanges = false;
        this.showSaveChangesDialog = false;
        
        // Refresh the lock
        await this.pageMenuStore.refreshLock();
      } catch (error) {
        console.error('Error saving order changes:', error);
      }
    },
    
    cancelSaveChanges() {
      this.showSaveChangesDialog = false;
      
      // Revert the local order changes by reloading data
      this.fetchMenus();
      if (this.pageMenuStore.selectedMenu) {
        this.pageMenuStore.fetchMenu(this.pageMenuStore.selectedMenu.id);
      }
      
      // Reset changes tracker
      this.orderChanges.menus = [];
      this.orderChanges.components = [];
      this.hasUnsavedChanges = false;
    }
  }
});
</script>