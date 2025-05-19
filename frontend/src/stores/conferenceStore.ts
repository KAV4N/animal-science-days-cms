import { defineStore } from 'pinia';
import { useAuthStore } from './authStore';
import apiService from '@/services/apiService';
import type { 
  Conference, 
  ConferenceFilters, 
  ConferenceStoreRequest, 
  ConferenceUpdateRequest, 
  ConferenceResponse, 
  ConferencePaginatedResponse,
  ConferenceLockResponse,
  ConferenceLockInfo
} from '@/types/conference';
import type { PaginationMeta } from '@/types/common';
import type { AxiosError } from 'axios';
import type { ApiErrorResponse } from '@/types/common';

interface ConferenceState {
  conferences: Conference[];
  latestConference: Conference | null;
  loading: boolean;
  error: string | null;
  meta: PaginationMeta | null;
  currentLock: ConferenceLockInfo | null;
}

export const useConferenceStore = defineStore('conference', {
  state: (): ConferenceState => ({
    conferences: [],
    latestConference: null,
    loading: false,
    error: null,
    meta: null,
    currentLock: null
  }),

  getters: {
    getConferences: (state) => state.conferences,
    isLoading: (state) => state.loading,
    getError: (state) => state.error,
    getPaginationMeta: (state) => state.meta,
    getCurrentLock: (state) => state.currentLock,

    getPublishedConferences: (state) => state.conferences.filter(c => c.is_published),

    getLatestConference: (state) => state.latestConference,

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

        // If there's a lock for this conference, clear it
        if (this.currentLock && this.currentLock.conferenceId === id) {
          this.currentLock = null;
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

    async acquireLock(conferenceId: number) {
      try {
        const response = await apiService.post<ConferenceResponse>(`/v1/conferences/${conferenceId}/lock`);
        // Store the lock information for the current user session
        this.currentLock = response.data.payload.lock_status || null;
        
        // Update the conference in the store with lock info
        const index = this.conferences.findIndex(c => c.id === conferenceId);
        if (index !== -1 && response.data.payload.lock_status) {
          this.conferences[index].lock_status = response.data.payload.lock_status;
        }
        
        return response.data;
      } catch (error) {
        // Pass through the error to be handled by the component
        throw error;
      }
    },

    async releaseLock(conferenceId: number) {
      try {
        const response = await apiService.delete(`/v1/conferences/${conferenceId}/lock`);
        // Clear the current lock
        this.currentLock = null;
        
        // Update the conference in the store to remove lock info
        const index = this.conferences.findIndex(c => c.id === conferenceId);
        if (index !== -1) {
          this.conferences[index].lock_status = undefined;
        }
        
        return response.data;
      } catch (error) {
        // Clear the lock even if the API call fails
        this.currentLock = null;
        throw error;
      }
    },

    async refreshLock(conferenceId: number) {
      try {
        const response = await apiService.post(`/v1/conferences/${conferenceId}/lock/refresh`);
        return response.data;
      } catch (error) {
        // If refresh fails, throw the error but don't clear the lock
        // as the component will handle this appropriately
        throw error;
      }
    },

    // Only use this when needed - conferences should already include lock_status from API
    async checkLock(conferenceId: number) {
      try {
        const response = await apiService.get<ConferenceLockResponse>(`/v1/conferences/${conferenceId}/lock`);
        const authStore = useAuthStore();
        
        if (response.data.payload.is_locked) {
          // Only set current lock if it's our lock
          if (response.data.payload.lock_info?.user_id === authStore.user?.id) {
            this.currentLock = response.data.payload.lock_info;
          }
          
          // Update the conference in the store with lock info
          const index = this.conferences.findIndex(c => c.id === conferenceId);
          if (index !== -1) {
            this.conferences[index].lock_status = response.data.payload.lock_info;
          }
        }
        
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    resetState() {
      this.conferences = [];
      this.latestConference = null;
      this.loading = false;
      this.error = null;
      this.meta = null;
      this.currentLock = null;
    }
  }
});