<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * API Authentication Controller
 * 
 * Mobil uygulama ve API için kimlik doğrulama işlemleri.
 * Laravel Sanctum kullanır.
 */
class AuthController extends Controller
{
    /**
     * Kullanıcı girişi
     * 
     * @bodyParam email string required E-posta adresi
     * @bodyParam password string required Şifre
     * @bodyParam device_name string Cihaz adı (token için)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        // Kullanıcıyı bul
        $user = User::where('email', $request->email)->first();

        // Kullanıcı kontrolü
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Girdiğiniz bilgiler hatalı.'],
            ]);
        }

        // Aktiflik kontrolü
        if (!$user->is_active) {
            return $this->error('Hesabınız pasif durumda. Lütfen yöneticinize başvurun.', 403);
        }

        // Son giriş bilgilerini güncelle
        $user->updateLoginInfo($request->ip());

        // Token oluştur
        $deviceName = $request->device_name ?? 'mobile-app';
        $token = $user->createToken($deviceName)->plainTextToken;

        // Kullanıcı bilgilerini yükle
        $user->load('role', 'courierDistricts');

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => [
                    'name' => $user->role->name,
                    'display_name' => $user->role->display_name,
                ],
                'employee_code' => $user->employee_code,
                'vehicle_type' => $user->vehicle_type,
                'vehicle_plate' => $user->vehicle_plate,
                'districts' => $user->courierDistricts->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'is_primary' => $d->pivot->is_primary,
                ]),
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Giriş başarılı');
    }

    /**
     * Kullanıcı çıkışı
     * 
     * Mevcut token'ı iptal eder.
     */
    public function logout(Request $request)
    {
        // Mevcut token'ı sil
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Çıkış başarılı');
    }

    /**
     * Tüm oturumlardan çıkış
     * 
     * Kullanıcının tüm token'larını iptal eder.
     */
    public function logoutAll(Request $request)
    {
        // Tüm token'ları sil
        $request->user()->tokens()->delete();

        return $this->success(null, 'Tüm oturumlardan çıkış yapıldı');
    }

    /**
     * Mevcut kullanıcı bilgileri
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('role', 'courierDistricts', 'partner');

        // Aktif vardiya kontrolü
        $activeShift = $user->activeShift();

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => [
                    'name' => $user->role->name,
                    'display_name' => $user->role->display_name,
                ],
                'employee_code' => $user->employee_code,
                'vehicle_type' => $user->vehicle_type,
                'vehicle_plate' => $user->vehicle_plate,
                'is_courier' => $user->isCourier(),
                'can_access_panel' => $user->canAccessPanel(),
                'districts' => $user->courierDistricts->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'city' => $d->city,
                    'is_primary' => $d->pivot->is_primary,
                ]),
                'partner' => $user->partner ? [
                    'id' => $user->partner->id,
                    'name' => $user->partner->name,
                ] : null,
            ],
            'active_shift' => $activeShift ? [
                'id' => $activeShift->id,
                'started_at' => $activeShift->started_at->toIso8601String(),
                'duration_minutes' => $activeShift->getDurationInMinutes(),
            ] : null,
        ]);
    }

    /**
     * Profil güncelleme
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'phone' => 'sometimes|nullable|string|max:20',
            'vehicle_type' => 'sometimes|nullable|string|max:50',
            'vehicle_plate' => 'sometimes|nullable|string|max:20',
        ]);

        $user->update($validated);

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'vehicle_type' => $user->vehicle_type,
                'vehicle_plate' => $user->vehicle_plate,
            ],
        ], 'Profil güncellendi');
    }

    /**
     * Şifre değiştirme
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        // Mevcut şifre kontrolü
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Mevcut şifreniz hatalı.'],
            ]);
        }

        // Yeni şifreyi kaydet
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->success(null, 'Şifreniz başarıyla değiştirildi');
    }
}
