<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

     if (!auth()->check() || !auth()->user()->is_admin) {
            // Log them out and redirect to login with an error message
            auth()->logout();
            
            return redirect()->route('login')
                ->withErrors(['email' => 'You do not have admin access.']);
        }

        return $next($request);
    }
}
