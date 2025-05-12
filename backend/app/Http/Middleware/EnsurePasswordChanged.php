<?php
// app/Http/Middleware/EnsurePasswordChanged.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponse;

class EnsurePasswordChanged
{

    use ApiResponse; 

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password) {
            return $this->errorResponse('You must change your password before accessing this resource.', 403);
        }

        return $next($request);
    }
}
