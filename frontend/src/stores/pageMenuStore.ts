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
    setConferenceId(id: number) {
      this.conferenceId = id;
    },
    
    sortMenusByOrder() {
      this.menus.sort((a, b) => a.order - b.order);
    },
    
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
    
    async fetchMenus() {
      if (!this.conferenceId) return;
      
      try {
        this.loading = true;
        this.error = null;
        
        const response = await apiService.get<PageMenuListResponse>(
          `/v1/conferences/${this.conferenceId}/menus`
        );
        
        this.menus = response.data.payload;
        this.sortMenusByOrder();
        
        if (this.menus.length > 0 && !this.selectedMenu) {
          this.selectedMenu = this.menus[0];
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch menus';
      } finally {
        this.loading = false;
      }
    },
    
    async fetchMenu(menuId: number) {
      if (!this.conferenceId) return;
      
      try {
        this.loading = true;
        this.error = null;
        
        const response = await apiService.get<PageMenuResponse>(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}`
        );
        
        const fetchedMenu = response.data.payload;
        
        if (fetchedMenu.page_data) {
          fetchedMenu.page_data.sort((a, b) => a.order - b.order);
        }
        
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
        
        const index = this.menus.findIndex(m => m.id === menuId);
        if (index !== -1) {
          this.menus[index] = updatedMenu;
        }
        
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
    
    async moveMenuUp(menuId: number) {
      if (!this.conferenceId) return;
      
      try {
        this.loading = true;
        this.error = null;
        
        const response = await apiService.patch<ApiResponse<PageMenu[]>>(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}/position`,
          { direction: 'up' }
        );
        
        this.menus = response.data.payload;
        this.sortMenusByOrder();
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to move menu up';
      } finally {
        this.loading = false;
      }
    },
    
    async moveMenuDown(menuId: number) {
      if (!this.conferenceId) return;
      
      try {
        this.loading = true;
        this.error = null;
        
        const response = await apiService.patch<ApiResponse<PageMenu[]>>(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}/position`,
          { direction: 'down' }
        );
        
        this.menus = response.data.payload;
        this.sortMenusByOrder();
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to move menu down';
      } finally {
        this.loading = false;
      }
    },
    
    async deleteMenu(menuId: number) {
      if (!this.conferenceId) return false;
      
      try {
        this.loading = true;
        this.error = null;
        
        await apiService.delete(`/v1/conferences/${this.conferenceId}/menus/${menuId}`);
        
        this.menus = this.menus.filter(m => m.id !== menuId);
        
        if (this.selectedMenu && this.selectedMenu.id === menuId) {
          this.selectedMenu = this.menus.length > 0 ? this.menus[0] : null;
          
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
        
        const menu = this.menus.find(m => m.id === menuId);
        if (menu && menu.page_data) {
          menu.page_data.push(newPageData);
          menu.page_data.sort((a, b) => a.order - b.order);
        }
        
        await this.fetchMenu(menuId);
        
        return newPageData;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to create page data';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
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
    
    async movePageDataUp(menuId: number, dataId: number) {
      if (!this.conferenceId) return;
      
      try {
        this.loading = true;
        this.error = null;
        
        const response = await apiService.patch<ApiResponse<PageData[]>>(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}/data/${dataId}/position`,
          { direction: 'up' }
        );
        
        const updatedPageData = response.data.payload;
        const menu = this.menus.find(m => m.id === menuId);
        if (menu) {
          menu.page_data = updatedPageData;
          menu.page_data.sort((a, b) => a.order - b.order);
        }
        if (this.selectedMenu && this.selectedMenu.id === menuId) {
          this.selectedMenu.page_data = updatedPageData;
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to move component up';
      } finally {
        this.loading = false;
      }
    },
    
    async movePageDataDown(menuId: number, dataId: number) {
      if (!this.conferenceId) return;
      
      try {
        this.loading = true;
        this.error = null;
        
        const response = await apiService.patch<ApiResponse<PageData[]>>(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}/data/${dataId}/position`,
          { direction: 'down' }
        );
        
        const updatedPageData = response.data.payload;
        const menu = this.menus.find(m => m.id === menuId);
        if (menu) {
          menu.page_data = updatedPageData;
          menu.page_data.sort((a, b) => a.order - b.order);
        }
        if (this.selectedMenu && this.selectedMenu.id === menuId) {
          this.selectedMenu.page_data = updatedPageData;
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to move component down';
      } finally {
        this.loading = false;
      }
    },
    
    async deletePageData(menuId: number, dataId: number) {
      if (!this.conferenceId) return false;
      
      try {
        this.loading = true;
        this.error = null;
        
        await apiService.delete(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}/data/${dataId}`
        );
        
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