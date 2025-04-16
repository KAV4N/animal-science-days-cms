// src/router/index.ts 
import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import middleware from './middleware';

import Login from '@/views/auth/Login.vue';
import Register from '@/views/auth/Register.vue';

import Dashboard from '@/views/Dashboard.vue';
import Home from '../views/dashboard/Home.vue';
import ConferenceManager from '@/views/dashboard/ConferenceManager.vue';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/dashboard',
    component: Dashboard,
    children: [
      {
        path: '',
        redirect: '/dashboard/conference-manager'
      },
      {
        path: 'home',
        name: 'Home',
        component: Home
      },
      {
        path: 'conference-manager',
        name: 'ConferenceManager',
        component: ConferenceManager
      }
    ]
  }
];


const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  if (!authStore.user && localStorage.getItem('isLoggedIn') === 'true') {
    try {
      await authStore.fetchCurrentUser();
    } catch (error) {
      localStorage.removeItem('isLoggedIn');
    }
  }
  next();
});

export default router;