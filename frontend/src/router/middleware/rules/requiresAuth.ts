import { type RouteLocationNormalized, type NavigationGuardNext } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

/**
 * Middleware for routes that require authentication
 * Redirects to login if user is not authenticated
 */
export default async (to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) => {
  const authStore = useAuthStore();
  
  const isAuthenticated = await authStore.isAuthenticated;
  
  if (!isAuthenticated) {
    next({ name: 'login' });
  } else {
    next();
  }
};