import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

import Dashboard from '@/views/Dashboard.vue';
import ConferenceManagement from '@/views/dashboard/ConferenceManagement.vue';
import UserManagement from '@/views/dashboard/UserManagement.vue';
import Site from '@/views/Site.vue';
import LoginCard from '@/components/auth/LoginCard.vue';
import ChangePasswordCard from '@/components/auth/ChangePasswordCard.vue';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    component: Site,
  },
  {
    path: '/dashboard',
    component: Dashboard,
    meta: { requiresAuth: true },
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
  },
  {
    path: '/login',
    name: 'login',
    component: LoginCard,
    meta: { hideIfAuth: true }
  },
  {
    path: '/change-password',
    name: 'change-password',
    component: ChangePasswordCard,
    meta: { requiresAuth: true }
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
  const hideIfAuth = to.matched.some(record => record.meta.hideIfAuth);

  // Check if we need to redirect based on authentication status
  if (requiresAuth && !authStore.isAuthenticated) {
    // User not authenticated but route requires auth
    await authStore.checkAuth();

    if (!authStore.isAuthenticated) {
      next({ name: 'login' });
      return;
    }
  }

  // Check if user is on first login and needs to change password
  if (authStore.isAuthenticated && authStore.user?.first_login && to.name !== 'change-password') {
    next({ name: 'change-password' });
    return;
  }

  // Check if route should be hidden when authenticated (like login page)
  if (hideIfAuth && authStore.isAuthenticated) {
    next({ name: 'dashboard' });
    return;
  }

  next();
});

export default router;

