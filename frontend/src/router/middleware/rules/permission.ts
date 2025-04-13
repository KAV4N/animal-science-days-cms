import { type RouteLocationNormalized, type NavigationGuardNext } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

/**
 * Middleware factory for routes that require specific permissions
 * @param requiredPermission - The permission required to access the route
 */
export default (requiredPermission: string) => {
  return (to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) => {
    const authStore = useAuthStore();
    
    if (!authStore.isAuthenticated) {
      return next({ name: 'login' });
    }
    
    if (authStore.hasPermission(requiredPermission)) {
      next();
    } else {
      next({ name: 'dashboard' });
    }
  };
};