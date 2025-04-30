import { defineStore } from 'pinia';
import apiService from '@/services/apiService';
import type { User, LoginCredentials, RegisterCredentials, ChangePasswordCredentials } from '@/types/user';
import router from '@/router';

interface AuthState {
  user: User | null;
  accessToken: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  roles: string[];
  permissions: string[];
  error: string | null;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    accessToken: null,
    isAuthenticated: false,
    isLoading: false,
    roles: [],
    permissions: [],
    error: null,
  }),

  getters: {
    isEditor: (state) => state.roles.includes('editor'),
    isAdmin: (state) => state.roles.includes('admin'),
    isSuperAdmin: (state) => state.roles.includes('super_admin'),

    hasEditorAccess: (state) => state.permissions.includes('access.editor'),
    hasAdminAccess: (state) => state.permissions.includes('access.admin'),
    hasSuperAdminAccess: (state) => state.permissions.includes('access.super_admin'),

    hasRole: (state) => (role: string) => state.roles.includes(role),
    hasPermission: (state) => (permission: string) => state.permissions.includes(permission),

    getToken: (state) => state.accessToken,
    getUser: (state) => state.user,
    getIsAuthenticated: (state) => state.isAuthenticated,
    getIsLoading: (state) => state.isLoading,
    getRoles: (state) => state.roles,
    getPermissions: (state) => state.permissions,
    getError: (state) => state.error,
    
    
  },

  actions: {
    setUserData(userData: { user: User; roles: string[]; permissions: string[]; access_token: string }) {
      this.user = userData.user;
      this.roles = userData.roles;
      this.permissions = userData.permissions;
      this.accessToken = userData.access_token;
      this.isAuthenticated = true;
      this.error = null;
    },

    clearUserData() {
      this.user = null;
      this.accessToken = null;
      this.isAuthenticated = false;
      this.roles = [];
      this.permissions = [];
      this.error = null;
    },

    setError(error: string) {
      this.error = error;
    },

    async login(credentials: LoginCredentials) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await apiService.auth.login(credentials.email, credentials.password);
        this.setUserData(response.data.data);
        return true;
      } catch (error: any) {
        this.isAuthenticated = false;
        this.error = error.response?.data?.message || 'Login failed';
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async register(credentials: RegisterCredentials) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await apiService.auth.register(
          credentials.name,
          credentials.email,
          credentials.password,
          credentials.password_confirmation
        );
        this.setUserData(response.data.data);
        return true;
      } catch (error: any) {
        this.isAuthenticated = false;
        this.error = error.response?.data?.message || 'Registration failed';
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async logout() {
      this.isLoading = true;

      try {
        await apiService.auth.logout();
        this.clearUserData();
        router.push({ name: 'login' });
        return true;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Logout failed';
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async refreshToken() {
      this.isLoading = true;

      try {
        const response = await apiService.auth.refresh();
        this.setUserData(response.data.data);
        return true;
      } catch (error: any) {
        this.clearUserData();
        this.error = error.response?.data?.message || 'Session expired';
        router.push({ name: 'login' });
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async fetchCurrentUser() {
      this.isLoading = true;

      try {
        const response = await apiService.auth.getCurrentUser();
        this.user = response.data.data;
        this.isAuthenticated = true;
        return true;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch user data';
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async changePassword(credentials: ChangePasswordCredentials) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await apiService.auth.changePassword(
          credentials.new_password,
          credentials.new_password_confirmation
        );
        this.accessToken = response.data.data.access_token;
        return true;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to change password';
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async checkAuth() {
      if (this.user && this.accessToken) {
        return true;
      }

      try {
        const success = await this.refreshToken();
        return success;
      } catch (error) {
        this.clearUserData();
        return false;
      }
    }
  }
});
