import { defineStore } from 'pinia';
import apiService from '@/services/apiService';
import type {
  User,
  LoginCredentials,
  ChangePasswordCredentials,
  LoginResponsePayload,
  ChangePasswordResponsePayload,
  RefreshTokenResponsePayload,
  UserResponsePayload,
  ApiErrorResponse
} from '@/types/user';
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
    accessToken: localStorage.getItem('access_token'),
    isAuthenticated: !!localStorage.getItem('access_token'),
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
    setUserData(userData: {
      user: User;
      roles: string[];
      permissions: string[];
      access_token: string;
      first_login: boolean;
    }) {
      this.user = userData.user;
      this.roles = userData.roles;
      this.permissions = userData.permissions;
      this.accessToken = userData.access_token;
      this.isAuthenticated = true;
      this.error = null;

      // Make sure first_login is set correctly on the user object
      if (this.user) {
        this.user.first_login = userData.first_login;
      }

      // Save token to localStorage for persistence
      localStorage.setItem('access_token', userData.access_token);

      console.log('User data set in store:', {
        user: this.user,
        roles: this.roles,
        permissions: this.permissions,
        isFirstLogin: this.user?.first_login
      });
    },

    async login(credentials: LoginCredentials) {
      this.isLoading = true;
      this.error = null;

      try {
        console.log('Login attempt with:', credentials.email);
        const response = await apiService.auth.login(
          credentials.email,
          credentials.password
        );

        console.log('Login response:', response.data);

        // Extract payload from the new response structure
        const payload = response.data.payload;

        this.setUserData(payload);

        return payload;
      } catch (error: any) {
        console.error('Login error:', error);
        const errorData = error.response?.data as ApiErrorResponse | undefined;
        this.error = errorData?.message || 'Login failed';
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    async changePassword(credentials: ChangePasswordCredentials) {
      this.isLoading = true;
      this.error = null;

      try {
        console.log('Changing password with payload:', credentials);

        const response = await apiService.auth.changePassword(
          credentials.new_password,
          credentials.new_password_confirmation,
          credentials.current_password
        );

        console.log('Change password response:', response.data);

        // Extract payload from the new response structure
        const payload = response.data.payload;

        // Update token
        this.accessToken = payload.access_token;
        localStorage.setItem('access_token', payload.access_token);

        // Update first_login status
        if (this.user) {
          this.user.first_login = payload.first_login;
        }

        return payload;
      } catch (error: any) {
        console.error('Change password error:', error);
        const errorData = error.response?.data as ApiErrorResponse | undefined;
        this.error = errorData?.message || 'Failed to change password';
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    clearUserData() {
      this.user = null;
      this.accessToken = null;
      this.isAuthenticated = false;
      this.roles = [];
      this.permissions = [];
      this.error = null;

      // Remove token from localStorage
      localStorage.removeItem('access_token');
      console.log('User data cleared from store');
    },

    async logout() {
      this.isLoading = true;

      try {
        await apiService.auth.logout();
        this.clearUserData();
        router.push({ name: 'login' });
      } catch (error: any) {
        console.error('Logout error:', error);
        const errorData = error.response?.data as ApiErrorResponse | undefined;
        this.error = errorData?.message || 'Logout failed';
        // Clear user data anyway to ensure they're logged out on frontend
        this.clearUserData();
      } finally {
        this.isLoading = false;
      }
    },

    async refreshToken() {
      this.isLoading = true;

      try {
        const response = await apiService.auth.refresh();
        const payload = response.data.payload;
        this.setUserData(payload);
        return true;
      } catch (error: any) {
        console.error('Refresh token error:', error);
        this.clearUserData();
        const errorData = error.response?.data as ApiErrorResponse | undefined;
        this.error = errorData?.message || 'Session expired';
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async fetchCurrentUser() {
      if (!this.accessToken) {
        return false;
      }

      this.isLoading = true;

      try {
        const response = await apiService.auth.getCurrentUser();
        const payload = response.data.payload;

        this.user = payload.user;
        this.roles = payload.roles;
        this.permissions = payload.permissions;
        this.isAuthenticated = true;

        return true;
      } catch (error: any) {
        console.error('Fetch current user error:', error);
        const errorData = error.response?.data as ApiErrorResponse | undefined;
        this.error = errorData?.message || 'Failed to fetch user data';
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async checkAuth() {
      if (this.isAuthenticated && this.user) {
        return true;
      }

      if (this.accessToken) {
        return await this.fetchCurrentUser();
      }

      try {
        return await this.refreshToken();
      } catch (error) {
        console.error('Check auth error:', error);
        this.clearUserData();
        return false;
      }
    }
  }
});
