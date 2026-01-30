<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base Controller
 * 
 * Tüm controller'ların miras aldığı temel sınıf.
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Başarılı yanıt döndür
     */
    protected function success($data = null, string $message = 'İşlem başarılı', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Hata yanıtı döndür
     */
    protected function error(string $message = 'Bir hata oluştu', int $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Yetkilendirme hatası döndür
     */
    protected function unauthorized(string $message = 'Bu işlem için yetkiniz bulunmuyor')
    {
        return $this->error($message, 403);
    }

    /**
     * Bulunamadı hatası döndür
     */
    protected function notFound(string $message = 'Kayıt bulunamadı')
    {
        return $this->error($message, 404);
    }
}
