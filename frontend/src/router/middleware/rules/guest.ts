import { type RouteLocationNormalized, type NavigationGuardNext } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

/**
 * Middleware for routes that can be accessed by non-authenticated users
 * but will redirect authenticated users to dashboard
 */
export default (to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) => {
  const authStore = useAuthStore();
  
  if (authStore.isAuthenticated) {
    next({ name: 'dashboard' });
  } else {
    next();
  }
};