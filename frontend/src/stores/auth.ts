// src/stores/auth.ts
import { defineStore } from 'pinia';
import apiService from '@/services/apiService';

interface User {
  id: number;
  name: string;
  email: string;
}

interface AuthState {
  user: User | null;
  roles: string[];
  permissions: string[];
  isAuthenticated: boolean;
  loading: boolean;
  error: string | null;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    roles: [],
    permissions: [],
    isAuthenticated: false,
    loading: false,
    error: null
  }),
  
  getters: {
    isEditor: (state) => state.roles.includes('editor'),
    isAdmin: (state) => state.roles.includes('admin'),
    isSuperAdmin: (state) => state.roles.includes('super_admin'),
    
    hasEditorAccess: (state) => state.permissions.includes('access.editor'),
    hasAdminAccess: (state) => state.permissions.includes('access.admin'),
    hasSuperAdminAccess: (state) => state.permissions.includes('access.super_admin'),
    
    currentUser: (state) => state.user,

    hasRole: (state) => {
      return (roleName: string): boolean => state.roles.includes(roleName);
    },
  
    hasPermission: (state) => {
      return (permissionName: string): boolean => state.permissions.includes(permissionName);
    }

  },
  
  actions: {
    async register(name: string, email: string, password: string, passwordConfirmation: string) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.auth.register(
          name, 
          email, 
          password, 
          passwordConfirmation
        );
        
        const { user, roles, permissions } = response.data.data;
        
        this.user = user;
        this.roles = roles;
        this.permissions = permissions;
        this.isAuthenticated = true;
        
        return response;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Registration failed';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async login(email: string, password: string) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.auth.login(email, password);
        
        const { user, roles, permissions } = response.data.data;
        
        this.user = user;
        this.roles = roles;
        this.permissions = permissions;
        this.isAuthenticated = true;
        
        return response;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Login failed';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async logout() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.auth.logout();
        
        this.user = null;
        this.roles = [];
        this.permissions = [];
        this.isAuthenticated = false;
        
        return response;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Logout failed';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async fetchCurrentUser() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.auth.getCurrentUser();
        
        const { user, roles, permissions } = response.data.data;
        
        this.user = user;
        this.roles = roles;
        this.permissions = permissions;
        this.isAuthenticated = true;
        
        return response;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch user';
        this.isAuthenticated = false;
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async checkAccess(accessType: string) {
      try {
        const response = await apiService.access.check(accessType);
        return response.data;
      } catch (error) {
        console.error(`Access check failed for ${accessType}`, error);
        throw error;
      }
    }
  }
});