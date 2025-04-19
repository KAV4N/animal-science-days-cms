import { type RouteLocationNormalized, type NavigationGuardNext } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

/**
 * Middleware for routes that can ONLY be accessed by non-authenticated users
 * Will always redirect authenticated users
 */
export default (to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) => {
  const authStore = useAuthStore();
  
  if (authStore.isAuthenticated) {
    next({ name: 'home' });
  } else {
    next();
  }
};