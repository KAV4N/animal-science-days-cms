// stores/university.ts
import { defineStore } from 'pinia';
import apiService from '@/services/apiService';
import type {
  University,
  UniversityStoreRequest,
  UniversityUpdateRequest,
  UniversityPaginatedResponse,
  UniversityResponse,
} from '@/types/university';
import type { AxiosError } from 'axios';
import type { ApiErrorResponse, ApiResponse, PaginationMeta } from '@/types/common';

interface UniversityState {
  universities: University[];
  currentUniversity: University | null;
  loading: boolean;
  error: string | null;
  meta: PaginationMeta | null;
}

export const useUniversityStore = defineStore('university', {
  state: (): UniversityState => ({
    universities: [],
    currentUniversity: null,
    loading: false,
    error: null,
    meta: null,
  }),

  getters: {
    getUniversities: (state) => state.universities,
    getCurrentUniversity: (state) => state.currentUniversity,
    isLoading: (state) => state.loading,
    getError: (state) => state.error,
    getPaginationMeta: (state) => state.meta,
  },

  actions: {
    /**
     * Fetch all universities with optional search and pagination
     */
    async fetchUniversities(search?: string, page = 1, perPage = 15) {
      this.loading = true;
      this.error = null;

      try {
        const params = { search, page, per_page: perPage };
        const response = await apiService.get<UniversityPaginatedResponse>('/v1/universities', { params });

        this.universities = response.data.payload;
        this.meta = response.data.meta || null;

        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || 'Failed to fetch universities';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Fetch a single university by ID
     */
    async fetchUniversity(id: number) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.get<UniversityResponse>(`/v1/universities/${id}`);
        this.currentUniversity = response.data.payload;
        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || `Failed to fetch university with ID: ${id}`;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Create a new university
     */
    async createUniversity(universityData: UniversityStoreRequest) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.post<UniversityResponse>('/v1/universities', universityData);
        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || 'Failed to create university';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Update an existing university
     */
    async updateUniversity(id: number, universityData: UniversityUpdateRequest) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.put<UniversityResponse>(`/v1/universities/${id}`, universityData);

        if (this.currentUniversity && this.currentUniversity.id === id) {
          this.currentUniversity = response.data.payload;
        }

        const index = this.universities.findIndex(u => u.id === id);
        if (index !== -1) {
          this.universities[index] = response.data.payload;
        }

        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || `Failed to update university with ID: ${id}`;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Delete a university
     */
    async deleteUniversity(id: number) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.delete<ApiResponse<null>>(`/v1/universities/${id}`);

        this.universities = this.universities.filter(u => u.id !== id);

        if (this.currentUniversity && this.currentUniversity.id === id) {
          this.currentUniversity = null;
        }

        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || `Failed to delete university with ID: ${id}`;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Reset store state
     */
    resetState() {
      this.universities = [];
      this.currentUniversity = null;
      this.loading = false;
      this.error = null;
      this.meta = null;
    }
  }
});
