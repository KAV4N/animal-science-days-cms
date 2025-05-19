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

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if ($user && $user->must_change_password) {
            return $this->errorResponse('Password change required before accessing this resource', 403, [
                'must_change_password' => true
            ]);
        }
        
        return $next($request);
    }
}
