import { type RouteLocationNormalized, type NavigationGuardNext } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

/**
 * Middleware factory for routes that require specific user roles
 * @param requiredRole - The role required to access the route
 */
export default (requiredRole: string) => {
  return async (to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) => {
    const authStore = useAuthStore();
    
    const isAuthenticated = await authStore.isAuthenticated();
    
    if (!isAuthenticated) {
      return next({ name: 'login' });
    }
    
    if (authStore.hasRole(requiredRole)) {
      next();
    } else {
      next({ name: 'dashboard' });
    }
  };
};