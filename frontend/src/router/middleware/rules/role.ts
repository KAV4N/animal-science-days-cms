import type { MiddlewareContext } from '@/router/middleware/middleware-pipeline';

/**
 * Middleware factory for routes that require specific user roles
 * @param requiredRole - The role required to access the route
 */
export default function role(requiredRole: string) {
  return function({ next, authStore }: MiddlewareContext): void {
    if (authStore?.hasRole(requiredRole)) {
      next();
    } else {
      next({ name: 'Dashboard' });
    }
  };
}