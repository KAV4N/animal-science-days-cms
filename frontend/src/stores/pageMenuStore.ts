// src/stores/pageMenuStore.ts
import { defineStore } from 'pinia';
import apiService from '@/services/apiService';
import type { 
  PageMenu, 
  PageData, 
  PageMenuResponse, 
  PageMenuListResponse,
  PageDataResponse,
  PageMenuStoreRequest,
  PageMenuUpdateRequest,
  PageDataStoreRequest,
  PageDataUpdateRequest
} from '@/types/pageMenu';

interface PageMenuState {
  conferenceId: number | null;
  menus: PageMenu[];
  selectedMenu: PageMenu | null;
  loading: boolean;
  error: string | null;
  lockStatus: {
    isLocked: boolean;
    lockInfo: any | null;
  };
}

export const usePageMenuStore = defineStore('pageMenu', {
  state: (): PageMenuState => ({
    conferenceId: null,
    menus: [],
    selectedMenu: null,
    loading: false,
    error: null,
    lockStatus: {
      isLocked: false,
      lockInfo: null,
    }
  }),

  getters: {
    getMenuById: (state) => (id: number) => {
      return state.menus.find(menu => menu.id === id) || null;
    },
    
    hasMenus: (state) => {
      return state.menus.length > 0;
    },
    
    isLocked: (state) => {
      return state.lockStatus.isLocked;
    }
  },

  actions: {
    // Set the active conference ID
    setConferenceId(id: number) {
      this.conferenceId = id;
    },
    
    // Sort menus by order
    sortMenusByOrder() {
      this.menus.sort((a, b) => a.order - b.order);
    },
    
    // Resource locking
    async acquireLock() {
      if (!this.conferenceId) return false;
      
      try {
        this.loading = true;
        const response = await apiService.post(`/v1/conferences/${this.conferenceId}/lock`);
        this.lockStatus = {
          isLocked: true,
          lockInfo: response.data.payload
        };
        return true;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to acquire lock';
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    async refreshLock() {
      if (!this.conferenceId || !this.lockStatus.isLocked) return false;
      
      try {
        const response = await apiService.post(`/v1/conferences/${this.conferenceId}/lock/refresh`);
        this.lockStatus = {
          isLocked: true,
          lockInfo: response.data.payload
        };
        return true;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to refresh lock';
        return false;
      }
    },
    
    async releaseLock() {
      if (!this.conferenceId) return false;
      
      try {
        this.loading = true;
        await apiService.delete(`/v1/conferences/${this.conferenceId}/lock`);
        this.lockStatus = {
          isLocked: false,
          lockInfo: null
        };
        return true;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to release lock';
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    // Fetch all menus for the current conference
    async fetchMenus() {
      if (!this.conferenceId) return;
      
      try {
        this.loading = true;
        this.error = null;
        
        const response = await apiService.get<PageMenuListResponse>(
          `/v1/conferences/${this.conferenceId}/menus`
        );
        
        this.menus = response.data.payload;
        
        // Sort menus by order
        this.sortMenusByOrder();
        
        // If we have menus but no selected menu, select the first one
        if (this.menus.length > 0 && !this.selectedMenu) {
          this.selectedMenu = this.menus[0];
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch menus';
      } finally {
        this.loading = false;
      }
    },
    
    // Fetch a specific menu with its page data
    async fetchMenu(menuId: number) {
      if (!this.conferenceId) return;
      
      try {
        this.loading = true;
        this.error = null;
        
        const response = await apiService.get<PageMenuResponse>(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}`
        );
        
        const fetchedMenu = response.data.payload;
        
        // Sort page data by order if present
        if (fetchedMenu.page_data) {
          fetchedMenu.page_data.sort((a, b) => a.order - b.order);
        }
        
        // Update the menu in the array if it exists
        const index = this.menus.findIndex(m => m.id === menuId);
        if (index !== -1) {
          this.menus[index] = fetchedMenu;
        } else {
          this.menus.push(fetchedMenu);
        }
        
        this.selectedMenu = fetchedMenu;
        
        return fetchedMenu;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch menu';
        return null;
      } finally {
        this.loading = false;
      }
    },
    
    // Create a new page menu
    async createMenu(menuData: PageMenuStoreRequest) {
      if (!this.conferenceId) return null;
      
      try {
        this.loading = true;
        this.error = null;
        
        const response = await apiService.post<PageMenuResponse>(
          `/v1/conferences/${this.conferenceId}/menus`,
          menuData
        );
        
        const newMenu = response.data.payload;
        this.menus.push(newMenu);
        this.sortMenusByOrder();
        this.selectedMenu = newMenu;
        
        return newMenu;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to create menu';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    // Update an existing page menu
    async updateMenu(menuId: number, menuData: PageMenuUpdateRequest) {
      if (!this.conferenceId) return null;
      
      try {
        this.loading = true;
        this.error = null;
        
        const response = await apiService.put<PageMenuResponse>(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}`,
          menuData
        );
        
        const updatedMenu = response.data.payload;
        
        // Update the menu in the array
        const index = this.menus.findIndex(m => m.id === menuId);
        if (index !== -1) {
          this.menus[index] = updatedMenu;
        }
        
        // Update selected menu if it's the one being edited
        if (this.selectedMenu && this.selectedMenu.id === menuId) {
          this.selectedMenu = updatedMenu;
        }
        
        return updatedMenu;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to update menu';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    // Update menu position
    async updateMenuPosition(menuId: number, order: number) {
      if (!this.conferenceId) return null;
      
      try {
        this.error = null;
        
        const response = await apiService.patch<PageMenuResponse>(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}/position`,
          { order }
        );
        
        const updatedMenu = response.data.payload;
        
        // Update the menu in the array
        const index = this.menus.findIndex(m => m.id === menuId);
        if (index !== -1) {
          this.menus[index] = updatedMenu;
        }
        
        // Re-sort menus
        this.sortMenusByOrder();
        
        return updatedMenu;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to update menu position';
        throw error;
      }
    },
    
    // Delete a menu
    async deleteMenu(menuId: number) {
      if (!this.conferenceId) return false;
      
      try {
        this.loading = true;
        this.error = null;
        
        await apiService.delete(`/v1/conferences/${this.conferenceId}/menus/${menuId}`);
        
        // Remove the menu from the array
        this.menus = this.menus.filter(m => m.id !== menuId);
        
        // If the deleted menu was the selected one, select another one if available
        if (this.selectedMenu && this.selectedMenu.id === menuId) {
          this.selectedMenu = this.menus.length > 0 ? this.menus[0] : null;
          
          // If we selected a new menu, fetch its details
          if (this.selectedMenu) {
            await this.fetchMenu(this.selectedMenu.id);
          }
        }
        
        return true;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to delete menu';
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    // Create page data for a menu
    async createPageData(menuId: number, pageData: PageDataStoreRequest) {
      if (!this.conferenceId) return null;
      
      try {
        this.loading = true;
        this.error = null;
        
        const response = await apiService.post<PageDataResponse>(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}/data`,
          pageData
        );
        
        const newPageData = response.data.payload;
        
        // Update the menu's page data if it's loaded
        const menu = this.menus.find(m => m.id === menuId);
        if (menu && menu.page_data) {
          menu.page_data.push(newPageData);
          menu.page_data.sort((a, b) => a.order - b.order);
        }
        
        // Refresh the menu to ensure we have the latest data
        await this.fetchMenu(menuId);
        
        return newPageData;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to create page data';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    // Update page data
    async updatePageData(menuId: number, dataId: number, pageData: PageDataUpdateRequest) {
      if (!this.conferenceId) return null;
      
      try {
        this.loading = true;
        this.error = null;
        
        const response = await apiService.put<PageDataResponse>(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}/data/${dataId}`,
          pageData
        );
        
        const updatedPageData = response.data.payload;
        
        // Update the page data in the menu if it's loaded
        const menu = this.menus.find(m => m.id === menuId);
        if (menu && menu.page_data) {
          const index = menu.page_data.findIndex(pd => pd.id === dataId);
          if (index !== -1) {
            menu.page_data[index] = updatedPageData;
            menu.page_data.sort((a, b) => a.order - b.order);
          }
        }
        
        return updatedPageData;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to update page data';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    // Update page data position
    async updatePageDataPosition(menuId: number, dataId: number, order: number) {
      if (!this.conferenceId) return null;
      
      try {
        this.error = null;
        
        const response = await apiService.patch<PageDataResponse>(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}/data/${dataId}/position`,
          { order }
        );
        
        const updatedPageData = response.data.payload;
        
        // Update the page data in the menu if it's loaded
        const menu = this.menus.find(m => m.id === menuId);
        if (menu && menu.page_data) {
          const index = menu.page_data.findIndex(pd => pd.id === dataId);
          if (index !== -1) {
            menu.page_data[index] = updatedPageData;
            menu.page_data.sort((a, b) => a.order - b.order);
          }
        }
        
        return updatedPageData;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to update page data position';
        throw error;
      }
    },
    
    // Delete page data
    async deletePageData(menuId: number, dataId: number) {
      if (!this.conferenceId) return false;
      
      try {
        this.loading = true;
        this.error = null;
        
        await apiService.delete(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}/data/${dataId}`
        );
        
        // Remove the page data from the menu if it's loaded
        const menu = this.menus.find(m => m.id === menuId);
        if (menu && menu.page_data) {
          menu.page_data = menu.page_data.filter(pd => pd.id !== dataId);
        }
        
        return true;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to delete page data';
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    // Reset store state
    resetState() {
      this.conferenceId = null;
      this.menus = [];
      this.selectedMenu = null;
      this.loading = false;
      this.error = null;
      this.lockStatus = {
        isLocked: false,
        lockInfo: null
      };
    }
  }
});