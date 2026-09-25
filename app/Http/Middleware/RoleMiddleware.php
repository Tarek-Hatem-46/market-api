<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    use ApiResponse;
    public function handle(Request $request, Closure $next,...$roles): Response
    {
        $user=auth()->user();
        if(!$user ||! in_array($user->role,$roles)){
            return self::error("Forbidden",null,403);
        }
        return $next($request);
        
    }
}
