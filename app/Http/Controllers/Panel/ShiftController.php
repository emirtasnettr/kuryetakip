<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\District;
use Illuminate\Http\Request;

/**
 * Panel Vardiya Controller
 * 
 * Operasyon paneli için vardiya yönetimi.
 */
class ShiftController extends Controller
{
    /**
     * Vardiya listesi
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $accessibleCouriers = $user->getAccessibleCouriers();

        $query = Shift::whereIn('user_id', $accessibleCouriers->pluck('id'))
            ->with(['user', 'district', 'photos']);

        // Tarih aralığı filtresi
        if ($request->filled('start_date')) {
            $query->whereDate('started_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('started_at', '<=', $request->end_date);
        }

        // Durum filtresi
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Kurye filtresi
        if ($request->filled('courier_id')) {
            $query->where('user_id', $request->courier_id);
        }

        // İlçe filtresi
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        // Sıralama
        $sortBy = $request->get('sort_by', 'started_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Sayfalama
        $shifts = $query->paginate(20)->withQueryString();

        // Filtre seçenekleri
        $couriers = $accessibleCouriers->get();
        $districts = District::active()->orderBy('name')->get();

        return view('panel.shifts.index', compact('shifts', 'couriers', 'districts'));
    }

    /**
     * Vardiya detayı
     */
    public function show(Request $request, Shift $shift)
    {
        $this->authorize('view', $shift);

        $shift->load(['user', 'district', 'photos', 'logs']);

        return view('panel.shifts.show', compact('shift'));
    }

    /**
     * Aktif vardiyalar
     */
    public function active(Request $request)
    {
        $user = $request->user();
        $accessibleCouriers = $user->getAccessibleCouriers();

        $shifts = Shift::whereIn('user_id', $accessibleCouriers->pluck('id'))
            ->active()
            ->with(['user', 'district'])
            ->orderBy('started_at', 'desc')
            ->get();

        return view('panel.shifts.active', compact('shifts'));
    }

    /**
     * Vardiyayı iptal et (sadece yönetici)
     */
    public function cancel(Request $request, Shift $shift)
    {
        $this->authorize('cancel', $shift);

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $shift->cancel($request->reason);

        return redirect()->route('panel.shifts.show', $shift)
            ->with('success', 'Vardiya başarıyla iptal edildi.');
    }

    /**
     * Yönetici notu ekle
     */
    public function addNote(Request $request, Shift $shift)
    {
        $this->authorize('addAdminNote', $shift);

        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $shift->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('panel.shifts.show', $shift)
            ->with('success', 'Not başarıyla eklendi.');
    }

    /**
     * Rapor sayfası
     */
    public function reports(Request $request)
    {
        $user = $request->user();
        $accessibleCouriers = $user->getAccessibleCouriers();

        // Tarih aralığı (varsayılan: bu ay)
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Kurye bazlı rapor
        $courierReport = $accessibleCouriers->get()
            ->map(function ($courier) use ($startDate, $endDate) {
                $shifts = $courier->shifts()
                    ->completed()
                    ->betweenDates($startDate, $endDate);

                return [
                    'courier' => $courier,
                    'shift_count' => $shifts->count(),
                    'total_packages' => $shifts->sum('package_count'),
                    'total_minutes' => $shifts->sum('total_minutes'),
                ];
            })
            ->sortByDesc('total_packages');

        // Genel istatistikler
        $overallStats = [
            'total_shifts' => Shift::whereIn('user_id', $accessibleCouriers->pluck('id'))
                ->completed()
                ->betweenDates($startDate, $endDate)
                ->count(),
            'total_packages' => Shift::whereIn('user_id', $accessibleCouriers->pluck('id'))
                ->completed()
                ->betweenDates($startDate, $endDate)
                ->sum('package_count'),
            'total_hours' => round(Shift::whereIn('user_id', $accessibleCouriers->pluck('id'))
                ->completed()
                ->betweenDates($startDate, $endDate)
                ->sum('total_minutes') / 60, 1),
        ];

        return view('panel.shifts.reports', compact(
            'courierReport',
            'overallStats',
            'startDate',
            'endDate'
        ));
    }
}
