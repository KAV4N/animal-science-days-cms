import { defineStore } from 'pinia';
import apiService from '@/services/apiService';
import type { 
  Conference, 
  ConferenceFilters, 
  CreateConferencePayload, 
  UpdateConferencePayload, 
  ConferenceStatusPayload,
  ConferencePaginatedResponse, 
  SingleConferenceResponse,
  ConferenceEditorsResponse,
  AttachEditorPayload
} from '@/types/conference';
import type { PaginationMeta } from '@/types/university';
import type { User } from '@/types/user';
import type { AxiosError } from 'axios';
import type { ApiErrorResponse } from '@/types/user';

interface ConferenceState {
  conferences: Conference[];
  latestConference: Conference | null; 
  loading: boolean;
  error: string | null;
  meta: PaginationMeta | null;
}

export const useConferenceStore = defineStore('conference', {
  state: (): ConferenceState => ({
    conferences: [],
    latestConference: null, 
    loading: false,
    error: null,
    meta: null,
  }),

  getters: {
    getConferences: (state) => state.conferences,
    isLoading: (state) => state.loading,
    getError: (state) => state.error,
    getPaginationMeta: (state) => state.meta,
    
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
    /**
     * Fetch conferences with filters
     */
    async fetchConferences(filters: ConferenceFilters = {}) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.get<ConferencePaginatedResponse>('/v1/conferences', { 
          params: filters 
        });
        
        this.conferences = response.data.data;
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

    /**
     * Fetch the latest conference
     */
    async fetchLatestConference() {
      this.loading = true;
      this.error = null;
      const response = await apiService.get<SingleConferenceResponse>('/v1/conferences/latest');
      try {
       
        
        this.latestConference = response.data.data;
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

    /**
     * Fetch a single conference by ID and update the list
     */
    async fetchConference(id: number) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.get<SingleConferenceResponse>(`/v1/conferences/${id}`);
        const conference = response.data.data;
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

    /**
     * Create a new conference
     */
    async createConference(conferenceData: CreateConferencePayload) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.post<SingleConferenceResponse>('/v1/conferences', conferenceData);
        
        // If the new conference is marked as latest, update latestConference
        if (response.data.data.is_latest) {
          this.latestConference = response.data.data;
        }
        
        // Add to conferences list
        this.conferences.push(response.data.data);
        
        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || 'Failed to create conference';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Update an existing conference
     */
    async updateConference(id: number, conferenceData: UpdateConferencePayload) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.put<SingleConferenceResponse>(`/v1/conferences/${id}`, conferenceData);
        const updatedConference = response.data.data;
        
        // Update conference in the list if it exists
        const index = this.conferences.findIndex(c => c.id === id);
        if (index !== -1) {
          this.conferences[index] = updatedConference;
        }
        
        // Update latestConference if necessary
        if (updatedConference.is_latest) {
          this.latestConference = updatedConference;
        } else if (this.latestConference && this.latestConference.id === id && !updatedConference.is_latest) {
          this.latestConference = null; // Clear if no longer latest
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

    /**
     * Update conference status (published or latest)
     */
    async updateConferenceStatus(id: number, statusData: ConferenceStatusPayload) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.patch<SingleConferenceResponse>(`/v1/conferences/${id}/status`, statusData);
        const updatedConference = response.data.data;
        
        // Update conference in the list if it exists
        const index = this.conferences.findIndex(c => c.id === id);
        if (index !== -1) {
          this.conferences[index] = updatedConference;
        }
        
        // Update latestConference if status includes latest
        if (statusData.latest !== undefined) {
          if (updatedConference.is_latest) {
            this.latestConference = updatedConference;
          } else if (this.latestConference && this.latestConference.id === id) {
            this.latestConference = null; // Clear if no longer latest
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

    /**
     * Delete a conference
     */
    async deleteConference(id: number) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.delete(`/v1/conferences/${id}`);
        
        // Remove conference from the list
        this.conferences = this.conferences.filter(c => c.id !== id);
        
        // Clear latestConference if it's the deleted one
        if (this.latestConference && this.latestConference.id === id) {
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
      this.loading = false;
      this.error = null;
      this.meta = null;
    }
  }
});