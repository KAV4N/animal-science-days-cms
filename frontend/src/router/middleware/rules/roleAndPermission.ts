import { type RouteLocationNormalized, type NavigationGuardNext } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

/**
 * Middleware factory that checks both role and permission
 * @param role - The required role
 * @param permission - The required permission
 */
export default (role: string, permission: string) => {
  return (to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) => {
    const authStore = useAuthStore();
    
    if (!authStore.isAuthenticated) {
      return next({ name: 'login' });
    }
    
    if (authStore.hasRole(role) && authStore.hasPermission(permission)) {
      next();
    } else {
      next({ name: 'dashboard' });
    }
  };
};