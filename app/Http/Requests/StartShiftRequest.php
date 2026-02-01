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
            
            // Fotoğraf (zorunlu)
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,heic,heif|max:25600', // Max 25MB
            
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
            'photo.required' => 'Başlangıç fotoğrafı zorunludur.',
            'photo.image' => 'Yüklenen dosya bir görsel olmalıdır.',
            'photo.mimes' => 'Fotoğraf formatı jpeg, png, jpg, gif veya heic olmalıdır.',
            'photo.max' => 'Fotoğraf boyutu en fazla 25MB olabilir.',
        ];
    }
}
