<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTeacher
{
    public function handle(Request $request, Closure $next)
    {
        if (auth('api')->user()?->role !== 'teacher') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}