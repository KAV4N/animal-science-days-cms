import type { MiddlewareContext } from '@/router/middleware/middleware-pipeline';

/**
 * Middleware for routes that can be accessed by non-authenticated users
 * but will redirect authenticated users to dashboard
 */
export default function guest({ next, authStore }: MiddlewareContext): void {
  if (authStore?.isAuthenticated) {
    next({ name: 'Dashboard' });
  } else {
    next();
  }
}