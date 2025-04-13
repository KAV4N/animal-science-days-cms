import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import middleware from './middleware';

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
    beforeEnter: middleware.requiresAuth
  },
  {
    path: '/editor',
    name: 'editor',
    component: EditorPage,
    beforeEnter: middleware.permission('access.editor')
  },
  {
    path: '/admin',
    name: 'admin',
    component: AdminPage,
    //beforeEnter: middleware.permission('access.admin')
  },
  {
    path: '/super-admin',
    name: 'super-admin',
    component: SuperAdminPage,
    beforeEnter: middleware.roleAndPermission('super_admin', 'access.super_admin')
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