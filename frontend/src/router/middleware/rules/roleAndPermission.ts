import type { MiddlewareContext } from '@/router/middleware/middleware-pipeline';

/**
 * Middleware factory that checks both role and permission
 * @param requiredRole - The required role
 * @param requiredPermission - The required permission
 */
export default function roleAndPermission(requiredRole: string, requiredPermission: string) {
  return function({ next, authStore }: MiddlewareContext): void {
    if (authStore?.hasRole(requiredRole) && authStore.hasPermission(requiredPermission)) {
      next();
    } else {
      next({ name: 'Dashboard' });
    }
  };
}