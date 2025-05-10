import { createRouter, createWebHistory, type RouteRecordRaw, type NavigationGuardNext, type RouteLocationNormalized } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import middlewarePipeline from './middleware/middleware-pipeline';


import Login from '@/views/auth/Login.vue';
import Register from '@/views/auth/Register.vue';
import Dashboard from '@/views/Dashboard.vue';
import ConferenceManagement from '@/views/dashboard/ConferenceManagement.vue';
import UserManagement from '@/views/dashboard/UserManagement.vue';
import Site from '@/views/Site.vue';

import middleware from './middleware';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    component: Site,
    name: 'home'
  },
  {
    path: '/login',
    component: Login,
    name: 'login',
    meta: {
      middleware: [middleware.guestOnly]
    }
  },
  {
    path: '/register',
    component: Register,
    name: 'register',
    meta: {
      middleware: [middleware.guestOnly]
    }
  },
  {
    path: '/dashboard',
    component: Dashboard,
    name: 'dashboard',
    meta: {
      middleware: [middleware.requiresAuth]
    },
    children: [
      {
        path: '',
        redirect: { name: 'ConferenceManagement' }
      },
      {
        path: 'conferences',
        name: 'ConferenceManagement',
        component: ConferenceManagement
      },
      {
        path: 'users',
        name: 'UserManagement',
        component: UserManagement,
        meta: {
          middleware: [middleware.permission('access.admin')]
        }
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
  if (!to.meta.middleware) {
    return next();
  }
  const middleware = Array.isArray(to.meta.middleware) 
    ? to.meta.middleware 
    : [to.meta.middleware];

  const context = {
    to,
    from,
    next,
    authStore
  };

  return middleware[0]({
    ...context,
    next: middlewarePipeline(context, middleware, 1)
  });
});

export default router;