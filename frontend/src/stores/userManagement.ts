// stores/userManagement.ts
import { defineStore } from 'pinia';

// Define the User interface
export interface User {
  id: string;
  name: string;
  email: string;
  role: string;
  createdAt: Date;
  updatedAt: Date;
}

export interface CreateUserPayload {
  name: string;
  email: string;
  role: string;
  password: string;
}

export interface UpdateUserPayload {
  name?: string;
  email?: string;
  role?: string;
  password?: string;
}

// Some dummy data for testing
const dummyUsers: User[] = [
  {
    id: '1',
    name: 'Admin User',
    email: 'admin@example.com',
    role: 'super_admin',
    createdAt: new Date('2023-09-15'),
    updatedAt: new Date('2023-10-20')
  },
  {
    id: '2',
    name: 'John Doe',
    email: 'john@example.com',
    role: 'admin',
    createdAt: new Date('2023-10-18'),
    updatedAt: new Date('2023-11-05')
  },
  {
    id: '3',
    name: 'Jane Smith',
    email: 'jane@example.com',
    role: 'editor',
    createdAt: new Date('2023-11-10'),
    updatedAt: new Date('2023-12-01')
  },
  {
    id: '4',
    name: 'Robert Brown',
    email: 'robert@example.com',
    role: 'editor',
    createdAt: new Date('2024-01-05'),
    updatedAt: new Date('2024-01-20')
  },
  {
    id: '5',
    name: 'Lisa Williams',
    email: 'lisa@example.com',
    role: 'admin',
    createdAt: new Date('2024-02-10'),
    updatedAt: new Date('2024-02-28')
  }
];

export const useUserManagementStore = defineStore('userManagement', {
  state: () => ({
    users: [...dummyUsers],
    loading: false,
    error: null as string | null
  }),
  
  getters: {
    getUserById: (state) => (id: string) => {
      return state.users.find(user => user.id === id);
    }
  },
  
  actions: {
    fetchUsers() {
      this.loading = true;
      
      try {
        // In a real app, this would be an API call
        // For now, just use the dummy data
        this.users = [...dummyUsers];
        this.loading = false;
        return this.users;
      } catch (error: any) {
        this.error = error.message || 'Failed to fetch users';
        this.loading = false;
        throw new Error(this.error);
      }
    },
    
    createUser(payload: CreateUserPayload) {
      try {
        // In a real app, this would be an API call
        const newUser: User = {
          id: Date.now().toString(), // Generate a simple ID
          name: payload.name,
          email: payload.email,
          role: payload.role,
          createdAt: new Date(),
          updatedAt: new Date()
        };
        
        this.users.push(newUser);
        return newUser;
      } catch (error: any) {
        this.error = error.message || 'Failed to create user';
        throw new Error(this.error);
      }
    },
    
    updateUser(id: string, payload: UpdateUserPayload) {
      try {
        const index = this.users.findIndex(user => user.id === id);
        
        if (index !== -1) {
          // Update only the provided fields
          const updatedUser = {
            ...this.users[index],
            ...payload,
            updatedAt: new Date()
          };
          
          this.users[index] = updatedUser;
          return updatedUser;
        } else {
          throw new Error('User not found');
        }
      } catch (error: any) {
        this.error = error.message || 'Failed to update user';
        throw new Error(this.error);
      }
    },
    
    deleteUser(id: string) {
      try {
        const index = this.users.findIndex(user => user.id === id);
        
        if (index !== -1) {
          this.users.splice(index, 1);
          return true;
        } else {
          throw new Error('User not found');
        }
      } catch (error: any) {
        this.error = error.message || 'Failed to delete user';
        throw new Error(this.error);
      }
    },
    
    deleteMultipleUsers(ids: string[]) {
      try {
        ids.forEach(id => {
          const index = this.users.findIndex(user => user.id === id);
          if (index !== -1) {
            this.users.splice(index, 1);
          }
        });
        return true;
      } catch (error: any) {
        this.error = error.message || 'Failed to delete users';
        throw new Error(this.error);
      }
    }
  }
});