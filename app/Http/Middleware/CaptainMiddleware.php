<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptainMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isCaptain()) {
            return response()->json([
                'success' => false,
                'message' => 'Captain access is required',
            ], 403);
        }

        return $next($request);
    }
}
