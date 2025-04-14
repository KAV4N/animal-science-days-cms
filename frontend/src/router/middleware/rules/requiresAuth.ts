import { type RouteLocationNormalized, type NavigationGuardNext } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

/**
 * Middleware for routes that require authentication
 * Redirects to login if user is not authenticated
 */
export default (to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) => {
  const authStore = useAuthStore();
  
  if (!authStore.isAuthenticated) {
    next({ name: 'login' });
  } else {
    next();
  }
};