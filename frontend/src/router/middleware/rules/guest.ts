import { type RouteLocationNormalized, type NavigationGuardNext } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

/**
 * Middleware for routes that can be accessed by non-authenticated users
 * but will redirect authenticated users to dashboard
 */
export default async (to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) => {
  const authStore = useAuthStore();

  const isAuthenticated = await authStore.isAuthenticated;
  
  if (isAuthenticated) {
    next({ name: 'dashboard' });
  } else {
    next();
  }
};