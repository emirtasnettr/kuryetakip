<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Panel Dashboard Controller
 * 
 * Operasyon paneli ana sayfa ve özet görünümler.
 */
class DashboardController extends Controller
{
    /**
     * Dashboard ana sayfası
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Erişilebilir kuryeler
        $accessibleCouriers = $user->getAccessibleCouriers();

        // Bugünün istatistikleri
        $todayStats = [
            'total_couriers' => $accessibleCouriers->count(),
            'active_shifts' => Shift::whereIn('user_id', $accessibleCouriers->pluck('id'))
                ->active()
                ->count(),
            'completed_shifts' => Shift::whereIn('user_id', $accessibleCouriers->pluck('id'))
                ->completed()
                ->today()
                ->count(),
            'total_packages' => Shift::whereIn('user_id', $accessibleCouriers->pluck('id'))
                ->completed()
                ->today()
                ->sum('package_count'),
        ];

        // Aktif vardiyalar (en son 10 tanesi)
        $activeShifts = Shift::whereIn('user_id', $accessibleCouriers->pluck('id'))
            ->active()
            ->with(['user', 'district'])
            ->orderBy('started_at', 'desc')
            ->limit(10)
            ->get();

        // Bugün tamamlanan vardiyalar (en son 10 tanesi)
        $completedShifts = Shift::whereIn('user_id', $accessibleCouriers->pluck('id'))
            ->completed()
            ->today()
            ->with(['user', 'district'])
            ->orderBy('ended_at', 'desc')
            ->limit(10)
            ->get();

        return view('panel.dashboard', compact(
            'todayStats',
            'activeShifts',
            'completedShifts'
        ));
    }
}
