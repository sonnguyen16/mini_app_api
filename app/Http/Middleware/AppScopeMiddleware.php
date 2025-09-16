<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppScopeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Lấy app_id từ header X-App-Id
        $appId = $request->header('X-App-Id');
        
        if (!$appId) {
            return response()->json([
                'success' => false,
                'error' => 'X-App-Id header is required'
            ], 400);
        }

        // Lưu app_id vào request để sử dụng trong controller
        $request->merge(['app_id' => $appId]);
        
        return $next($request);
    }
}
