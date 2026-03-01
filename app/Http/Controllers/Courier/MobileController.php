<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ScheduledShift;
use App\Models\ShiftLog;
use App\Models\ShiftPhoto;
use App\Models\SettlementSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        // Bugün için atanmış vardiyalar
        $todayAssignments = ShiftAssignment::where('courier_id', $user->id)
            ->whereHas('scheduledShift', function($q) {
                $q->whereDate('shift_date', today());
            })
            ->whereIn('status', [ShiftAssignment::STATUS_ASSIGNED, ShiftAssignment::STATUS_CONFIRMED])
            ->whereNull('actual_end_time') // Erken bitirilmemiş
            ->with(['scheduledShift.region'])
            ->get()
            ->sortBy(function($a) {
                return $a->scheduledShift->start_time;
            });

        // Gelecek vardiyalar (yarın ve sonrası)
        $upcomingAssignments = ShiftAssignment::where('courier_id', $user->id)
            ->whereHas('scheduledShift', function($q) {
                $q->whereDate('shift_date', '>', today());
            })
            ->whereIn('status', [ShiftAssignment::STATUS_ASSIGNED, ShiftAssignment::STATUS_CONFIRMED])
            ->with(['scheduledShift.region'])
            ->get()
            ->sortBy(function($a) {
                return $a->scheduledShift->shift_date . ' ' . $a->scheduledShift->start_time;
            })
            ->take(5);

        // Aktif olan (başlatılmış) atama
        $activeAssignment = ShiftAssignment::where('courier_id', $user->id)
            ->where('status', ShiftAssignment::STATUS_STARTED)
            ->whereNull('actual_end_time')
            ->with(['scheduledShift.region'])
            ->first();

        // Fotoğraf talepleri: yöneticinin tekrar fotoğraf istediği vardiyalar (aktif veya tamamlanmış)
        $shiftsNeedingPhotoRetry = Shift::where('user_id', $user->id)
            ->where('photo_compliance_status', Shift::PHOTO_COMPLIANCE_RE_REQUESTED)
            ->orderBy('started_at', 'desc')
            ->get();

        return view('courier.home', compact('user', 'activeShift', 'todayStats', 'todayAssignments', 'upcomingAssignments', 'activeAssignment', 'shiftsNeedingPhotoRetry'));
    }

    /**
     * Kurye hakedişi (tarih filtresi, varsayılan son 1 hafta; vardiya bölgesine göre ücretler)
     */
    public function settlement(Request $request)
    {
        $user = $request->user();

        $endDate = Carbon::parse($request->get('end_date', Carbon::today()->format('Y-m-d')))->endOfDay();
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->subDays(6)->format('Y-m-d')))->startOfDay();
        if ($startDate->gt($endDate)) {
            $startDate = $endDate->copy()->startOfDay();
        }

        $shifts = Shift::where('user_id', $user->id)
            ->where('status', Shift::STATUS_COMPLETED)
            ->whereBetween('started_at', [$startDate, $endDate])
            ->get();

        $hourlyEarnings = 0;
        $photoBonusTotal = 0;
        $photoBonusCount = 0;
        $packageEarnings = 0;

        foreach ($shifts as $shift) {
            $settings = SettlementSetting::getForRegion($shift->region_id);
            $minutes = $shift->total_minutes ?? 0;
            $hourlyEarnings += round(($minutes / 60) * (float) $settings->hourly_rate, 2);
            if ($shift->photo_compliance_status === Shift::PHOTO_COMPLIANCE_APPROVED) {
                $photoBonusCount++;
                $photoBonusTotal += round((float) $settings->photo_compliance_bonus, 2);
            }
            $packageEarnings += round(($shift->package_count ?? 0) * (float) $settings->package_rate, 2);
        }

        $totalMinutes = $shifts->sum('total_minutes');
        $hours = round($totalMinutes / 60, 2);
        $packageCount = $shifts->sum('package_count');

        $deductTotal = (float) ExpenseRequest::where('user_id', $user->id)
            ->where('status', ExpenseRequest::STATUS_APPROVED)
            ->where('approval_type', ExpenseRequest::APPROVAL_DEDUCT_FROM_SETTLEMENT)
            ->whereBetween('approved_at', [$startDate, $endDate])
            ->sum('total_amount');
        $deductTotal = round($deductTotal, 2);

        $deductionTotal = (float) \App\Models\SettlementDeduction::where('user_id', $user->id)
            ->whereDate('deduction_date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('deduction_date', '<=', $endDate->format('Y-m-d'))
            ->sum('amount');
        $deductionTotal = round($deductionTotal, 2);

        $extraBonusTotal = (float) \App\Models\ExtraBonus::where('user_id', $user->id)
            ->whereDate('bonus_date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('bonus_date', '<=', $endDate->format('Y-m-d'))
            ->sum('amount');
        $extraBonusTotal = round($extraBonusTotal, 2);

        $total = round($hourlyEarnings + $photoBonusTotal + $packageEarnings + $extraBonusTotal - $deductTotal - $deductionTotal, 2);

        $data = [
            'shift_count' => $shifts->count(),
            'hours' => $hours,
            'hourly_earnings' => round($hourlyEarnings, 2),
            'photo_bonus_count' => $photoBonusCount,
            'photo_bonus_total' => round($photoBonusTotal, 2),
            'package_count' => $packageCount,
            'package_earnings' => round($packageEarnings, 2),
            'deduct_from_settlement_total' => $deductTotal,
            'deduction_total' => $deductionTotal,
            'extra_bonus_total' => $extraBonusTotal,
            'total' => $total,
        ];

        return view('courier.settlement', [
            'data' => $data,
            'settings' => SettlementSetting::get(),
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }

    /**
     * Vardiya başlatma formu
     */
    public function showStartForm(Request $request)
    {
        $user = $request->user();
        $assignmentId = $request->get('assignment_id');

        // Zaten aktif vardiya varsa yönlendir
        if ($user->hasActiveShift()) {
            return redirect()->route('courier.home')
                ->with('error', 'Zaten aktif bir vardiyanız var.');
        }

        // Atama kontrolü
        $assignment = null;
        if ($assignmentId) {
            $assignment = ShiftAssignment::where('id', $assignmentId)
                ->where('courier_id', $user->id)
                ->whereIn('status', [ShiftAssignment::STATUS_ASSIGNED, ShiftAssignment::STATUS_CONFIRMED])
                ->with(['scheduledShift.region'])
                ->first();

            if (!$assignment) {
                return redirect()->route('courier.home')
                    ->with('error', 'Geçersiz vardiya ataması.');
            }

            // Başlangıç saatine 10 dakika kala kontrolü
            $scheduledShift = $assignment->scheduledShift;
            $shiftDate = $scheduledShift->shift_date->format('Y-m-d');
            $startTime = Carbon::parse($shiftDate . ' ' . Carbon::parse($scheduledShift->start_time)->format('H:i:s'));
            $canStartAt = $startTime->copy()->subMinutes(10);
            
            if (now()->lt($canStartAt)) {
                $minutesLeft = now()->diffInMinutes($canStartAt, false);
                return redirect()->route('courier.home')
                    ->with('error', "Vardiya başlangıç saatine 10 dakikadan fazla var. {$startTime->format('H:i')}'de başlayabilirsiniz.");
            }
        }

        return view('courier.shift-start', compact('user', 'assignment'));
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
            'assignment_id' => 'required|exists:shift_assignments,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'required|image|max:25600',
        ], [
            'assignment_id.required' => 'Vardiya seçimi zorunludur.',
            'photo.required' => 'Başlangıç fotoğrafı zorunludur.',
            'photo.image' => 'Yüklenen dosya bir görsel olmalıdır.',
            'photo.max' => 'Fotoğraf boyutu en fazla 25MB olabilir.',
        ]);

        // Atama kontrolü
        $assignment = ShiftAssignment::where('id', $validated['assignment_id'])
            ->where('courier_id', $user->id)
            ->whereIn('status', [ShiftAssignment::STATUS_ASSIGNED, ShiftAssignment::STATUS_CONFIRMED])
            ->with(['scheduledShift.region'])
            ->first();

        if (!$assignment) {
            return redirect()->route('courier.home')
                ->with('error', 'Geçersiz vardiya ataması.');
        }

        // Başlangıç saatine 10 dakika kala kontrolü
        $scheduledShift = $assignment->scheduledShift;
        $shiftDate = $scheduledShift->shift_date->format('Y-m-d');
        $startTime = Carbon::parse($shiftDate . ' ' . Carbon::parse($scheduledShift->start_time)->format('H:i:s'));
        $canStartAt = $startTime->copy()->subMinutes(10);
        
        if (now()->lt($canStartAt)) {
            return redirect()->route('courier.home')
                ->with('error', "Vardiya başlangıç saatine 10 dakikadan fazla var. {$startTime->format('H:i')}'de başlayabilirsiniz.");
        }

        DB::beginTransaction();

        try {
            // Vardiya oluştur (bölge, hakedişte bölge ayarlarına göre hesaplanacak)
            $shift = Shift::startNew($user, [
                'district_id' => null,
                'region_id' => $scheduledShift->region_id ?? null,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            // Atamayı güncelle
            $assignment->update([
                'status' => ShiftAssignment::STATUS_STARTED,
                'started_at' => now(),
                'actual_shift_id' => $shift->id,
                'actual_start_time' => now()->format('H:i'),
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

        // Bitiş saati kontrolü
        $assignment = ShiftAssignment::where('actual_shift_id', $activeShift->id)
            ->where('courier_id', $user->id)
            ->with('scheduledShift')
            ->first();

        if ($assignment && $assignment->scheduledShift) {
            $scheduledShift = $assignment->scheduledShift;
            $shiftDate = $scheduledShift->shift_date->format('Y-m-d');
            $endTime = Carbon::parse($shiftDate . ' ' . Carbon::parse($scheduledShift->end_time)->format('H:i:s'));
            
            // Gece yarısını geçen vardiyalar için
            $startTime = Carbon::parse($shiftDate . ' ' . Carbon::parse($scheduledShift->start_time)->format('H:i:s'));
            if ($endTime->lt($startTime)) {
                $endTime->addDay();
            }
            
            if (now()->lt($endTime)) {
                $remainingMinutes = now()->diffInMinutes($endTime, false);
                $hours = floor($remainingMinutes / 60);
                $minutes = $remainingMinutes % 60;
                $remainingText = $hours > 0 ? "{$hours} saat {$minutes} dakika" : "{$minutes} dakika";
                
                return redirect()->route('courier.home')
                    ->with('error', "Vardiya bitiş saatine henüz ulaşılmadı. Kalan süre: {$remainingText}");
            }
        }

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

        // Bitiş saati kontrolü
        $assignment = ShiftAssignment::where('actual_shift_id', $activeShift->id)
            ->where('courier_id', $user->id)
            ->with('scheduledShift')
            ->first();

        if ($assignment && $assignment->scheduledShift) {
            $scheduledShift = $assignment->scheduledShift;
            $shiftDate = $scheduledShift->shift_date->format('Y-m-d');
            $endTime = Carbon::parse($shiftDate . ' ' . Carbon::parse($scheduledShift->end_time)->format('H:i:s'));
            
            // Gece yarısını geçen vardiyalar için
            $startTime = Carbon::parse($shiftDate . ' ' . Carbon::parse($scheduledShift->start_time)->format('H:i:s'));
            if ($endTime->lt($startTime)) {
                $endTime->addDay();
            }
            
            if (now()->lt($endTime)) {
                $remainingMinutes = now()->diffInMinutes($endTime, false);
                $hours = floor($remainingMinutes / 60);
                $minutes = $remainingMinutes % 60;
                $remainingText = $hours > 0 ? "{$hours} saat {$minutes} dakika" : "{$minutes} dakika";
                
                return redirect()->route('courier.home')
                    ->with('error', "Vardiya bitiş saatine henüz ulaşılmadı. Kalan süre: {$remainingText}");
            }
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'package_count' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:1000',
            'photo' => 'required|image|max:25600',
        ], [
            'photo.required' => 'Bitiş fotoğrafı zorunludur.',
            'photo.image' => 'Yüklenen dosya bir görsel olmalıdır.',
            'photo.max' => 'Fotoğraf boyutu en fazla 25MB olabilir.',
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

            // Atamayı da tamamla
            $assignment = ShiftAssignment::where('actual_shift_id', $activeShift->id)
                ->where('courier_id', $user->id)
                ->first();

            if ($assignment) {
                $assignment->update([
                    'status' => ShiftAssignment::STATUS_COMPLETED,
                    'completed_at' => now(),
                    'actual_end_time' => now()->format('H:i'),
                ]);
            }

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
     * Atanmış vardiyalar
     */
    public function assignments(Request $request)
    {
        $user = $request->user();

        // Tüm atanmış vardiyalar (bugün ve gelecek)
        $assignments = ShiftAssignment::where('courier_id', $user->id)
            ->whereNotIn('status', [ShiftAssignment::STATUS_CANCELLED])
            ->whereHas('scheduledShift', function($q) {
                $q->whereDate('shift_date', '>=', Carbon::today());
            })
            ->with(['scheduledShift.region'])
            ->get()
            ->sortBy(function($a) {
                return $a->scheduledShift->shift_date->format('Y-m-d') . ' ' . $a->scheduledShift->start_time;
            });

        // Geçmiş atamalar
        $pastAssignments = ShiftAssignment::where('courier_id', $user->id)
            ->whereNotIn('status', [ShiftAssignment::STATUS_CANCELLED])
            ->whereHas('scheduledShift', function($q) {
                $q->whereDate('shift_date', '<', Carbon::today());
            })
            ->with(['scheduledShift.region'])
            ->orderByDesc(
                ScheduledShift::select('shift_date')
                    ->whereColumn('scheduled_shifts.id', 'shift_assignments.scheduled_shift_id')
            )
            ->limit(10)
            ->get();

        return view('courier.assignments', compact('user', 'assignments', 'pastAssignments'));
    }

    /**
     * Profil sayfası
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load(['courierDistricts', 'partner']);

        // Bu ay istatistikleri (yıl ve ay kontrolü)
        $monthlyStats = [
            'shift_count' => $user->shifts()->completed()
                ->whereYear('started_at', now()->year)
                ->whereMonth('started_at', now()->month)
                ->count(),
            'total_packages' => $user->shifts()->completed()
                ->whereYear('started_at', now()->year)
                ->whereMonth('started_at', now()->month)
                ->sum('package_count'),
            'total_hours' => round($user->shifts()->completed()
                ->whereYear('started_at', now()->year)
                ->whereMonth('started_at', now()->month)
                ->sum('total_minutes') / 60, 1),
        ];

        return view('courier.profile', compact('user', 'monthlyStats'));
    }

    /**
     * Fotoğraf talepleri listesi (yöneticinin tekrar vardiya başlangıç fotoğrafı istediği vardiyalar)
     */
    public function photoRetryList(Request $request)
    {
        $user = $request->user();
        $shifts = Shift::where('user_id', $user->id)
            ->where('photo_compliance_status', Shift::PHOTO_COMPLIANCE_RE_REQUESTED)
            ->orderBy('started_at', 'desc')
            ->get();

        return view('courier.photo-retry-list', compact('user', 'shifts'));
    }

    /**
     * Tekrar vardiya başlangıç fotoğrafı yükleme formu (tek vardiya)
     */
    public function photoRetryUpload(Request $request, Shift $shift)
    {
        if ($shift->user_id !== $request->user()->id) {
            abort(403);
        }
        if ($shift->photo_compliance_status !== Shift::PHOTO_COMPLIANCE_RE_REQUESTED) {
            return redirect()->route('courier.home')->with('error', 'Bu vardiya için tekrar fotoğraf istenmiyor.');
        }

        return view('courier.photo-retry-upload', compact('shift'));
    }

    /**
     * Tekrar vardiya başlangıç fotoğrafı yükleme (sadece başlangıç; tekrar incelemeye düşer)
     */
    public function photoRetryUploadSubmit(Request $request, Shift $shift)
    {
        if ($shift->user_id !== $request->user()->id) {
            abort(403);
        }
        if ($shift->photo_compliance_status !== Shift::PHOTO_COMPLIANCE_RE_REQUESTED) {
            return redirect()->route('courier.home')->with('error', 'Bu vardiya için tekrar fotoğraf istenmiyor.');
        }

        $request->validate([
            'photo_start' => 'required|image|max:25600',
        ], [
            'photo_start.required' => 'Vardiya başlangıç fotoğrafı zorunludur.',
        ]);

        ShiftPhoto::createFromUpload($shift, ShiftPhoto::TYPE_START, $request->file('photo_start'), 'public', true);

        $shift->update(['photo_compliance_status' => Shift::PHOTO_COMPLIANCE_PENDING]);

        return redirect()->route('courier.photo-retry')->with('success', 'Vardiya başlangıç fotoğrafınız yüklendi. Vardiya Uyumluluk İncelemesi\'ne tekrar düşecektir.');
    }
}
