<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra xem session có key 'user' không
        if (!Auth::check()) {
            return redirect()->route('login.auth');
        }
        return $next($request);
    }
    // protected function redirectTo(Request $request)
    // {
    //     if(!$request->session()->has('user')){
    //         return redirect()->route('login.auth');
    //     }
    // }

}
