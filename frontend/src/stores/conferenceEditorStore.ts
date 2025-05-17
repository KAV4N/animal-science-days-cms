import { defineStore } from 'pinia';

interface Component {
  id: number;
  type: string;
  content: any;
}

interface Page {
  id: number;
  title: string;
  components: Component[];
}

export const useConferenceEditorStore = defineStore('conferenceEditorStore', {
  state: () => ({
    pages: [] as Page[],
  }),
  
  getters: {
    getPageById: (state) => (pageId: number) => {
      return state.pages.find(page => page.id === pageId);
    },
    
    totalPages: (state) => {
      return state.pages.length;
    },
  },
  
  actions: {
    setPages(pages: Page[]) {
      this.pages = pages;
    },
    
    addPage(page: Page) {
      this.pages.push(page);
    },
    
    removePage(pageId: number) {
      const index = this.pages.findIndex(page => page.id === pageId);
      if (index !== -1) {
        this.pages.splice(index, 1);
      }
    },
    
    updatePageOrder(pageIds: number[]) {
      // Reorder pages based on the provided array of IDs
      this.pages.sort((a, b) => {
        return pageIds.indexOf(a.id) - pageIds.indexOf(b.id);
      });
    },
    
    addComponent(pageId: number, component: Component) {
      const pageIndex = this.pages.findIndex(page => page.id === pageId);
      if (pageIndex !== -1) {
        this.pages[pageIndex].components.push(component);
      }
    },
    
    removeComponent(pageId: number, componentId: number) {
      const pageIndex = this.pages.findIndex(page => page.id === pageId);
      if (pageIndex !== -1) {
        const componentIndex = this.pages[pageIndex].components.findIndex(c => c.id === componentId);
        if (componentIndex !== -1) {
          this.pages[pageIndex].components.splice(componentIndex, 1);
        }
      }
    },
    
    updateComponentOrder(pageId: number, componentIds: number[]) {
      const pageIndex = this.pages.findIndex(page => page.id === pageId);
      if (pageIndex !== -1) {
        this.pages[pageIndex].components.sort((a, b) => {
          return componentIds.indexOf(a.id) - componentIds.indexOf(b.id);
        });
      }
    },
  },
});