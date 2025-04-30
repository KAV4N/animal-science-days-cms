import { type RouteLocationNormalized, type NavigationGuardNext } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

/**
 * Middleware for routes that can ONLY be accessed by non-authenticated users
 * Will always redirect authenticated users
 */
export default async (to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) => {
  const authStore = useAuthStore();

  const isAuthenticated = await authStore.isAuthenticated;
  
  if (isAuthenticated) {
    next({ name: 'home' });
  } else {
    next();
  }
};