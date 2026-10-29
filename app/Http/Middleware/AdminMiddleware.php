<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            if (!$request->expectsJson()) {
                return redirect()->route('login')->withErrors([
                    'email' => 'يرجى تسجيل الدخول بحساب مدير للوصول إلى لوحة الإدارة.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        if (!Auth::user()->isAdmin()) {
            if (!$request->expectsJson()) {
                Auth::logout();

                return redirect()->route('login')->withErrors([
                    'email' => 'هذا الحساب لا يملك صلاحية دخول لوحة الإدارة.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin only.'
            ], 403);
        }

        return $next($request);
    }
} 
