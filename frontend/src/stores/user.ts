// src/stores/user.ts
import { defineStore } from 'pinia';
import apiService from '@/services/apiService';

export interface User {
  id: string;
  name: string;
  email: string;
  role: 'super_admin' | 'admin' | 'editor';
  createdAt: Date;
  updatedAt: Date;
}

interface UserState {
  users: User[];
  loading: boolean;
  error: string | null;
}

export const useUserStore = defineStore('user', {
  state: (): UserState => ({
    users: [],
    loading: false,
    error: null
  }),
  
  getters: {
    // Filter users by role
    admins: (state) => state.users.filter(user => user.role === 'admin'),
    editors: (state) => state.users.filter(user => user.role === 'editor'),
    superAdmins: (state) => state.users.filter(user => user.role === 'super_admin'),
    
    // Get a specific user by ID
    getUserById: (state) => {
      return (id: string) => state.users.find(user => user.id === id);
    }
  },
  
  actions: {
    // Mock data for development
    async fetchUsers() {
      this.loading = true;
      this.error = null;
      
      try {
        // In a real app, this would be an API call
        // const response = await apiService.get('/api/users');
        // this.users = response.data;
        
        // For now, we'll use mock data
        // Add a small delay to simulate network latency
        await new Promise(resolve => setTimeout(resolve, 500));
        
        this.users = [
          {
            id: '1',
            name: 'Super Admin',
            email: 'superadmin@example.com',
            role: 'super_admin',
            createdAt: new Date('2024-01-01'),
            updatedAt: new Date('2024-01-01')
          },
          {
            id: '2',
            name: 'Admin One',
            email: 'admin1@example.com',
            role: 'admin',
            createdAt: new Date('2024-02-01'),
            updatedAt: new Date('2024-02-01')
          },
          {
            id: '3',
            name: 'Admin Two',
            email: 'admin2@example.com',
            role: 'admin',
            createdAt: new Date('2024-02-15'),
            updatedAt: new Date('2024-02-15')
          },
          {
            id: '4',
            name: 'Editor One',
            email: 'editor1@example.com',
            role: 'editor',
            createdAt: new Date('2024-03-01'),
            updatedAt: new Date('2024-03-01')
          },
          {
            id: '5',
            name: 'Editor Two',
            email: 'editor2@example.com',
            role: 'editor',
            createdAt: new Date('2024-03-15'),
            updatedAt: new Date('2024-03-15')
          }
        ];
        
        return this.users;
      } catch (error: any) {
        this.error = error.message || 'Failed to fetch users';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async createUser(userData: Omit<User, 'id' | 'createdAt' | 'updatedAt'>) {
      this.loading = true;
      this.error = null;
      
      try {
        // In a real app, this would be an API call
        // const response = await apiService.post('/api/users', userData);
        // const newUser = response.data;
        
        // Simulate server-side creation
        await new Promise(resolve => setTimeout(resolve, 500));
        
        const newUser: User = {
          ...userData,
          id: Math.random().toString(36).substring(2, 10),
          createdAt: new Date(),
          updatedAt: new Date()
        };
        
        this.users.push(newUser);
        return newUser;
      } catch (error: any) {
        this.error = error.message || 'Failed to create user';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async updateUser(id: string, userData: Partial<Omit<User, 'id' | 'createdAt' | 'updatedAt'>>) {
      this.loading = true;
      this.error = null;
      
      try {
        // In a real app, this would be an API call
        // const response = await apiService.put(`/api/users/${id}`, userData);
        // const updatedUser = response.data;
        
        // Simulate server-side update
        await new Promise(resolve => setTimeout(resolve, 500));
        
        const index = this.users.findIndex(user => user.id === id);
        if (index === -1) {
          throw new Error('User not found');
        }
        
        const updatedUser: User = {
          ...this.users[index],
          ...userData,
          updatedAt: new Date()
        };
        
        this.users[index] = updatedUser;
        return updatedUser;
      } catch (error: any) {
        this.error = error.message || 'Failed to update user';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async deleteUser(id: string) {
      this.loading = true;
      this.error = null;
      
      try {
        // In a real app, this would be an API call
        // await apiService.delete(`/api/users/${id}`);
        
        // Simulate server-side deletion
        await new Promise(resolve => setTimeout(resolve, 500));
        
        const index = this.users.findIndex(user => user.id === id);
        if (index === -1) {
          throw new Error('User not found');
        }
        
        this.users.splice(index, 1);
        return true;
      } catch (error: any) {
        this.error = error.message || 'Failed to delete user';
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});