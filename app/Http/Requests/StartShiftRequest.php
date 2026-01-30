<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Vardiya Başlatma Request Validation
 */
class StartShiftRequest extends FormRequest
{
    /**
     * Yetki kontrolü (Policy'de yapılıyor)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation kuralları
     */
    public function rules(): array
    {
        return [
            // Konum bilgileri (zorunlu)
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|integer|min:0',
            'address' => 'nullable|string|max:500',
            
            // Fotoğraf (opsiyonel)
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // Max 10MB
            
            // Cihaz bilgileri (opsiyonel, loglama için)
            'device_id' => 'nullable|string|max:100',
            'device_model' => 'nullable|string|max:100',
            'os_version' => 'nullable|string|max:50',
            'app_version' => 'nullable|string|max:20',
        ];
    }

    /**
     * Validation hata mesajları
     */
    public function messages(): array
    {
        return [
            'latitude.required' => 'Konum bilgisi (enlem) zorunludur.',
            'latitude.between' => 'Geçersiz enlem değeri.',
            'longitude.required' => 'Konum bilgisi (boylam) zorunludur.',
            'longitude.between' => 'Geçersiz boylam değeri.',
            'photo.image' => 'Yüklenen dosya bir görsel olmalıdır.',
            'photo.max' => 'Fotoğraf boyutu en fazla 10MB olabilir.',
        ];
    }
}
