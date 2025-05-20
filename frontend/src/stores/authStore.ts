import { defineStore } from 'pinia';
import apiService from '@/services/apiService';
import type { User, Role, Permission } from '@/types/user';
import router from '@/router';

// Import the auth types
import type { 
  LoginRequest, 
  RegisterRequest, 
  ChangePasswordRequest,
  LoginResponse,
  RegisterResponse,
  RefreshTokenResponse,
  ChangePasswordResponse,
  AuthResponse,
} from '@/types/auth';

interface AuthState {
  user: User | null;
  accessToken: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    accessToken: null,
    isAuthenticated: false,
    isLoading: false,
    error: null,
  }),

  getters: {
    isEditor: (state) => state.user?.roles.some(role => role.name === 'editor') || false,
    isAdmin: (state) => state.user?.permissions.some(permission => permission.name === 'access.admin') || false,
    isSuperAdmin: (state) => state.user?.roles.some(role => role.name === 'super_admin') || false,

    hasEditorAccess: (state) => state.user?.permissions.some(permission => permission.name === 'access.editor') || false,
    hasAdminAccess: (state) => state.user?.permissions.some(permission => permission.name === 'access.admin') || false,
    hasSuperAdminAccess: (state) => state.user?.permissions.some(permission => permission.name === 'access.super_admin') || false,

    hasRole: (state) => (roleName: string) => state.user?.roles.some(role => role.name === roleName) || false,
    hasPermission: (state) => (permissionName: string) => state.user?.permissions.some(permission => permission.name === permissionName) || false,

    getToken: (state) => state.accessToken,
    getUser: (state) => state.user,
    getIsAuthenticated: (state) => state.isAuthenticated,
    getIsLoading: (state) => state.isLoading,
    getRoles: (state) => state.user?.roles || [],
    getRoleNames: (state) => state.user?.roles.map(role => role.name) || [],
    getPermissions: (state) => state.user?.permissions || [],
    getPermissionNames: (state) => state.user?.permissions.map(permission => permission.name) || [],
    getError: (state) => state.error,
  },

  actions: {
    setUserData(authResponse: AuthResponse) {
      this.user = authResponse.user;
      this.accessToken = authResponse.access_token;
      this.isAuthenticated = true;
      this.error = null;
    },

    clearUserData() {
      this.user = null;
      this.accessToken = null;
      this.isAuthenticated = false;
      this.error = null;
    },

    setError(error: string) {
      this.error = error;
    },

    async login(credentials: LoginRequest) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await apiService.auth.login(credentials.email, credentials.password);
        const authData = response.data.payload;
        console.log(response);
        this.setUserData(authData);
        return true;
      } catch (error: any) {
        console.log(error);
        this.isAuthenticated = false;
        this.error = error.response?.data?.message || 'Login failed';
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async register(credentials: RegisterRequest) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await apiService.auth.register(
          credentials.name,
          credentials.email,
          credentials.password,
          credentials.password_confirmation
        );
        const authData = response.data.payload;
        this.setUserData(authData);
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
        router.push({ name: 'Login' });
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
        const authData = response.data.payload;
        this.setUserData(authData);
        return true;
      } catch (error: any) {
        this.clearUserData();
        this.error = error.response?.data?.message || 'Session expired';
        router.push({ name: 'Login' });
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async fetchCurrentUser() {
      this.isLoading = true;

      try {
        const response = await apiService.auth.getCurrentUser();
        this.user = response.data.payload;
        this.isAuthenticated = true;
        return true;
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch user data';
        return false;
      } finally {
        this.isLoading = false;
      }
    },

    async changePassword(credentials: ChangePasswordRequest) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await apiService.auth.changePassword(
          credentials.new_password,
          credentials.new_password_confirmation
        );
        this.accessToken = response.data.payload.access_token;
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