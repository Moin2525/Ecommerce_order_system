<?php
// app/Http/Middleware/CustomerMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if (!auth()->user()->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Customer privileges required.'
            ], 403);
        }

        return $next($request);
    }
}
