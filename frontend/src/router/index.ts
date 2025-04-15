import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import middleware from './middleware';

import Login from '@/views/auth/Login.vue';
import Register from '@/views/auth/Register.vue';
import Dashboard from '@/views/Dashboard.vue';


const routes: Array<RouteRecordRaw> = [

  {
    path: '/login',
    name: 'login',
    component: Login,
    beforeEnter: middleware.guest
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    beforeEnter: middleware.guest
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    //beforeEnter: middleware.requiresAuth
  },
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