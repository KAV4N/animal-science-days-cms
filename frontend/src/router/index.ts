import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import middlewarePipeline from './middleware/middleware-pipeline';

import Dashboard from '@/views/Dashboard.vue';
import ConferenceManagement from '@/views/dashboard/ConferenceManagement.vue';
import UserManagement from '@/views/dashboard/UserManagement.vue';
import Site from '@/views/Site.vue';
import ConferenceView from '@/views/site/ConferenceView.vue';
import PreviewConferenceView from '@/views/site/PreviewConferenceView.vue';
import NotFoundPage from '@/views/NotFoundPage.vue'; // Add this import

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
  // Preview routes (requires authentication)
  {
    path: '/preview',
    name: 'Preview',
    meta: {
      middleware: [middleware.requiresAuth]
    },
    children: [
      {
        path: 'conferences/:slug',
        name: 'previewConference',
        component: PreviewConferenceView,
        props: (route) => ({ 
          slug: route.params.slug as string, 
          pageSlug: '' 
        }),
        beforeEnter: (to, from, next) => {
          // Open in new tab
          if (from.name && from.name.toString().startsWith('Dashboard')) {
            window.open(to.fullPath, '_blank');
            next(false);
          } else {
            next();
          }
        }
      },
      {
        path: 'conferences/:slug/pages/:pageSlug',
        name: 'previewConferencePage',
        component: PreviewConferenceView,
        props: (route) => ({ 
          slug: route.params.slug as string, 
          pageSlug: route.params.pageSlug as string 
        }),
        beforeEnter: (to, from, next) => {
          // Open in new tab
          if (from.name && from.name.toString().startsWith('Dashboard')) {
            window.open(to.fullPath, '_blank');
            next(false);
          } else {
            next();
          }
        }
      }
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
  },
  // 404 Catch-all route - MUST be last!
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFoundPage,
    meta: {
      title: 'Page Not Found'
    }
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
  
  // Set page title for 404 pages
  if (to.meta.title) {
    document.title = `${to.meta.title} | Your App Name`;
  }
  
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