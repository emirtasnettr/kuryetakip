<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * T.C. Kimlik Numarası Doğrulama Kuralı
 * 
 * T.C. Kimlik numarasının geçerliliğini kontrol eder:
 * - 11 hane olmalı
 * - İlk hane 0 olamaz
 * - Sadece rakamlardan oluşmalı
 * - Matematiksel doğrulama formülünü geçmeli
 */
class TurkishIdNumber implements ValidationRule
{
    /**
     * Validasyon kuralını çalıştır
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Boş değer kabul edilebilir (nullable ile kullanılabilir)
        if (empty($value)) {
            return;
        }

        // Sadece rakamlardan oluşmalı
        if (!preg_match('/^\d+$/', $value)) {
            $fail('T.C. Kimlik No sadece rakamlardan oluşmalıdır.');
            return;
        }

        // 11 hane olmalı
        if (strlen($value) !== 11) {
            $fail('T.C. Kimlik No 11 haneli olmalıdır.');
            return;
        }

        // İlk hane 0 olamaz
        if ($value[0] === '0') {
            $fail('T.C. Kimlik No\'nun ilk hanesi 0 olamaz.');
            return;
        }

        // Matematiksel doğrulama
        if (!$this->validateChecksum($value)) {
            $fail('T.C. Kimlik No geçersiz.');
            return;
        }
    }

    /**
     * T.C. Kimlik No algoritma kontrolü
     * 
     * 10. hane: ((1+3+5+7+9. haneler toplamı) * 7 - (2+4+6+8. haneler toplamı)) mod 10
     * 11. hane: (1-10. haneler toplamı) mod 10
     */
    protected function validateChecksum(string $tcno): bool
    {
        $digits = array_map('intval', str_split($tcno));

        // Tek haneler toplamı (1, 3, 5, 7, 9. pozisyonlar - 0-indexed: 0, 2, 4, 6, 8)
        $oddSum = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8];

        // Çift haneler toplamı (2, 4, 6, 8. pozisyonlar - 0-indexed: 1, 3, 5, 7)
        $evenSum = $digits[1] + $digits[3] + $digits[5] + $digits[7];

        // 10. hane kontrolü
        $tenthDigit = (($oddSum * 7) - $evenSum) % 10;
        if ($tenthDigit < 0) {
            $tenthDigit += 10;
        }
        
        if ($tenthDigit !== $digits[9]) {
            return false;
        }

        // 11. hane kontrolü (ilk 10 hanenin toplamı mod 10)
        $sumFirst10 = array_sum(array_slice($digits, 0, 10));
        $eleventhDigit = $sumFirst10 % 10;
        
        if ($eleventhDigit !== $digits[10]) {
            return false;
        }

        return true;
    }
}
