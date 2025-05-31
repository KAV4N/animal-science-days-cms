import { defineStore } from 'pinia';
import apiService from '@/services/apiService';
import type { 
  PageMenu, 
  PageData, 
  PageMenuResponse, 
  PageMenuListResponse,
  PageDataResponse,
  PageDataListResponse,
  PageMenuStoreRequest,
  PageMenuUpdateRequest,
  PageDataStoreRequest,
  PageDataUpdateRequest,
  ApiResponse
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
  // Pagination state
  menusLoading: boolean;
  menusHasMore: boolean;
  menusCurrentPage: number;
  menusTotalPages: number;
  pageDataLoading: { [menuId: number]: boolean };
  pageDataHasMore: { [menuId: number]: boolean };
  pageDataCurrentPage: { [menuId: number]: number };
  pageDataTotalPages: { [menuId: number]: number };
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
    },
    // Pagination state initialization
    menusLoading: false,
    menusHasMore: true,
    menusCurrentPage: 0,
    menusTotalPages: 1,
    pageDataLoading: {},
    pageDataHasMore: {},
    pageDataCurrentPage: {},
    pageDataTotalPages: {},
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
    },

    isMenusLoading: (state) => {
      return state.menusLoading;
    },

    isPageDataLoading: (state) => (menuId: number) => {
      return state.pageDataLoading[menuId] || false;
    }
  },

  actions: {
    setConferenceId(id: number) {
      this.conferenceId = id;
      // Reset pagination state when conference changes
      this.resetPaginationState();
    },

    resetPaginationState() {
      this.menusCurrentPage = 0;
      this.menusTotalPages = 1;
      this.menusHasMore = true;
      this.pageDataLoading = {};
      this.pageDataHasMore = {};
      this.pageDataCurrentPage = {};
      this.pageDataTotalPages = {};
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

    async fetchAllMenus(params: Record<string, any> = {}) {
      if (!this.conferenceId) return;
      
      try {
        this.menusLoading = true;
        this.error = null;
        this.menus = []; // Reset menus for fresh load
        this.menusCurrentPage = 0;
        this.menusHasMore = true;

        await this.loadAllMenusPages(params);
        
        this.sortMenusByOrder();
        
        if (this.menus.length > 0 && !this.selectedMenu) {
          this.selectedMenu = this.menus[0];
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch menus';
      } finally {
        this.menusLoading = false;
      }
    },

    async loadAllMenusPages(params: Record<string, any> = {}) {
      while (this.menusHasMore && !this.error) {
        const nextPage = this.menusCurrentPage + 1;
        
        const response = await apiService.get<PageMenuListResponse>(
          `/v1/conferences/${this.conferenceId}/menus`,
          { 
            params: { 
              ...params,
              page: nextPage 
            } 
          }
        );

        const newMenus = response.data.payload;
        this.menus.push(...newMenus);
        
        // Update pagination state
        this.menusCurrentPage = response.data.meta.current_page;
        this.menusTotalPages = response.data.meta.last_page;
        this.menusHasMore = this.menusCurrentPage < this.menusTotalPages;
      }
    },
    
    // Legacy method for backward compatibility
    async fetchMenus() {
      return this.fetchAllMenus();
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
        
        // Load all page data for this menu
        if (fetchedMenu.page_data) {
          fetchedMenu.page_data.sort((a, b) => a.order - b.order);
        } else {
          // If page_data is not included, fetch it separately
          await this.fetchAllPageData(menuId);
          const menuWithData = this.menus.find(m => m.id === menuId);
          if (menuWithData) {
            fetchedMenu.page_data = menuWithData.page_data;
          }
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

    async fetchAllPageData(menuId: number, params: Record<string, any> = {}) {
      if (!this.conferenceId) return;
      
      try {
        this.pageDataLoading[menuId] = true;
        this.error = null;
        
        // Reset page data pagination state for this menu
        this.pageDataCurrentPage[menuId] = 0;
        this.pageDataHasMore[menuId] = true;
        
        // Find the menu and reset its page_data
        const menu = this.menus.find(m => m.id === menuId);
        if (menu) {
          menu.page_data = [];
        }

        await this.loadAllPageDataPages(menuId, params);
        
        // Sort page data by order
        if (menu && menu.page_data) {
          menu.page_data.sort((a, b) => a.order - b.order);
        }
        
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch page data';
      } finally {
        this.pageDataLoading[menuId] = false;
      }
    },

    async loadAllPageDataPages(menuId: number, params: Record<string, any> = {}) {
      while (this.pageDataHasMore[menuId] && !this.error) {
        const nextPage = (this.pageDataCurrentPage[menuId] || 0) + 1;
        
        const response = await apiService.get<PageDataListResponse>(
          `/v1/conferences/${this.conferenceId}/menus/${menuId}/data`,
          { 
            params: { 
              ...params,
              page: nextPage 
            } 
          }
        );

        const newPageData = response.data.payload;
        
        // Find the menu and add the new page data
        const menu = this.menus.find(m => m.id === menuId);
        if (menu) {
          if (!menu.page_data) {
            menu.page_data = [];
          }
          menu.page_data.push(...newPageData);
        }
        
        // Update pagination state
        this.pageDataCurrentPage[menuId] = response.data.meta.current_page;
        this.pageDataTotalPages[menuId] = response.data.meta.last_page;
        this.pageDataHasMore[menuId] = this.pageDataCurrentPage[menuId] < this.pageDataTotalPages[menuId];
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
        
        // Clean up pagination state for deleted menu
        delete this.pageDataLoading[menuId];
        delete this.pageDataHasMore[menuId];
        delete this.pageDataCurrentPage[menuId];
        delete this.pageDataTotalPages[menuId];
        
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
        
        // Optionally refresh the full menu to ensure consistency
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
      this.resetPaginationState();
    }
  }
});