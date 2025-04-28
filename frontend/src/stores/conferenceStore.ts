import { defineStore } from 'pinia';
import apiService from '@/services/apiService';
import type { 
  Conference, 
  ConferenceFilters, 
  CreateConferencePayload, 
  UpdateConferencePayload, 
  ConferenceStatusPayload,
  ConferenceResponse, 
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
  currentConference: Conference | null;
  conferenceEditors: User[];
  loading: boolean;
  error: string | null;
  meta: PaginationMeta | null;
}

export const useConferenceStore = defineStore('conference', {
  state: (): ConferenceState => ({
    conferences: [],
    currentConference: null,
    conferenceEditors: [],
    loading: false,
    error: null,
    meta: null,
  }),

  getters: {
    getConferences: (state) => state.conferences,
    getCurrentConference: (state) => state.currentConference,
    getConferenceEditors: (state) => state.conferenceEditors,
    isLoading: (state) => state.loading,
    getError: (state) => state.error,
    getPaginationMeta: (state) => state.meta,
    
    getPublishedConferences: (state) => state.conferences.filter(c => c.is_published),
    
    getLatestConferences: (state) => state.conferences.filter(c => c.is_latest),
    
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
        const response = await apiService.get<ConferenceResponse>('/v1/conferences', { 
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
     * Fetch a single conference by ID
     */
    async fetchConference(id: number) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.get<SingleConferenceResponse>(`/v1/conferences/${id}`);
        this.currentConference = response.data.data;
        return response.data;
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
        
        if (this.currentConference && this.currentConference.id === id) {
          this.currentConference = response.data.data;
        }
        
        // Update conference in the list if it exists
        const index = this.conferences.findIndex(c => c.id === id);
        if (index !== -1) {
          this.conferences[index] = response.data.data;
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
        
        if (this.currentConference && this.currentConference.id === id) {
          this.currentConference = response.data.data;
        }
        
        // Update conference in the list if it exists
        const index = this.conferences.findIndex(c => c.id === id);
        if (index !== -1) {
          this.conferences[index] = response.data.data;
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
        
        // Remove conference from the list if it exists
        this.conferences = this.conferences.filter(c => c.id !== id);
        
        // Clear currentConference if it's the deleted one
        if (this.currentConference && this.currentConference.id === id) {
          this.currentConference = null;
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

    /**
     * Fetch editors for a conference
     */
    async fetchConferenceEditors(conferenceId: number) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.get<ConferenceEditorsResponse>(`/v1/conferences/${conferenceId}/editors`);
        this.conferenceEditors = response.data.data;
        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || `Failed to fetch editors for conference with ID: ${conferenceId}`;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Attach an editor to a conference
     */
    async attachEditor(conferenceId: number, editorData: AttachEditorPayload) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.post(`/v1/conferences/${conferenceId}/editors`, editorData);
        await this.fetchConferenceEditors(conferenceId); // Refresh editors list
        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || `Failed to attach editor to conference with ID: ${conferenceId}`;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Detach an editor from a conference
     */
    async detachEditor(conferenceId: number, userId: number) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiService.delete(`/v1/conferences/${conferenceId}/editors/${userId}`);
        
        // Remove editor from the list if it exists
        this.conferenceEditors = this.conferenceEditors.filter(e => e.id !== userId);
        
        return response.data;
      } catch (error) {
        const axiosError = error as AxiosError<ApiErrorResponse>;
        this.error = axiosError.response?.data.message || `Failed to detach editor from conference with ID: ${conferenceId}`;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Reset store state
     */
    resetState() {
      this.conferences = [];
      this.currentConference = null;
      this.conferenceEditors = [];
      this.loading = false;
      this.error = null;
      this.meta = null;
    }
  }
});