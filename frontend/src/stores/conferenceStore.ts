// stores/conferenceManagement.ts
import { defineStore } from 'pinia';
import apiService from '@/services/apiService';
import type { Conference, ConferenceResponse, PaginatedResponse, ApiResponse } from '@/types/conference';
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';

export const useConferenceStore = defineStore('conferenceManagement', () => {
  // State
  const conferences = ref<Conference[]>([]);
  const latestConference = ref<Conference | null>(null);
  const currentConference = ref<Conference | null>(null);
  const selectedConferences = ref<Conference[]>([]);
  const conferenceDialog = ref(false);
  const deleteConferenceDialog = ref(false);
  const deleteConferencesDialog = ref(false);
  const loading = ref(false);
  const submitted = ref(false);
  const totalRecords = ref(0);
  const currentPage = ref(1);
  const perPage = ref(10);

  // Toast for messages
  const toast = useToast();
  const router = useRouter();

  // Actions
  async function fetchConferences(page = 1, search = '') {
    loading.value = true;
    try {
      const params = { 
        page,
        per_page: perPage.value,
        search: search || undefined
      };
      
      const response = await apiService.get<PaginatedResponse<Conference>>('/conferences', { params });
      conferences.value = response.data.data;
      totalRecords.value = response.data.total;
      currentPage.value = response.data.current_page;
      return response.data;
    } catch (error) {
      console.error('Error fetching conferences:', error);
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Failed to load conferences',
        life: 3000
      });
      return null;
    } finally {
      loading.value = false;
    }
  }

  async function fetchLatestConference() {
    try {
      const response = await apiService.get<ConferenceResponse>('/conferences', { 
        params: { 
          is_latest: true
        } 
      });
      
      if (Array.isArray(response.data.data) && response.data.data.length > 0) {
        latestConference.value = response.data.data[0];
      } else {
        latestConference.value = null;
      }
      return latestConference.value;
    } catch (error) {
      console.error('Error fetching latest conference:', error);
      latestConference.value = null;
      return null;
    }
  }

  async function saveConference(conference: Conference) {
    submitted.value = true;
    try {
      let response;
      
      if (conference.id) {
        // Update existing conference
        response = await apiService.put<ConferenceResponse>(`/conferences/${conference.id}`, conference);
        
        const index = conferences.value.findIndex(c => c.id === conference.id);
        if (index !== -1) {
          conferences.value[index] = response.data.data as Conference;
        }
        
        toast.add({
          severity: 'success',
          summary: 'Successful',
          detail: 'Conference Updated',
          life: 3000
        });
      } else {
        // Create new conference
        response = await apiService.post<ConferenceResponse>('/conferences', conference);
        
        if (Array.isArray(response.data.data)) {
          if (response.data.data.length > 0) {
            conferences.value.unshift(response.data.data[0]);
          }
        } else {
          conferences.value.unshift(response.data.data as Conference);
        }
        
        toast.add({
          severity: 'success',
          summary: 'Successful',
          detail: 'Conference Created',
          life: 3000
        });
      }
      
      // If this is set as the latest conference, update the reference
      if (conference.is_latest) {
        fetchLatestConference();
      }
      
      conferenceDialog.value = false;
      currentConference.value = null;
      
      return response.data.data;
    } catch (error: any) {
      console.error('Error saving conference:', error);
      
      let errorMessage = 'Failed to save conference';
      if (error.response?.data?.message) {
        errorMessage = error.response.data.message;
      }
      
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: errorMessage,
        life: 3000
      });
      
      return null;
    } finally {
      submitted.value = false;
    }
  }

  async function setLatestConference(conference: Conference) {
    try {
      const response = await apiService.put<ConferenceResponse>(`/conferences/${conference.id}/set-latest`);
      
      // Update the conference in the list
      const index = conferences.value.findIndex(c => c.id === conference.id);
      if (index !== -1) {
        conferences.value[index] = response.data.data as Conference;
      }
      
      // Update the latest conference reference
      latestConference.value = response.data.data as Conference;
      
      // Set all other conferences as not latest
      conferences.value = conferences.value.map(c => {
        if (c.id !== conference.id) {
          return { ...c, is_latest: false };
        }
        return c;
      });
      
      toast.add({
        severity: 'success',
        summary: 'Successful',
        detail: 'Conference set as latest',
        life: 3000
      });
      
      return response.data.data;
    } catch (error) {
      console.error('Error setting latest conference:', error);
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Failed to set conference as latest',
        life: 3000
      });
      return null;
    }
  }

  async function deleteConference(id: number) {
    try {
      await apiService.delete(`/conferences/${id}`);
      
      // Remove from list
      conferences.value = conferences.value.filter(c => c.id !== id);
      
      // If this was the latest conference, fetch the new latest
      if (latestConference.value?.id === id) {
        fetchLatestConference();
      }
      
      deleteConferenceDialog.value = false;
      currentConference.value = null;
      
      toast.add({
        severity: 'success',
        summary: 'Successful',
        detail: 'Conference Deleted',
        life: 3000
      });
      
      return true;
    } catch (error) {
      console.error('Error deleting conference:', error);
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Failed to delete conference',
        life: 3000
      });
      return false;
    }
  }

  async function deleteSelectedConferences() {
    try {
      // Using Promise.all to delete multiple conferences in parallel
      await Promise.all(
        selectedConferences.value.map(conference => 
          apiService.delete(`/conferences/${conference.id}`)
        )
      );
      
      // Check if any of the deleted conferences was the latest
      const deletedLatest = selectedConferences.value.some(c => c.id === latestConference.value?.id);
      
      // Remove deleted conferences from the list
      const deletedIds = selectedConferences.value.map(c => c.id);
      conferences.value = conferences.value.filter(c => !deletedIds.includes(c.id));
      
      // If a latest conference was deleted, fetch the new latest
      if (deletedLatest) {
        fetchLatestConference();
      }
      
      deleteConferencesDialog.value = false;
      selectedConferences.value = [];
      
      toast.add({
        severity: 'success',
        summary: 'Successful',
        detail: 'Conferences Deleted',
        life: 3000
      });
      
      return true;
    } catch (error) {
      console.error('Error deleting conferences:', error);
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'Failed to delete conferences',
        life: 3000
      });
      return false;
    }
  }

  // UI Actions
  function openNewConference() {
    currentConference.value = {
      id: 0,
      university_id: 0,
      created_by: null,
      name: '',
      title: '',
      slug: '',
      description: '',
      location: '',
      venue_details: '',
      start_date: new Date().toISOString().split('T')[0],
      end_date: new Date().toISOString().split('T')[0],
      primary_color: '#3B82F6',
      secondary_color: '#93C5FD',
      is_latest: false,
      is_published: false,
      created_at: '',
      updated_at: ''
    };
    submitted.value = false;
    conferenceDialog.value = true;
  }

  function editConference(conference: Conference) {
    currentConference.value = { ...conference };
    conferenceDialog.value = true;
  }

  function confirmDeleteConference(conference: Conference) {
    currentConference.value = conference;
    deleteConferenceDialog.value = true;
  }

  function confirmDeleteSelected() {
    deleteConferencesDialog.value = true;
  }

  // Reset store state
  function resetState() {
    conferences.value = [];
    latestConference.value = null;
    currentConference.value = null;
    selectedConferences.value = [];
    conferenceDialog.value = false;
    deleteConferenceDialog.value = false;
    deleteConferencesDialog.value = false;
    loading.value = false;
    submitted.value = false;
  }

  return {
    // State
    conferences,
    latestConference,
    currentConference,
    selectedConferences,
    conferenceDialog,
    deleteConferenceDialog,
    deleteConferencesDialog,
    loading,
    submitted,
    totalRecords,
    currentPage,
    perPage,
    
    // Actions
    fetchConferences,
    fetchLatestConference,
    saveConference,
    setLatestConference,
    deleteConference,
    deleteSelectedConferences,
    
    // UI Actions
    openNewConference,
    editConference,
    confirmDeleteConference,
    confirmDeleteSelected,
    resetState
  };
});