<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLogged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login')->withErrors([
                'usuario' => 'Debes iniciar sesión.'
            ]);
        }
        return $next($request);
    }
}
