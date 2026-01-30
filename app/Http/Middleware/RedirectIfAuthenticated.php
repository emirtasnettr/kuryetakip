<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Kurye ise kurye ana sayfasına
                if ($user->isCourier()) {
                    return redirect()->route('courier.home');
                }

                // Panel kullanıcısı ise dashboard'a
                if ($user->canAccessPanel()) {
                    return redirect()->route('panel.dashboard');
                }

                // Varsayılan
                return redirect('/');
            }
        }

        return $next($request);
    }
}
