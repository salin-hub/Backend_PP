<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUser
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role !== 'user') {
            return response()->json(['error' => 'Access denied. Users only.'], 403);
        }

        return $next($request);
    }
}
