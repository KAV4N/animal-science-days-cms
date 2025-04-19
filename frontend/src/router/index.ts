import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import middleware from './middleware';

import Login from '@/views/auth/Login.vue';
import Register from '@/views/auth/Register.vue';

import Dashboard from '@/views/Dashboard.vue';
import ConferenceManagement from '@/views/dashboard/ConferenceManagement.vue';
import UserManagement from '@/views/dashboard/UserManagement.vue';

import Site from '@/views/Site.vue';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    component: Site,
  },
  {
    path: '/dashboard',
    component: Dashboard,
    beforeEnter: middleware.requiresAuth,
    children: [
      {
        path: '',
        redirect: 'dashboard/conferences'
      },
      {
        path: 'conferences',
        name: 'ConferenceManagement',
        component: ConferenceManagement
      },
      {
        path: 'users',
        name: 'UserManagement',
        component: UserManagement
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
