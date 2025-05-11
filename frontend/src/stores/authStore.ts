import { defineStore } from 'pinia';
import apiService from '@/services/apiService';
import type {
  LoginCredentials,
  ChangePasswordCredentials,
  LoginResponsePayload, // Used in setUserData
  ChangePasswordResponsePayload,
  RefreshTokenResponsePayload, // Structure is same as LoginResponsePayload for relevant parts
  UserResponsePayload, // Used in fetchCurrentUser
  ApiErrorResponse,
  User // Keep this single import for the store's User type
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
    setUserData(payload: LoginResponsePayload | RefreshTokenResponsePayload) {
      // Construct the store's User object
      this.user = {
        ...payload.user, // Spread properties from UserFromResource
        first_login: payload.first_login, // Add first_login from the root of the payload
      };
      this.roles = payload.user.roles || [];
      this.permissions = payload.user.permissions || [];
      this.accessToken = payload.access_token;
      this.isAuthenticated = true;
      this.error = null;

      // Save token to localStorage for persistence
      localStorage.setItem('access_token', payload.access_token);

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
        const payload = response.data.payload as UserResponsePayload; // payload.user is from UserResource

        const apiUserFromResource = payload.user; // This is UserFromResource

        // Construct the store's User object
        this.user = {
          id: apiUserFromResource.id,
          name: apiUserFromResource.name,
          email: apiUserFromResource.email,
          university_id: apiUserFromResource.university_id,
          roles: apiUserFromResource.roles || [],
          permissions: apiUserFromResource.permissions || [],
          // UserFromResource doesn't have first_login.
          // Preserve existing first_login if this.user exists, otherwise default to false.
          // This is consistent with how it was handled before the UserFromResource change.
          first_login: this.user?.first_login ?? false,
        };

        // Update roles and permissions from the newly constructed user object
        this.roles = this.user.roles || []; // Ensure roles are taken from the new this.user
        this.permissions = this.user.permissions || []; // Ensure permissions are taken from the new this.user
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
