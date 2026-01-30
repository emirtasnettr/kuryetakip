<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * CSRF doğrulamasından muaf URL'ler
     */
    protected $except = [
        // API endpoint'leri zaten Sanctum ile korunuyor
    ];
}
