<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Raporlanmayacak exception tipleri
     */
    protected $dontReport = [
        //
    ];

    /**
     * Loglara kaydedilmeyecek input alanları
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Exception handler'ları kaydet
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // API istekleri için özel 404 yanıtı
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kayıt bulunamadı.',
                ], 404);
            }
        });
    }

    /**
     * Kimlik doğrulama hatalarında yönlendirme
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Kimlik doğrulama gerekli.',
            ], 401);
        }

        // URL'e göre yönlendir
        if ($request->is('courier/*') || $request->is('courier')) {
            return redirect()->guest(route('courier.login'));
        }

        return redirect()->guest(route('panel.login'));
    }
}
