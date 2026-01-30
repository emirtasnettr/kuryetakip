<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftLog;
use App\Models\ShiftPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Kurye Mobil Web Controller
 * 
 * Kuryelerin mobil cihazlarından kullanacağı web arayüzü.
 * Mobile-first, responsive tasarım için optimize edilmiştir.
 */
class MobileController extends Controller
{
    /**
     * Giriş formu
     */
    public function showLoginForm()
    {
        return view('courier.login');
    }

    /**
     * Giriş işlemi
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Girdiğiniz bilgiler hatalı.']);
        }

        $user = Auth::user();

        // Kurye kontrolü
        if (!$user->isCourier()) {
            Auth::logout();
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Bu uygulama sadece kuryeler içindir.']);
        }

        // Aktiflik kontrolü
        if (!$user->is_active) {
            Auth::logout();
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Hesabınız pasif durumda.']);
        }

        $request->session()->regenerate();
        $user->updateLoginInfo($request->ip());

        return redirect()->route('courier.home');
    }

    /**
     * Çıkış işlemi
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('courier.login');
    }

    /**
     * Ana sayfa
     */
    public function home(Request $request)
    {
        $user = $request->user();
        $activeShift = $user->activeShift();

        // Bugünün istatistikleri
        $todayStats = [
            'shift_count' => $user->shifts()->completed()->today()->count(),
            'total_packages' => $user->shifts()->completed()->today()->sum('package_count'),
            'total_minutes' => $user->shifts()->completed()->today()->sum('total_minutes'),
        ];

        return view('courier.home', compact('user', 'activeShift', 'todayStats'));
    }

    /**
     * Vardiya başlatma formu
     */
    public function showStartForm(Request $request)
    {
        $user = $request->user();

        // Zaten aktif vardiya varsa yönlendir
        if ($user->hasActiveShift()) {
            return redirect()->route('courier.home')
                ->with('error', 'Zaten aktif bir vardiyanız var.');
        }

        return view('courier.shift-start', compact('user'));
    }

    /**
     * Vardiya başlat
     */
    public function startShift(Request $request)
    {
        $user = $request->user();

        // Zaten aktif vardiya kontrolü
        if ($user->hasActiveShift()) {
            return redirect()->route('courier.home')
                ->with('error', 'Zaten aktif bir vardiyanız var.');
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'nullable|image|max:10240',
        ]);

        // Kuryenin ana bölgesini otomatik olarak al
        $primaryDistrict = $user->courierDistricts()->wherePivot('is_primary', true)->first();

        DB::beginTransaction();

        try {
            // Vardiya oluştur (bölge otomatik atanır)
            $shift = Shift::startNew($user, [
                'district_id' => $primaryDistrict?->id,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            // Log oluştur
            ShiftLog::createFromRequest($shift, ShiftLog::TYPE_START, $validated, $request);

            // Fotoğraf
            if ($request->hasFile('photo')) {
                ShiftPhoto::createFromUpload($shift, ShiftPhoto::TYPE_START, $request->file('photo'));
            }

            DB::commit();

            return redirect()->route('courier.home')
                ->with('success', 'Vardiya başarıyla başlatıldı!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Vardiya başlatılamadı: ' . $e->getMessage());
        }
    }

    /**
     * Vardiya bitirme formu
     */
    public function showEndForm(Request $request)
    {
        $user = $request->user();
        $activeShift = $user->activeShift();

        // Aktif vardiya yoksa yönlendir
        if (!$activeShift) {
            return redirect()->route('courier.home')
                ->with('error', 'Aktif bir vardiyanız bulunmuyor.');
        }

        $activeShift->load('district');

        return view('courier.shift-end', compact('user', 'activeShift'));
    }

    /**
     * Vardiya bitir
     */
    public function endShift(Request $request)
    {
        $user = $request->user();
        $activeShift = $user->activeShift();

        if (!$activeShift) {
            return redirect()->route('courier.home')
                ->with('error', 'Aktif bir vardiyanız bulunmuyor.');
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'package_count' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|max:10240',
        ]);

        DB::beginTransaction();

        try {
            // Vardiyayı tamamla
            $activeShift->complete([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'package_count' => $validated['package_count'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Log oluştur
            ShiftLog::createFromRequest($activeShift, ShiftLog::TYPE_END, $validated, $request);

            // Fotoğraf
            if ($request->hasFile('photo')) {
                ShiftPhoto::createFromUpload($activeShift, ShiftPhoto::TYPE_END, $request->file('photo'));
            }

            DB::commit();

            return redirect()->route('courier.home')
                ->with('success', 'Vardiya başarıyla tamamlandı!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Vardiya tamamlanamadı: ' . $e->getMessage());
        }
    }

    /**
     * Vardiya geçmişi
     */
    public function shiftHistory(Request $request)
    {
        $user = $request->user();

        $shifts = $user->shifts()
            ->with('district')
            ->orderBy('started_at', 'desc')
            ->paginate(10);

        return view('courier.shifts', compact('user', 'shifts'));
    }

    /**
     * Profil sayfası
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load(['courierDistricts', 'partner']);

        // Bu ay istatistikleri
        $monthlyStats = [
            'shift_count' => $user->shifts()->completed()->whereMonth('started_at', now()->month)->count(),
            'total_packages' => $user->shifts()->completed()->whereMonth('started_at', now()->month)->sum('package_count'),
            'total_hours' => round($user->shifts()->completed()->whereMonth('started_at', now()->month)->sum('total_minutes') / 60, 1),
        ];

        return view('courier.profile', compact('user', 'monthlyStats'));
    }
}
