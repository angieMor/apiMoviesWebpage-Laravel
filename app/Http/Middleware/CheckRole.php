<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
# Custom middleware, to check the user by its role with the JWT
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    
    public function handle($request, Closure $next, $role)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user && $user->role === $role) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
