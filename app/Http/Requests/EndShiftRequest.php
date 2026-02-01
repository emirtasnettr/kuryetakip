<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Vardiya Bitirme Request Validation
 */
class EndShiftRequest extends FormRequest
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
            
            // Paket sayısı (zorunlu)
            'package_count' => 'required|integer|min:0|max:9999',
            
            // Fotoğraf (zorunlu)
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,heic,heif|max:25600', // Max 25MB
            
            // Notlar (opsiyonel)
            'notes' => 'nullable|string|max:1000',
            
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
            'package_count.required' => 'Paket sayısı zorunludur.',
            'package_count.integer' => 'Paket sayısı bir sayı olmalıdır.',
            'package_count.min' => 'Paket sayısı 0\'dan küçük olamaz.',
            'photo.required' => 'Bitiş fotoğrafı zorunludur.',
            'photo.image' => 'Yüklenen dosya bir görsel olmalıdır.',
            'photo.mimes' => 'Fotoğraf formatı jpeg, png, jpg, gif veya heic olmalıdır.',
            'photo.max' => 'Fotoğraf boyutu en fazla 25MB olabilir.',
        ];
    }
}
