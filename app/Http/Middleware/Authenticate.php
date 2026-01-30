<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Misafir kullanıcıyı yönlendir
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // URL'e göre doğru login sayfasına yönlendir
        if ($request->is('courier/*') || $request->is('courier')) {
            return route('courier.login');
        }

        return route('panel.login');
    }
}
