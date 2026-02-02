<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if maintenance mode is enabled
        $maintenanceFile = storage_path('framework/maintenance.json');
        if (file_exists($maintenanceFile)) {
            // Allow access if user is authenticated and is system_admin
            if (Auth::check() && Auth::user()->hasRole('system_admin')) {
                return $next($request);
            }

            // Allow access to login page, login POST, and logout for admin to authenticate
            if ($request->routeIs('login') || $request->routeIs('logout') || 
                ($request->is('login') && $request->isMethod('POST'))) {
                return $next($request);
            }

            // Redirect to maintenance page for all other users
            return response()->view('errors.maintenance', [], 503);
        }

        return $next($request);
    }
}
