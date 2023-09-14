<?php

namespace App\Http\Middleware;
/*
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     /
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
*/

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Authenticate
{
    public function handle(Request $request, Closure $next)
    {
        //return $next($request);
        $tokenHeader = [ "Authorization" => $request -> header("Authorization")];
        
        $response = Http::withHeaders($tokenHeader)->get(getenv("API_AUTH_URL") . "/api/v1/validate");

        
        if($response -> successful())
            return $next($request);
        
        return response(['message' => 'Not Allowed'], 403);
    }
}
