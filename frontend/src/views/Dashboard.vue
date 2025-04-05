<template>
    <div class="dashboard-container">
      <div class="header">
        <h1>Dashboard</h1>
        <button @click="logout" class="logout-button">Logout</button>
      </div>
      
      <div v-if="authStore.isLoading" class="loading">Loading...</div>
      
      <div v-else class="user-info">
        <h2>Welcome, {{ authStore.getUser?.name }}</h2>
        <p>Email: {{ authStore.getUser?.email }}</p>
      </div>
      
      <div class="protected-data">
        <h3>Protected Data</h3>
        <button @click="fetchProtectedData" :disabled="loadingData">
          {{ loadingData ? 'Loading...' : 'Fetch Protected Data' }}
        </button>
        
        <div v-if="protectedData" class="data-display">
          <p>{{ protectedData.message }}</p>
          <p>User ID: {{ protectedData.user_id }}</p>
          <p>Example: {{ protectedData.data.example }}</p>
          <p>Timestamp: {{ protectedData.data.timestamp }}</p>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { defineComponent, ref } from 'vue';
  import { useAuthStore } from '../stores/auth';
  import api from '../utils/api';
  
  export default defineComponent({
    name: 'DashboardView',
    
    setup() {
      const authStore = useAuthStore();
      const protectedData = ref(null);
      const loadingData = ref(false);
      
      // Fetch protected data from the API
      const fetchProtectedData = async () => {
        loadingData.value = true;
        try {
          const response = await api.get('/protected-data');
          protectedData.value = response.data;
        } catch (error) {
          console.error('Error fetching protected data:', error);
          alert('Failed to fetch data. You might need to login again.');
        } finally {
          loadingData.value = false;
        }
      };
      
      // Logout function
      const logout = async () => {
        await authStore.logout();
      };
      
      return {
        authStore,
        protectedData,
        loadingData,
        fetchProtectedData,
        logout
      };
    }
  });
  </script>
  
  <style scoped>
  .dashboard-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
  }
  
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }
  
  .logout-button {
    padding: 8px 16px;
    background-color: #f44336;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }
  
  .user-info {
    margin-bottom: 30px;
    padding: 15px;
    background-color: #f5f5f5;
    border-radius: 4px;
  }
  
  .protected-data {
    margin-top: 20px;
  }
  
  .protected-data button {
    padding: 8px 16px;
    background-color: #2196F3;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-bottom: 15px;
  }
  
  .protected-data button:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
  }
  
  .data-display {
    padding: 15px;
    background-color: #e3f2fd;
    border-radius: 4px;
    margin-top: 15px;
  }
  </style>