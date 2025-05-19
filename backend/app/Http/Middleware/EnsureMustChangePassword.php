<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponse;

class EnsureMustChangePassword
{
    use ApiResponse;

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && !$user->must_change_password) {
            return $this->errorResponse('Password change not required for this user', 403);
        }
        
        return $next($request);
    }
}
