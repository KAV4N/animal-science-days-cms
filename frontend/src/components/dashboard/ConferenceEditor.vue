<template>
  <div class="conference-editor flex flex-row">
    <!-- Main Editor Area -->
    <div class="editor-main flex-1 p-4 border-r border-gray-300">
      <h2 class="text-2xl font-bold mb-4">Page Editor</h2>
      
      <!-- No page selected message -->
      <div v-if="!selectedPage" class="flex items-center justify-center h-64 bg-gray-100 rounded-lg">
        <p class="text-gray-500">Please select a page from the right panel</p>
      </div>
      
      <!-- Editor for selected page -->
      <div v-else class="selected-page-editor">
        <h3 class="text-xl font-bold mb-2">{{ selectedPage.title }}</h3>
        
        <!-- Component list for the selected page -->
        <div class="components-container">
          <TransitionGroup name="component-list" tag="div">
            <div 
              v-for="component in selectedPage.components" 
              :key="component.id"
              class="component-item p-4 mb-4 border border-gray-300 rounded-lg bg-white"
            >
              <!-- Component header with actions -->
              <div class="component-header flex justify-between items-center mb-2">
                <div class="flex items-center">
                  <span class="font-bold">{{ component.type }}</span>
                  <Button 
                    icon="pi pi-pencil" 
                    severity="secondary" 
                    size="small"
                    @click="toggleEditMode(component.id)"
                    class="p-button-sm p-button-text ml-2"
                  />
                </div>
                <div class="actions flex gap-2">
                  <Button 
                    icon="pi pi-arrow-up" 
                    severity="secondary" 
                    size="small"
                    :disabled="isFirstComponent(component.id)"
                    @click="moveComponentUp(component.id)"
                    class="p-button-sm p-button-outlined"
                  />
                  <Button 
                    icon="pi pi-arrow-down" 
                    severity="secondary" 
                    size="small"
                    :disabled="isLastComponent(component.id)"
                    @click="moveComponentDown(component.id)"
                    class="p-button-sm p-button-outlined"
                  />
                  <Button 
                    icon="pi pi-trash" 
                    severity="danger" 
                    size="small"
                    @click="removeComponent(component.id)"
                    class="p-button-sm p-button-outlined"
                  />
                </div>
              </div>
              
              <!-- Component content (edit or view mode) -->
              <div class="component-content mt-3">
                <!-- TinyMCE Editor Component -->
                <div v-if="component.type === 'Editor'" class="wysiwyg-component">
                  <client-only>
                    <editor
                      v-model="component.content"
                      :init="{
                        height: 300,
                        menubar: true,
                        plugins: 'accordion advlist anchor autolink autoresize autosave charmap code codesample directionality fullscreen image insertdatetime link lists media nonbreaking pagebreak preview quickbars save searchreplace table template visualblocks visualchars wordcount emoticons help',
                        toolbar:
                          'undo redo | formatselect | bold italic backcolor | \
                          alignleft aligncenter alignright alignjustify | \
                          bullist numlist outdent indent | removeformat | help'
                      }"
                    />
                  </client-only>
                </div>
                
                <!-- Banner Component -->
                <div v-else-if="component.type === 'Banner'" class="banner-component">
                  <div v-if="editingComponentId === component.id" class="banner-edit bg-gray-50 p-4 rounded">
                    <div class="mb-3">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Banner Title</label>
                      <InputText v-model="component.content.title" class="w-full" />
                    </div>
                    <div class="mb-3">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                      <InputText v-model="component.content.buttonText" class="w-full" />
                    </div>
                    <div class="mb-3">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                      <InputText v-model="component.content.imageUrl" class="w-full" placeholder="https://..." />
                    </div>
                    <div class="mb-3">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Button Link</label>
                      <InputText v-model="component.content.buttonLink" class="w-full" placeholder="https://..." />
                    </div>
                    <div class="flex justify-end mt-4">
                      <Button label="Done" icon="pi pi-check" @click="toggleEditMode(0)" />
                    </div>
                  </div>
                  <div v-else class="banner-preview bg-gray-100 p-4 rounded">
                    <div class="flex items-center justify-between">
                      <div>
                        <h3 class="text-lg font-bold">{{ component.content.title }}</h3>
                        <div v-if="component.content.imageUrl" class="mt-2">
                          <div class="bg-gray-300 h-16 flex items-center justify-center text-gray-600">
                            [Image: {{ component.content.imageUrl }}]
                          </div>
                        </div>
                      </div>
                      <Button 
                        :label="component.content.buttonText" 
                        size="small"
                        class="p-button-sm"
                      />
                    </div>
                  </div>
                </div>
                
                <!-- Contact Component -->
                <div v-else-if="component.type === 'Contact'" class="contact-component">
                  <div v-if="editingComponentId === component.id" class="contact-edit bg-gray-50 p-4 rounded">
                    <div class="mb-3">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Contact Form Title</label>
                      <InputText v-model="component.content.title" class="w-full" />
                    </div>
                    <div class="mb-3">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                      <InputText v-model="component.content.email" class="w-full" placeholder="contact@example.com" />
                    </div>
                    <div class="mb-3">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                      <InputText v-model="component.content.phone" class="w-full" placeholder="+1 234 567 890" />
                    </div>
                    <div class="mb-3">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Success Message</label>
                      <InputText v-model="component.content.successMessage" class="w-full" placeholder="Thank you for your message!" />
                    </div>
                    <div class="flex justify-end mt-4">
                      <Button label="Done" icon="pi pi-check" @click="toggleEditMode(0)" />
                    </div>
                  </div>
                  <div v-else class="contact-preview bg-gray-100 p-4 rounded">
                    <h4 class="text-lg font-bold mb-2">{{ component.content.title }}</h4>
                    <div v-if="component.content.email || component.content.phone" class="mb-4 text-sm">
                      <div v-if="component.content.email" class="mb-1">Email: {{ component.content.email }}</div>
                      <div v-if="component.content.phone">Phone: {{ component.content.phone }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                      <InputText placeholder="Name" disabled />
                      <InputText placeholder="Email" disabled />
                      <div class="col-span-2">
                        <Textarea placeholder="Message" disabled rows="3" class="w-full" />
                      </div>
                      <div class="col-span-2 flex justify-end mt-2">
                        <Button label="Send Message" disabled />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </TransitionGroup>
        </div>
        
        <!-- Add new component -->
        <div class="add-component mt-4">
          <Dropdown
            v-model="newComponentType"
            :options="availableComponentTypes"
            placeholder="Select component type"
            class="mr-2"
          />
          <Button 
            label="Add Component" 
            icon="pi pi-plus" 
            @click="addComponent"
            :disabled="!newComponentType"
          />
        </div>
      </div>
    </div>
    
    <!-- Pages Sidebar -->
    <div class="pages-sidebar w-80 p-4 bg-gray-50">
      <h3 class="text-xl font-bold mb-4">Pages</h3>
      
      <!-- Page list -->
      <div class="page-list">
        <TransitionGroup name="page-list" tag="div">
          <div 
            v-for="page in pages" 
            :key="page.id"
            :class="['page-item p-3 mb-2 border rounded-lg cursor-pointer', 
              selectedPageId === page.id ? 'bg-blue-100 border-blue-300' : 'bg-white border-gray-300']"
            @click="selectPage(page.id)"
          >
            <div class="flex justify-between items-center">
              <div class="flex items-center">
                <span class="font-semibold">{{ page.title }}</span>
                <Button 
                  v-if="selectedPageId === page.id"
                  icon="pi pi-pencil" 
                  severity="secondary" 
                  size="small"
                  @click.stop="editPageTitle(page.id)"
                  class="p-button-sm p-button-text ml-1"
                />
              </div>
              <div class="page-actions flex gap-1">
                <Button 
                  icon="pi pi-arrow-up" 
                  severity="secondary" 
                  size="small"
                  :disabled="isFirstPage(page.id)"
                  @click.stop="movePageUp(page.id)"
                  class="p-button-sm p-button-text"
                />
                <Button 
                  icon="pi pi-arrow-down" 
                  severity="secondary" 
                  size="small"
                  :disabled="isLastPage(page.id)"
                  @click.stop="movePageDown(page.id)"
                  class="p-button-sm p-button-text"
                />
                <Button 
                  icon="pi pi-trash" 
                  severity="danger" 
                  size="small"
                  @click.stop="removePage(page.id)"
                  class="p-button-sm p-button-text"
                />
              </div>
            </div>
          </div>
        </TransitionGroup>
      </div>
      
      <!-- Add new page -->
      <div class="add-page mt-4">
        <div class="flex">
          <InputText v-model="newPageTitle" placeholder="Page title" class="mr-2 flex-1" />
          <Button 
            icon="pi pi-plus" 
            @click="addPage"
            :disabled="!newPageTitle.trim()"
          />
        </div>
      </div>
    </div>
    
    <!-- Edit Page Title Dialog -->
    <Dialog v-model:visible="showEditPageDialog" header="Edit Page Title" :style="{ width: '30rem' }" :modal="true">
      <div class="p-fluid">
        <div class="field">
          <label for="pageTitle">Page Title</label>
          <InputText id="pageTitle" v-model="editingPageTitle" autofocus />
        </div>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="cancelEditPageTitle" text />
        <Button label="Save" icon="pi pi-check" @click="savePageTitle" autofocus />
      </template>
    </Dialog>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { useConferenceEditorStore } from '@/stores/conferenceEditorStore';
