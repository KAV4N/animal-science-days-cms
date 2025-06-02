import { defineStore } from 'pinia';
import { useAuthStore } from './authStore';
import apiService from '@/services/apiService';
import type {
  Conference,
  ConferenceFilters,
  ConferenceStoreRequest,
  ConferenceUpdateRequest,
  ConferenceResponse,
  ConferencePaginatedResponse
} from '@/types/conference';
import type { PaginationMeta } from '@/types/common';
import type { AxiosError } from 'axios';
import type { ApiErrorResponse } from '@/types/common';

interface ConferenceState {
  conferences: Conference[];
  latestConference: Conference | null;
  currentPublicConference: Conference | null;
  loading: boolean;
  publicConferenceLoading: boolean;
  error: string | null;
  publicConferenceError: string | null;
  meta: PaginationMeta | null;
}

export const useConferenceStore = defineStore('conference', {
  state: (): ConferenceState => ({
    conferences: [],
    latestConference: null,
    currentPublicConference: null,
    loading: false,
    publicConferenceLoading: false,
    error: null,
    publicConferenceError: null,
    meta: null
  }),

  getters: {
    getConferences: (state) => state.conferences,
    isLoading: (state) => state.loading,
    isPublicConferenceLoading: (state) => state.publicConferenceLoading,
    getError: (state) => state.error,
    getPublicConferenceError: (state) => state.publicConferenceError,
    getPaginationMeta: (state) => state.meta,

    getPublishedConferences: (state) => state.conferences.filter(c => c.is_published),

    getLatestConference: (state) => state.latestConference,
    getCurrentPublicConference: (state) => state.currentPublicConference,

    getConferencesByUniversity: (state) => (universityId: number) => {
      return state.conferences.filter(c => c.university?.id === universityId);
    },

    getUpcomingConferences: (state) => {
      const today = new Date().toISOString().split('T')[0];
      return state.conferences.filter(c => c.start_date > today);
    }
  },

  actions: {
    async fetchConferences(filters: ConferenceFilters = {}) {
      const authStore = useAuthStore();
      return authStore.hasAdminAccess
        ? this.fetchConferencesAll(filters)
        : this.fetchMyConferences(filters);
    },

    async fetchConferencesAll(filters: ConferenceFilters = {}) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.get<ConferencePaginatedResponse>('/v1/conferences', {
          params: filters
        });

        this.conferences = response.data.payload;
        this.meta = response.data.meta;

        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || 'Failed to fetch conferences';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchMyConferences(filters: ConferenceFilters = {}) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.get<ConferencePaginatedResponse>('/v1/conferences/my', {
          params: filters
        });

        this.conferences = response.data.payload;
        this.meta = response.data.meta;

        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || 'Failed to fetch my conferences';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchLatestConference() {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.get<ConferenceResponse>('/v1/conferences/latest');
        this.latestConference = response.data.payload;
        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || 'Failed to fetch latest conference';
        this.latestConference = null;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchConference(id: number) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.get<ConferenceResponse>(`/v1/conferences/${id}`);
        const conference = response.data.payload;

        const index = this.conferences.findIndex(c => c.id === id);
        if (index !== -1) {
          this.conferences[index] = conference;
        } else {
          this.conferences.push(conference);
        }

        return conference;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || `Failed to fetch conference with ID: ${id}`;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createConference(conferenceData: ConferenceStoreRequest) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.post<ConferenceResponse>('/v1/conferences', conferenceData);
        const newConference = response.data.payload;

        if (newConference.is_latest) {
          this.latestConference = newConference;
        }

        this.conferences.push(newConference);

        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || 'Failed to create conference';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateConference(id: number, conferenceData: ConferenceUpdateRequest) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.put<ConferenceResponse>(`/v1/conferences/${id}`, conferenceData);
        const updatedConference = response.data.payload;

        const index = this.conferences.findIndex(c => c.id === id);
        if (index !== -1) {
          this.conferences[index] = updatedConference;
        }

        if (updatedConference.is_latest) {
          this.latestConference = updatedConference;
        } else if (this.latestConference && this.latestConference.id === id && !updatedConference.is_latest) {
          this.latestConference = null;
        }

        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || `Failed to update conference with ID: ${id}`;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateConferenceStatus(id: number, statusData: Partial<Pick<Conference, 'is_published' | 'is_latest'>>) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.patch<ConferenceResponse>(`/v1/conferences/${id}/status`, statusData);
        const updatedConference = response.data.payload;

        const index = this.conferences.findIndex(c => c.id === id);
        if (index !== -1) {
          this.conferences[index] = updatedConference;
        }

        if (statusData.is_latest !== undefined) {
          if (updatedConference.is_latest) {
            this.latestConference = updatedConference;
          } else if (this.latestConference?.id === id) {
            this.latestConference = null;
          }
        }

        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || `Failed to update conference status with ID: ${id}`;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteConference(id: number) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.delete(`/v1/conferences/${id}`);

        this.conferences = this.conferences.filter(c => c.id !== id);

        if (this.latestConference?.id === id) {
          this.latestConference = null;
        }

        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || `Failed to delete conference with ID: ${id}`;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    resetState() {
      this.conferences = [];
      this.latestConference = null;
      this.currentPublicConference = null;
      this.loading = false;
      this.publicConferenceLoading = false;
      this.error = null;
      this.publicConferenceError = null;
      this.meta = null;
    },

    async fetchPublicConferenceBySlug(slug: string) {
      this.publicConferenceLoading = true;
      this.publicConferenceError = null;
      this.currentPublicConference = null;

      try {
        const response = await apiService.get<{ payload: Conference }>(`/v1/public/conferences/${slug}`);
        this.currentPublicConference = response.data.payload;
        return this.currentPublicConference;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.publicConferenceError = axiosError.response?.data.message || `Failed to fetch public conference with slug: ${slug}`;
        this.currentPublicConference = null;
        throw error;
      } finally {
        this.publicConferenceLoading = false;
      }
    },

    clearCurrentPublicConference() {
      this.currentPublicConference = null;
      this.publicConferenceError = null;
    }
  }
});
