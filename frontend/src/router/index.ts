import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import middlewarePipeline from './middleware/middleware-pipeline';

import Dashboard from '@/views/Dashboard.vue';
import ConferenceManagement from '@/views/dashboard/ConferenceManagement.vue';
import UserManagement from '@/views/dashboard/UserManagement.vue';
import Site from '@/views/Site.vue';
import ConferenceView from '@/views/site/ConferenceView.vue';

import middleware from './middleware';
import ChangePassword from '@/views/auth/ChangePassword.vue';
import Login from '@/views/auth/Login.vue';
import ConferenceEditorView from '@/views/dashboard/ConferenceEditorView.vue';
import ConferencesByDecade from '@/views/site/ConferencesByDecade.vue';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    component: Site,
    children: [
      {
        path: '',
        name: 'HomePage',
        component: ConferenceView,
        props: { slug: '', pageSlug: '' } 
      },
      {
        path: 'conferences/:slug',
        name: 'conference',
        component: ConferenceView,
        props: (route) => ({ 
          slug: route.params.slug as string, 
          pageSlug: '' 
        })
      },
      {
        path: 'conferences/:slug/pages/:pageSlug',
        name: 'conferencePage',
        component: ConferenceView,
        props: (route) => ({ 
          slug: route.params.slug as string, 
          pageSlug: route.params.pageSlug as string 
        })
      },
      {
        path: 'archive',
        name: 'archive',
        component: ConferencesByDecade
      },
    ]
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: {
      middleware: [middleware.guestOnly]
    }
  },
  {
    path: '/change-password',
    name: 'ChangePassword',
    component: ChangePassword,
    meta: {
      middleware: [middleware.requiresUnchangedPassword]
    }
  },
  {
    path: '/dashboard',
    component: Dashboard,
    name: 'Dashboard',
    meta: {
      middleware: [middleware.requiresAuth]
    },
    children: [
      {
        path: '',
        name: 'DashboardHome',
        redirect: { name: 'ConferenceManagement' }
      },
      {
        path: 'conferences',
        name: 'ConferenceManagement',
        component: ConferenceManagement
      },
      {
        path: 'conferences/:id/edit',
        name: 'ConferenceEdit',
        component: ConferenceEditorView,
        props: true
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
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  }
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