import type { MiddlewareContext } from '@/router/middleware/middleware-pipeline';

/**
 * Middleware for routes that are only accessible
 * by authenticated users who have NOT changed their password.
 * All others get redirected.
 */
export default function requiresUnchangedPassword({ next, authStore }: MiddlewareContext): void {
  if (!authStore?.user?.must_change_password) {
    next({ name: 'HomePage' });
  } 
  else {
    next();
  }
}