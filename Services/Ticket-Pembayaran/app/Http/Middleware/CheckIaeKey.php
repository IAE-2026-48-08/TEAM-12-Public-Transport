<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIaeKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nimMahasiswa = config('services.iae.nim', '102022400251');
        
        if ($request->header('X-IAE-KEY') !== $nimMahasiswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Invalid X-IAE-KEY.',
                'errors' => null
            ], 401)->header('Content-Type', 'application/json; charset=utf-8');
        }

        return $next($request);
    }
}
