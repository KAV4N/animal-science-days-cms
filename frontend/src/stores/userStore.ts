import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { 
  User, 
  UserListResponse, 
  UserPaginatedResponse 
} from '@/types/user';
import axios from 'axios';

interface UserFilter {
  roles?: string[];
  search?: string;
  sort_field?: 'name' | 'email' | 'created_at' | 'updated_at';
  sort_order?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}

export const useUserStore = defineStore('user', () => {
  const users = ref<User[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const currentPage = ref(1);
  const totalPages = ref(1);
  const totalUsers = ref(0);
  const perPage = ref(15);


  const usersByRole = computed(() => {
    const grouped: Record<string, User[]> = {};
    
    return grouped;
  });

  const sortedUsers = computed(() => {
    return [...users.value];
  });

  async function fetchUsers(filters: UserFilter = {}) {
    loading.value = true;
    error.value = null;
    
    try {
      const params = new URLSearchParams();

      if (filters.roles && filters.roles.length > 0) {
        params.append('roles', filters.roles.join(','));
      }
      
      if (filters.search) {
        params.append('search', filters.search);
      }
      
      if (filters.sort_field) {
        params.append('sort_field', filters.sort_field);
      }
      
      if (filters.sort_order) {
        params.append('sort_order', filters.sort_order);
      }

      if (filters.page) {
        params.append('page', filters.page.toString());
        currentPage.value = filters.page;
      }
      
      if (filters.per_page) {
        params.append('per_page', filters.per_page.toString());
        perPage.value = filters.per_page;
      }
      
      const response = await axios.get<UserPaginatedResponse | UserListResponse>(
        '/v1/users', 
        { params }
      );
      
      if (response.data.success) {
        users.value = response.data.payload;
        
        if ('meta' in response.data) {
          currentPage.value = response.data.meta.current_page;
          totalPages.value = response.data.meta.last_page;
          totalUsers.value = response.data.meta.total;
          perPage.value = response.data.meta.per_page;
        }
      } else {
        throw new Error(response.data.message);
      }
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch users';
      console.error(error.value);
    } finally {
      loading.value = false;
    }
  }

  async function getUsersByRole(roleName: string) {
    return fetchUsers({ roles: [roleName] });
  }

  async function searchUsers(query: string) {
    return fetchUsers({ search: query });
  }

  function setPage(page: number) {
    return fetchUsers({ page });
  }

  function setSorting(field: UserFilter['sort_field'], order: UserFilter['sort_order'] = 'asc') {
    return fetchUsers({ sort_field: field, sort_order: order });
  }

  function resetState() {
    users.value = [];
    loading.value = false;
    error.value = null;
    currentPage.value = 1;
    totalPages.value = 1;
    totalUsers.value = 0;
    perPage.value = 15;
  }

  return {
    // State
    users,
    loading,
    error,
    currentPage,
    totalPages,
    totalUsers,
    perPage,
    
    // Getters
    usersByRole,
    sortedUsers,
    
    // Actions
    fetchUsers,
    getUsersByRole,
    searchUsers,
    setPage,
    setSorting,
    resetState
  };
});