import type { MiddlewareContext } from '@/router/middleware/middleware-pipeline';

/**
 * Middleware for routes that require authentication
 * Redirects to login if user is not authenticated
 */
export default function requiresAuth({ next, authStore }: MiddlewareContext): void {
  if (!authStore?.isAuthenticated) {
    next({ name: 'Login' });
  } 
  else if (authStore?.user?.must_change_password) {
    next({ name: 'ChangePassword' });
  } 
  else {
    next();
  }
}
