// src/router/index.ts
import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

import Home from '@/views/Home.vue';
import Login from '@/views/Login.vue';
import Register from '@/views/Register.vue';
import Dashboard from '@/views/Dashboard.vue';
import EditorPage from '@/views/EditorPage.vue';
import AdminPage from '@/views/AdminPage.vue';
import SuperAdminPage from '@/views/SuperAdminPage.vue';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    name: 'home',
    component: Home
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { 
      requiresGuest: true 
    }
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { 
      requiresGuest: true 
    }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { 
      requiresAuth: true 
    }
  },
  {
    path: '/editor',
    name: 'editor',
    component: EditorPage,
    meta: { 
      requiresAuth: true,
      permission: 'access.editor'
    }
  },
  {
    path: '/admin',
    name: 'admin',
    component: AdminPage,
    meta: { 
      requiresAuth: true,
      permission: 'access.admin'
    }
  },
  {
    path: '/super-admin',
    name: 'super-admin',
    component: SuperAdminPage,
    meta: { 
      requiresAuth: true,
      permission: 'access.super_admin'
    }
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
  
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!authStore.isAuthenticated) {
      return next({ name: 'login' });
    }
    
    if (to.meta.permission && !authStore.permissions.includes(to.meta.permission as string)) {
      return next({ name: 'dashboard' });
    }
  }
  
  if (to.matched.some(record => record.meta.requiresGuest) && authStore.isAuthenticated) {
    return next({ name: 'dashboard' });
  }
  
  next();
});

export default router;