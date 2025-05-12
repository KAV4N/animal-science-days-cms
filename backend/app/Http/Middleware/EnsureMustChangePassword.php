<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponse;

class EnsureMustChangePassword
{
    use ApiResponse;

    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->must_change_password) {
            return $this->errorResponse('You have already changed your password. This route is no longer accessible.', 403);
        }

        return $next($request);
    }
}
