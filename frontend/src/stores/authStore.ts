import { defineStore } from 'pinia';
import apiService from '@/services/apiService';
import { tokenService } from '@/services/tokenService';
import type { User } from '@/types/user';

interface AuthState {
  user: User | null;
  roles: string[];
  permissions: string[];
  token:  boolean,
  loading: boolean;
  error: string | null;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    roles: [],
    token: tokenService.hasTokens(),
    permissions: [],
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
    },

    isAuthenticated: (state) => state.token,
  },

  actions: {

    /**
     * Register a new user
     */
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

        const { user, roles, permissions, access_token, refresh_token } = response.data.data;

        tokenService.setTokens(access_token, refresh_token);

        this.updateAuthState(user, roles, permissions);

        return response;
      } catch (error: any) {
        this.handleError(error, 'Registration failed');
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Login with email and password
     */
    async login(email: string, password: string) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.auth.login(email, password);

        const { user, roles, permissions, access_token, refresh_token } = response.data.data;

        tokenService.setTokens(access_token, refresh_token);

        this.updateAuthState(user, roles, permissions);

        return response;
      } catch (error: any) {
        this.handleError(error, 'Login failed');
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Logout the current user
     */
    async logout() {
      this.loading = true;
      this.error = null;

      try {
        if (this.isAuthenticated) {
          await apiService.auth.logout();
        }
        
        this.resetState();
        return { success: true };
      } catch (error: any) {
        this.handleError(error, 'Logout failed');
        
        this.resetState();
        
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Reset authentication state and remove tokens
     */
    resetState() {
      this.user = null;
      this.roles = [];
      this.permissions = [];

      tokenService.removeTokens();
    },

    /**
     * Update authentication state with user data
     */
    updateAuthState(user: User, roles: string[], permissions: string[]) {
      this.user = user;
      this.roles = roles;
      this.permissions = permissions;
    },

    /**
     * Handle authentication errors
     */
    handleError(error: any, defaultMessage: string) {
      this.error = error.response?.data?.message || defaultMessage;
    },

    /**
     * Fetch the current authenticated user
     */
    async fetchCurrentUser() {
      if (tokenService.hasTokens()) {
        this.loading = true;
        this.error = null;
        try {
          const response = await apiService.auth.getCurrentUser();
          const { user, roles, permissions } = response.data.data;

          this.updateAuthState(user, roles, permissions);

          return response;
        } catch (error: any) {
          this.handleError(error, 'Failed to fetch user');
          throw error;
        } finally {
          this.loading = false;
        }
      }else{
        this.resetState();
      }
    },

    /**
     * Refresh authentication tokens
     */
    async refreshToken() {
      try {
        if (!tokenService.getRefreshToken()) {
          this.resetState();
          throw new Error('No refresh token found');
        }

        const response = await apiService.auth.refresh();
        const { access_token, refresh_token } = response.data;

        tokenService.setTokens(access_token, refresh_token);

        return response;
      } catch (error) {
        this.resetState();
        throw error;
      }
    },


    /**
     * Change user password
     */
    async changePassword(newPassword: string, newPasswordConfirmation: string) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.auth.changePassword(
          newPassword, 
          newPasswordConfirmation
        );
        return response;
      } catch (error: any) {
        this.handleError(error, 'Password change failed');
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});