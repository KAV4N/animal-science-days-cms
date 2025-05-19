import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router';
import type { useAuthStore } from '@/stores/authStore';

export interface MiddlewareContext {
  to: RouteLocationNormalized;
  from: RouteLocationNormalized;
  next: NavigationGuardNext;
  authStore?: ReturnType<typeof useAuthStore>;
}

export type Middleware = (context: MiddlewareContext) => void;

/**
 * Middleware pipeline for processing multiple middleware in sequence
 * @param context - The navigation context
 * @param middleware - Array of middleware functions to process
 * @param index - Current middleware index
 * @returns Navigation function for the next middleware
 */
export default function middlewarePipeline(
  context: MiddlewareContext, 
  middleware: Middleware[], 
  index: number
): NavigationGuardNext {
  const nextMiddleware = middleware[index];
  if (!nextMiddleware) {
    return context.next;
  }
  return () => {
    const nextPipeline = middlewarePipeline(context, middleware, index + 1);
    nextMiddleware({
      ...context,
      next: nextPipeline
    });
  };
}