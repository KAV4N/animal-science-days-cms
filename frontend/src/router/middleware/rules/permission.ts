import type { MiddlewareContext } from '@/router/middleware/middleware-pipeline';

/**
 * Middleware factory for routes that require specific permissions
 * @param requiredPermission - The permission required to access the route
 */
export default function permission(requiredPermission: string) {
  return function({ next, authStore }: MiddlewareContext): void {  
    if (authStore?.hasPermission(requiredPermission)) {
      next();
    } else {
      next({ name: 'Dashboard' });
    }
  };
}
