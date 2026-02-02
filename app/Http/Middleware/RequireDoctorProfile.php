<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireDoctorProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user has doctor profile
        if (!hasDoctorProfile()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Access denied. This action requires a doctor profile.',
                    'redirect' => route('dashboard')
                ], 403);
            }
            
            session()->flash('error', 'Access denied. This page is for doctors only.');
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
