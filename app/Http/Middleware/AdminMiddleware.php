<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->accessRole === 'Admin') {
            return $next($request);
        }

        // If not admin, redirect or abort
        // You can redirect to a specific route like 'home' or abort with a 403 Forbidden error
        // abort(403, 'Unauthorized action.');
        return redirect('/')->with('error', 'You do not have permission to access this page.'); // Redirect to home or login
    }
}
