import type { MiddlewareContext } from '@/router/middleware/middleware-pipeline';

/**
 * Middleware for routes that can ONLY be accessed by non-authenticated users
 * Will always redirect authenticated users
 */
export default function guestOnly({ next, authStore }: MiddlewareContext): void {
  if (authStore?.isAuthenticated) {
    next({ name: 'home' });
  } else {
    next();
  }
}