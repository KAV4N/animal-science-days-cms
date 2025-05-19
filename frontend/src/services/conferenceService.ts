// Import the original service
import apiService from './apiService';
import type { ApiResponse, ApiPaginatedResponse } from '@/types/common';
import type { Conference, ConferenceFilters } from '@/types/conference';
import type { Decade } from '@/types/decade';

/**
 * Conference related API calls - Modified to handle university display
 */
const conferenceService = {
  /**
   * Get all decades that have conferences
   */
  getDecades() {
    return apiService.get<ApiResponse<Decade[]>>('/v1/conferences/decades');
  },

  /**
   * Get conferences by decade - With updated handling for university data
   */
  getConferencesByDecade(
    decade: string, 
    page: number = 1, 
    perPage: number = 100, // Get larger number by default
    sortField: string = 'start_date',
    sortOrder: string = 'desc'
  ) {
    return apiService.get<ApiPaginatedResponse<Conference[]>>(`/v1/conferences/decade/${decade}`, {
      params: {
        page,
        per_page: perPage,
        sort_field: sortField,
        sort_order: sortOrder,
        include: 'university' // Explicitly request university data if supported by API
      }
    });
  },

  /**
   * Get all conferences with optional filters
   */
  getConferences(filters: ConferenceFilters = {}) {
    return apiService.get<ApiPaginatedResponse<Conference[]>>('/v1/conferences', {
      params: {
        ...filters,
        include: 'university' // Explicitly request university data
      }
    });
  },

  /**
   * Get a single conference by ID
   */
  getConference(id: number) {
    return apiService.get<ApiResponse<Conference>>(`/v1/conferences/${id}`, {
      params: {
        include: 'university' // Explicitly request university data
      }
    });
  },

  /**
   * Get the latest conference
   */
  getLatestConference() {
    return apiService.get<ApiResponse<Conference>>('/v1/conferences/latest', {
      params: {
        include: 'university' // Explicitly request university data
      }
    });
  }
};

export default conferenceService;