// Import TinyMCE Vue component
import Editor from '@hugerte/hugerte-vue';


export default defineComponent({
  name: 'ConferenceEditor',
  components: {
    // Register TinyMCE editor component
    'editor': Editor
  },
  data() {
    return {
      selectedPageId: 0,
      newPageTitle: '',
      newComponentType: '',
      availableComponentTypes: [
        'Editor',
        'Banner',
        'Contact'
      ],
      nextPageId: 1,
      nextComponentId: 1,
      pages: [] as Array<{
        id: number;
        title: string;
        components: Array<{
          id: number;
          type: string;
          content: any;
        }>;
      }>,
      editingComponentId: 0,
      showEditPageDialog: false,
      editingPageTitle: '',
      editingPageId: 0,
    };
  },
  computed: {
    selectedPage() {
      return this.pages.find(page => page.id === this.selectedPageId);
    },
  },
  created() {
    // Initialize with the conference store data
    const conferenceStore = useConferenceEditorStore();
    
    // If the store has pages, use them
    if (conferenceStore.pages && conferenceStore.pages.length) {
      this.pages = JSON.parse(JSON.stringify(conferenceStore.pages));
      
      // Set the next IDs based on existing data
      const maxPageId = Math.max(...this.pages.map(p => p.id), 0);
      this.nextPageId = maxPageId + 1;
      
      const allComponents = this.pages.flatMap(p => p.components);
      const maxComponentId = allComponents.length > 0 
        ? Math.max(...allComponents.map(c => c.id), 0) 
        : 0;
      this.nextComponentId = maxComponentId + 1;
      
      // Select the first page if it exists
      if (this.pages.length > 0) {
        this.selectedPageId = this.pages[0].id;
      }
    }
  },
  methods: {
    // Page operations
    addPage() {
      if (!this.newPageTitle.trim()) return;
      
      const newPage = {
        id: this.nextPageId++,
        title: this.newPageTitle,
        components: []
      };
      
      this.pages.push(newPage);
      this.newPageTitle = '';
      this.selectedPageId = newPage.id;
      this.saveToStore();
    },
    selectPage(pageId: number) {
      this.selectedPageId = pageId;
      // Exit any component edit mode when changing pages
      this.editingComponentId = 0;
    },
    removePage(pageId: number) {
      if (this.pages.length <= 1) {
        alert('Cannot remove the last page');
        return;
      }
      
      const pageIndex = this.pages.findIndex(p => p.id === pageId);
      if (pageIndex === -1) return;
      
      this.pages.splice(pageIndex, 1);
      
      // If we removed the selected page, select another one
      if (this.selectedPageId === pageId) {
        this.selectedPageId = this.pages.length > 0 ? this.pages[0].id : 0;
      }
      
      this.saveToStore();
    },
    movePageUp(pageId: number) {
      const pageIndex = this.pages.findIndex(p => p.id === pageId);
      if (pageIndex <= 0) return;
      
      const page = this.pages.splice(pageIndex, 1)[0];
      this.pages.splice(pageIndex - 1, 0, page);
      this.saveToStore();
    },
    movePageDown(pageId: number) {
      const pageIndex = this.pages.findIndex(p => p.id === pageId);
      if (pageIndex === -1 || pageIndex >= this.pages.length - 1) return;
      
      const page = this.pages.splice(pageIndex, 1)[0];
      this.pages.splice(pageIndex + 1, 0, page);
      this.saveToStore();
    },
    isFirstPage(pageId: number): boolean {
      const pageIndex = this.pages.findIndex(p => p.id === pageId);
      return pageIndex === 0;
    },
    isLastPage(pageId: number): boolean {
      const pageIndex = this.pages.findIndex(p => p.id === pageId);
      return pageIndex === this.pages.length - 1;
    },
    editPageTitle(pageId: number) {
      const page = this.pages.find(p => p.id === pageId);
      if (page) {
        this.editingPageId = pageId;
        this.editingPageTitle = page.title;
        this.showEditPageDialog = true;
      }
    },
    savePageTitle() {
      const pageIndex = this.pages.findIndex(p => p.id === this.editingPageId);
      if (pageIndex !== -1 && this.editingPageTitle.trim()) {
        this.pages[pageIndex].title = this.editingPageTitle;
        this.saveToStore();
      }
      this.showEditPageDialog = false;
    },
    cancelEditPageTitle() {
      this.showEditPageDialog = false;
    },
    
    // Component operations
    addComponent() {
      if (!this.selectedPage || !this.newComponentType) return;
      
      let defaultContent: any = {};
      
      // Set default content based on component type
      switch (this.newComponentType) {
        case 'Editor':
          defaultContent = '<p>Enter content here...</p>';
          break;
        case 'Banner':
          defaultContent = {
            title: 'Banner Title',
            buttonText: 'Learn More',
            imageUrl: '',
            buttonLink: '#'
          };
          break;
        case 'Contact':
          defaultContent = {
            title: 'Contact Us',
            email: 'contact@example.com',
            phone: '+1 234 567 890',
            successMessage: 'Thank you for your message!'
          };
          break;
      }
      
      const newComponent = {
        id: this.nextComponentId++,
        type: this.newComponentType,
        content: defaultContent
      };
      
      const pageIndex = this.pages.findIndex(p => p.id === this.selectedPageId);
      if (pageIndex !== -1) {
        this.pages[pageIndex].components.push(newComponent);
        this.newComponentType = '';
        this.saveToStore();
      }
    },
    removeComponent(componentId: number) {
      if (!this.selectedPage) return;
      
      const pageIndex = this.pages.findIndex(p => p.id === this.selectedPageId);
      if (pageIndex === -1) return;
      
      const componentIndex = this.pages[pageIndex].components.findIndex(c => c.id === componentId);
      if (componentIndex === -1) return;
      
      this.pages[pageIndex].components.splice(componentIndex, 1);
      this.saveToStore();
    },
    moveComponentUp(componentId: number) {
      if (!this.selectedPage) return;
      
      const pageIndex = this.pages.findIndex(p => p.id === this.selectedPageId);
      if (pageIndex === -1) return;
      
      const componentIndex = this.pages[pageIndex].components.findIndex(c => c.id === componentId);
      if (componentIndex <= 0) return;
      
      const component = this.pages[pageIndex].components.splice(componentIndex, 1)[0];
      this.pages[pageIndex].components.splice(componentIndex - 1, 0, component);
      this.saveToStore();
    },
    moveComponentDown(componentId: number) {
      if (!this.selectedPage) return;
      
      const pageIndex = this.pages.findIndex(p => p.id === this.selectedPageId);
      if (pageIndex === -1) return;
      
      const componentIndex = this.pages[pageIndex].components.findIndex(c => c.id === componentId);
      if (componentIndex === -1 || componentIndex >= this.pages[pageIndex].components.length - 1) return;
      
      const component = this.pages[pageIndex].components.splice(componentIndex, 1)[0];
      this.pages[pageIndex].components.splice(componentIndex + 1, 0, component);
      this.saveToStore();
    },
    isFirstComponent(componentId: number): boolean {
      if (!this.selectedPage) return false;
      
      const componentIndex = this.selectedPage.components.findIndex(c => c.id === componentId);
      return componentIndex === 0;
    },
    isLastComponent(componentId: number): boolean {
      if (!this.selectedPage) return false;
      
      const componentIndex = this.selectedPage.components.findIndex(c => c.id === componentId);
      return componentIndex === this.selectedPage.components.length - 1;
    },
    toggleEditMode(componentId: number) {
      this.editingComponentId = this.editingComponentId === componentId ? 0 : componentId;
      this.saveToStore();
    },
    
    // Save to store
    saveToStore() {
      const conferenceStore = useConferenceEditorStore();
      conferenceStore.setPages(JSON.parse(JSON.stringify(this.pages)));
    }
  }
});
</script>

<style scoped>
.component-list-move,
.page-list-move {
  transition: transform 0.5s ease;
}

.component-list-enter-active,
.component-list-leave-active,
.page-list-enter-active,
.page-list-leave-active {
  transition: all 0.5s ease;
}

.component-list-enter-from,
.component-list-leave-to,
.page-list-enter-from,
.page-list-leave-to {
  opacity: 0;
  transform: translateY(30px);
}
</style>