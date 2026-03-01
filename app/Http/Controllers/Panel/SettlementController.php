<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Models\ExtraBonus;
use App\Models\Region;
use App\Models\Shift;
use App\Models\SettlementDeduction;
use App\Models\SettlementSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    /**
     * Hakediş hesaplama sayfası (tarih filtresi, kurye bazlı, vardiya bölgesine göre ücretler)
     */
    public function calculation(Request $request)
    {
        $user = $request->user();
        $nameSearch = $request->get('name', '');

        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $couriersQuery = $user->getAccessibleCouriers();
        if ($nameSearch !== '') {
            $couriersQuery = $couriersQuery->where('name', 'like', '%' . $nameSearch . '%');
        }
        $couriers = $couriersQuery->get();

        $rows = [];
        foreach ($couriers as $courier) {
            $shifts = Shift::where('user_id', $courier->id)
                ->where('status', Shift::STATUS_COMPLETED)
                ->whereBetween('started_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get();

            $hourlyEarnings = 0;
            $photoBonusTotal = 0;
            $packageEarnings = 0;
            $photoBonusCount = 0;

            foreach ($shifts as $shift) {
                $settings = SettlementSetting::getForRegion($shift->region_id);
                $minutes = $shift->total_minutes ?? 0;
                $hourlyEarnings += round(($minutes / 60) * (float) $settings->hourly_rate, 2);
                if ($shift->photo_compliance_status === Shift::PHOTO_COMPLIANCE_APPROVED) {
                    $photoBonusCount++;
                    $photoBonusTotal += round((float) $settings->photo_compliance_bonus, 2);
                }
                $packageCount = (int) ($shift->package_count ?? 0);
                if ($settings->has_guaranteed_package && $settings->guaranteed_packages_per_hour !== null && $settings->max_guaranteed_packages_per_shift !== null) {
                    $shiftHours = $minutes / 60;
                    $guaranteedCount = min(
                        $shiftHours * (float) $settings->guaranteed_packages_per_hour,
                        (float) $settings->max_guaranteed_packages_per_shift
                    );
                    $effectivePackages = max($packageCount, (int) round($guaranteedCount));
                    $packageEarnings += round($effectivePackages * (float) $settings->package_rate, 2);
                } else {
                    $packageEarnings += round($packageCount * (float) $settings->package_rate, 2);
                }
            }

            $totalMinutes = $shifts->sum('total_minutes');
            $hours = round($totalMinutes / 60, 2);
            $packageCount = $shifts->sum('package_count');

            $deductTotal = (float) ExpenseRequest::where('user_id', $courier->id)
                ->where('status', ExpenseRequest::STATUS_APPROVED)
                ->where('approval_type', ExpenseRequest::APPROVAL_DEDUCT_FROM_SETTLEMENT)
                ->whereBetween('approved_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->sum('total_amount');
            $deductTotal = round($deductTotal, 2);

            $extraBonusTotal = (float) ExtraBonus::where('user_id', $courier->id)
                ->whereDate('bonus_date', '>=', $startDate)
                ->whereDate('bonus_date', '<=', $endDate)
                ->sum('amount');
            $extraBonusTotal = round($extraBonusTotal, 2);

            $deductionTotal = (float) SettlementDeduction::where('user_id', $courier->id)
                ->whereDate('deduction_date', '>=', $startDate)
                ->whereDate('deduction_date', '<=', $endDate)
                ->sum('amount');
            $deductionTotal = round($deductionTotal, 2);

            $total = round($hourlyEarnings + $photoBonusTotal + $packageEarnings + $extraBonusTotal - $deductTotal - $deductionTotal, 2);

            $rows[] = [
                'courier' => $courier,
                'shift_count' => $shifts->count(),
                'total_minutes' => $totalMinutes,
                'hours' => $hours,
                'hourly_earnings' => round($hourlyEarnings, 2),
                'package_count' => $packageCount,
                'package_earnings' => round($packageEarnings, 2),
                'photo_bonus_count' => $photoBonusCount,
                'photo_bonus_total' => round($photoBonusTotal, 2),
                'deduct_from_settlement_total' => $deductTotal,
                'extra_bonus_total' => $extraBonusTotal,
                'deduction_total' => $deductionTotal,
                'total' => $total,
            ];
        }

        $totals = [
            'shift_count' => collect($rows)->sum('shift_count'),
            'hours' => round(collect($rows)->sum('hours'), 2),
            'hourly_earnings' => round(collect($rows)->sum('hourly_earnings'), 2),
            'photo_bonus_count' => collect($rows)->sum('photo_bonus_count'),
            'photo_bonus_total' => round(collect($rows)->sum('photo_bonus_total'), 2),
            'package_count' => collect($rows)->sum('package_count'),
            'package_earnings' => round(collect($rows)->sum('package_earnings'), 2),
            'deduct_from_settlement_total' => round(collect($rows)->sum('deduct_from_settlement_total'), 2),
            'extra_bonus_total' => round(collect($rows)->sum('extra_bonus_total'), 2),
            'deduction_total' => round(collect($rows)->sum('deduction_total'), 2),
            'total' => round(collect($rows)->sum('total'), 2),
        ];

        $settings = SettlementSetting::get(); // view'da KDV oranı vb. için varsayılan
        return view('panel.settlement.calculation', compact('settings', 'startDate', 'endDate', 'rows', 'totals', 'couriers', 'nameSearch'));
    }

    /**
     * Hakediş hesaplama verisini Excel (CSV) olarak indir
     */
    public function exportCalculation(Request $request)
    {
        $user = $request->user();
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $nameSearch = $request->get('name', '');

        $couriersQuery = $user->getAccessibleCouriers();
        if ($nameSearch !== '') {
            $couriersQuery = $couriersQuery->where('name', 'like', '%' . $nameSearch . '%');
        }
        $couriers = $couriersQuery->get();

        $rows = [];
        foreach ($couriers as $courier) {
            $shifts = Shift::where('user_id', $courier->id)
                ->where('status', Shift::STATUS_COMPLETED)
                ->whereBetween('started_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->get();

            $hourlyEarnings = 0;
            $photoBonusTotal = 0;
            $packageEarnings = 0;
            $photoBonusCount = 0;
            foreach ($shifts as $shift) {
                $settings = SettlementSetting::getForRegion($shift->region_id);
                $minutes = $shift->total_minutes ?? 0;
                $hourlyEarnings += round(($minutes / 60) * (float) $settings->hourly_rate, 2);
                if ($shift->photo_compliance_status === Shift::PHOTO_COMPLIANCE_APPROVED) {
                    $photoBonusCount++;
                    $photoBonusTotal += round((float) $settings->photo_compliance_bonus, 2);
                }
                $packageCount = (int) ($shift->package_count ?? 0);
                if ($settings->has_guaranteed_package && $settings->guaranteed_packages_per_hour !== null && $settings->max_guaranteed_packages_per_shift !== null) {
                    $shiftHours = $minutes / 60;
                    $guaranteedCount = min($shiftHours * (float) $settings->guaranteed_packages_per_hour, (float) $settings->max_guaranteed_packages_per_shift);
                    $effectivePackages = max($packageCount, (int) round($guaranteedCount));
                    $packageEarnings += round($effectivePackages * (float) $settings->package_rate, 2);
                } else {
                    $packageEarnings += round($packageCount * (float) $settings->package_rate, 2);
                }
            }
            $totalMinutes = $shifts->sum('total_minutes');
            $hours = round($totalMinutes / 60, 2);
            $packageCount = $shifts->sum('package_count');
            $deductTotal = round((float) ExpenseRequest::where('user_id', $courier->id)->where('status', ExpenseRequest::STATUS_APPROVED)->where('approval_type', ExpenseRequest::APPROVAL_DEDUCT_FROM_SETTLEMENT)->whereBetween('approved_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->sum('total_amount'), 2);
            $extraBonusTotal = round((float) ExtraBonus::where('user_id', $courier->id)->whereDate('bonus_date', '>=', $startDate)->whereDate('bonus_date', '<=', $endDate)->sum('amount'), 2);
            $deductionTotal = round((float) SettlementDeduction::where('user_id', $courier->id)->whereDate('deduction_date', '>=', $startDate)->whereDate('deduction_date', '<=', $endDate)->sum('amount'), 2);
            $total = round($hourlyEarnings + $photoBonusTotal + $packageEarnings + $extraBonusTotal - $deductTotal - $deductionTotal, 2);
            $rows[] = [
                'courier' => $courier,
                'shift_count' => $shifts->count(),
                'hours' => $hours,
                'hourly_earnings' => round($hourlyEarnings, 2),
                'package_count' => $packageCount,
                'package_earnings' => round($packageEarnings, 2),
                'photo_bonus_count' => $photoBonusCount,
                'photo_bonus_total' => round($photoBonusTotal, 2),
                'deduct_from_settlement_total' => $deductTotal,
                'extra_bonus_total' => $extraBonusTotal,
                'deduction_total' => $deductionTotal,
                'total' => $total,
            ];
        }
        $totals = [
            'shift_count' => collect($rows)->sum('shift_count'),
            'hours' => round(collect($rows)->sum('hours'), 2),
            'hourly_earnings' => round(collect($rows)->sum('hourly_earnings'), 2),
            'package_count' => collect($rows)->sum('package_count'),
            'package_earnings' => round(collect($rows)->sum('package_earnings'), 2),
            'photo_bonus_count' => collect($rows)->sum('photo_bonus_count'),
            'photo_bonus_total' => round(collect($rows)->sum('photo_bonus_total'), 2),
            'deduct_from_settlement_total' => round(collect($rows)->sum('deduct_from_settlement_total'), 2),
            'extra_bonus_total' => round(collect($rows)->sum('extra_bonus_total'), 2),
            'deduction_total' => round(collect($rows)->sum('deduction_total'), 2),
            'total' => round(collect($rows)->sum('total'), 2),
        ];

        $filename = 'hakedis_hesaplama_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $callback = function () use ($rows, $totals) {
            $stream = fopen('php://output', 'w');
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($stream, [
                'Kurye', 'Vardiya', 'Süre (saat)', 'Süre tutarı', 'Paket', 'Paket tutarı', 'Foto prim (adet)', 'Foto prim (TL)', 'Hakedişten düşülen', 'Kesinti', 'Ek prim', 'Toplam (KDV dahil)',
            ], ';');
            foreach ($rows as $r) {
                fputcsv($stream, [
                    $r['courier']->name,
                    $r['shift_count'],
                    $r['hours'],
                    $r['hourly_earnings'],
                    $r['package_count'],
                    $r['package_earnings'],
                    $r['photo_bonus_count'],
                    $r['photo_bonus_total'],
                    $r['deduct_from_settlement_total'],
                    $r['deduction_total'],
                    $r['extra_bonus_total'],
                    $r['total'],
                ], ';');
            }
            fputcsv($stream, ['TOPLAM', $totals['shift_count'], $totals['hours'], $totals['hourly_earnings'], $totals['package_count'], $totals['package_earnings'], $totals['photo_bonus_count'], $totals['photo_bonus_total'], $totals['deduct_from_settlement_total'], $totals['deduction_total'], $totals['extra_bonus_total'], $totals['total']], ';');
            fclose($stream);
        };
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Vardiya uyumluluk raporunu Excel (CSV) olarak indir
     */
    public function exportPhotoComplianceReport(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');
        $nameSearch = $request->get('name', '');
        $endDate = Carbon::parse($request->get('end_date', Carbon::today()->format('Y-m-d')))->endOfDay();
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->subDays(6)->format('Y-m-d')))->startOfDay();
        if ($startDate->gt($endDate)) {
            $startDate = $endDate->copy()->startOfDay();
        }
        $couriersQuery = \App\Models\User::whereIn('id', $courierIds);
        if ($nameSearch !== '') {
            $couriersQuery = $couriersQuery->where('name', 'like', '%' . $nameSearch . '%');
        }
        $couriers = $couriersQuery->orderBy('name')->get();
        $rows = [];
        foreach ($couriers as $courier) {
            $baseQuery = Shift::where('user_id', $courier->id)->where('status', Shift::STATUS_COMPLETED)->whereBetween('started_at', [$startDate, $endDate]);
            $uyumlu = (clone $baseQuery)->where('photo_compliance_status', Shift::PHOTO_COMPLIANCE_APPROVED)->count();
            $uyumsuz = (clone $baseQuery)->where('photo_compliance_status', Shift::PHOTO_COMPLIANCE_NO_BONUS)->count();
            $bekleyen = (clone $baseQuery)->whereIn('photo_compliance_status', [Shift::PHOTO_COMPLIANCE_PENDING, Shift::PHOTO_COMPLIANCE_RE_REQUESTED])->count();
            $rows[] = ['courier' => $courier, 'uyumlu' => $uyumlu, 'uyumsuz' => $uyumsuz, 'bekleyen' => $bekleyen, 'toplam' => $uyumlu + $uyumsuz + $bekleyen];
        }
        $filename = 'vardiya_uyumluluk_raporu_' . now()->format('Y-m-d_His') . '.csv';
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="' . $filename . '"'];
        $callback = function () use ($rows) {
            $stream = fopen('php://output', 'w');
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($stream, ['Kurye', 'Uyumlu', 'Uyumsuz', 'Bekleyen', 'Toplam'], ';');
            foreach ($rows as $r) {
                fputcsv($stream, [$r['courier']->name, $r['uyumlu'], $r['uyumsuz'], $r['bekleyen'], $r['toplam']], ';');
            }
            fclose($stream);
        };
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Kesintiler listesini Excel (CSV) olarak indir
     */
    public function exportDeductions(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');
        $deductions = SettlementDeduction::whereIn('user_id', $courierIds)->with('user')->orderBy('deduction_date', 'desc')->orderBy('id', 'desc')->get();

        $filename = 'kesintiler_' . now()->format('Y-m-d_His') . '.csv';
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="' . $filename . '"'];
        $callback = function () use ($deductions) {
            $stream = fopen('php://output', 'w');
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($stream, ['Kurye', 'Tutar (TL)', 'Neden', 'Tarih', 'Oluşturulma'], ';');
            foreach ($deductions as $d) {
                fputcsv($stream, [
                    $d->user?->name ?? '',
                    number_format($d->amount, 2, ',', '.'),
                    $d->reason ?? '',
                    $d->deduction_date->format('d.m.Y'),
                    $d->created_at->format('d.m.Y H:i'),
                ], ';');
            }
            fclose($stream);
        };
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Ekstra prim ekle (KDV dahil; bonus_date tarihinde hakedişe yansır)
     */
    public function storeExtraBonus(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|in:' . $courierIds->implode(','),
            'reason' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
            'bonus_date' => 'required|date',
        ], [
            'user_id.required' => 'Kurye seçiniz.',
            'reason.required' => 'Prim nedeni zorunludur.',
            'amount.required' => 'Tutar zorunludur.',
            'bonus_date.required' => 'Tarih zorunludur.',
        ]);

        ExtraBonus::create([
            'user_id' => $validated['user_id'],
            'amount' => round((float) $validated['amount'], 2),
            'reason' => $validated['reason'],
            'bonus_date' => $validated['bonus_date'],
        ]);

        $params = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
        ];
        if ($request->filled('name')) {
            $params['name'] = $request->get('name');
        }
        return redirect()->route('panel.settlement.calculation', $params)->with('success', 'Ekstra prim eklendi.');
    }

    /**
     * Kurye ek prim listesi (tarih aralığına göre; modal için JSON)
     */
    public function listExtraBonuses(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');

        $courierId = $request->get('courier_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if (!$courierId || !$courierIds->contains((int) $courierId)) {
            return response()->json(['items' => [], 'courier_name' => '']);
        }

        $items = ExtraBonus::where('user_id', $courierId)
            ->whereDate('bonus_date', '>=', $startDate)
            ->whereDate('bonus_date', '<=', $endDate)
            ->orderBy('bonus_date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'amount' => number_format($b->amount, 2, ',', '.'),
                'reason' => $b->reason,
                'bonus_date' => $b->bonus_date->format('d.m.Y'),
            ]);

        $courierName = \App\Models\User::find($courierId)?->name ?? '';

        return response()->json(['items' => $items, 'courier_name' => $courierName]);
    }

    /**
     * Kurye hakedişten düşülen / borç bakiyesi listesi (modal için JSON)
     */
    public function listExpenseDeductions(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');

        $courierId = $request->get('courier_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $type = $request->get('type'); // deduct_from_settlement | debt_balance

        if (!$courierId || !$courierIds->contains((int) $courierId)) {
            return response()->json(['items' => [], 'courier_name' => '', 'title' => '']);
        }
        if ($type !== ExpenseRequest::APPROVAL_DEDUCT_FROM_SETTLEMENT) {
            return response()->json(['items' => [], 'courier_name' => '', 'title' => '']);
        }

        $items = ExpenseRequest::where('user_id', $courierId)
            ->where('status', ExpenseRequest::STATUS_APPROVED)
            ->where('approval_type', $type)
            ->whereBetween('approved_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('approved_at', 'desc')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'date' => $e->approved_at->format('d.m.Y H:i'),
                'amount' => number_format((float) $e->total_amount, 2, ',', '.'),
                'reason' => $e->reason ?? '',
                'order_number' => $e->order_number ?? '',
            ]);

        $courierName = \App\Models\User::find($courierId)?->name ?? '';
        $title = 'Hakedişten düşülen';

        return response()->json(['items' => $items, 'courier_name' => $courierName, 'title' => $title]);
    }

    /**
     * Kesintiler listesi
     */
    public function deductionsIndex(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');

        $deductions = SettlementDeduction::whereIn('user_id', $courierIds)
            ->with('user')
            ->orderBy('deduction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        $couriers = $user->getAccessibleCouriers()->orderBy('name')->get();

        return view('panel.settlement.deductions.index', compact('deductions', 'couriers'));
    }

    /**
     * Kesinti oluştur (KDV hariç hakedişten düşer)
     */
    public function deductionsStore(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|in:' . $courierIds->implode(','),
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:2000',
            'deduction_date' => 'required|date',
        ], [
            'user_id.required' => 'Kurye seçiniz.',
            'amount.required' => 'Kesinti tutarı zorunludur.',
            'reason.required' => 'Kesinti nedeni zorunludur.',
            'deduction_date.required' => 'Tarih zorunludur.',
        ]);

        SettlementDeduction::create([
            'user_id' => $validated['user_id'],
            'amount' => round((float) $validated['amount'], 2),
            'reason' => $validated['reason'],
            'deduction_date' => $validated['deduction_date'],
        ]);

        return redirect()->route('panel.settlement.deductions.index')->with('success', 'Kesinti oluşturuldu. Kurye KDV hariç hakedişinden düşülecektir.');
    }

    /**
     * Hakediş ayarları (bölge bazlı: her bölge için ayrı saatlik ücret, paket, vardiya uyumluluk primi, KDV)
     */
    public function settings()
    {
        $regions = Region::where('is_active', true)->orderBy('city')->orderBy('name')->get();
        $regionSettings = $regions->mapWithKeys(fn ($r) => [$r->id => SettlementSetting::getForRegion($r->id)]);

        return view('panel.settlement.settings', [
            'regions' => $regions,
            'regionSettings' => $regionSettings,
        ]);
    }

    /**
     * Tek bölge ayarını kaydet (kart üzerinden)
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'region_key' => 'required|string',
            'hourly_rate' => 'required|numeric|min:0',
            'photo_compliance_bonus' => 'required|numeric|min:0',
            'package_rate' => 'required|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'has_guaranteed_package' => 'nullable|in:0,1',
            'guaranteed_packages_per_hour' => 'nullable|numeric|min:0',
            'max_guaranteed_packages_per_shift' => 'nullable|integer|min:0',
        ], [
            'hourly_rate.required' => 'Saatlik ücret zorunludur.',
            'photo_compliance_bonus.required' => 'Vardiya uyumluluk primi zorunludur.',
            'package_rate.required' => 'Paket başı ücret zorunludur.',
        ]);

        $regionId = $request->region_key === 'default' || $request->region_key === '' ? null : (int) $request->region_key;
        $vatRate = $request->filled('vat_rate') ? (float) $request->vat_rate : 18;
        $hasGuaranteed = (bool) $request->boolean('has_guaranteed_package');
        $guaranteedPerHour = $hasGuaranteed && $request->filled('guaranteed_packages_per_hour')
            ? (float) $request->guaranteed_packages_per_hour
            : null;
        $maxGuaranteedPerShift = $hasGuaranteed && $request->filled('max_guaranteed_packages_per_shift')
            ? (int) $request->max_guaranteed_packages_per_shift
            : null;

        $setting = SettlementSetting::getForRegion($regionId);
        $setting->update([
            'hourly_rate' => $validated['hourly_rate'],
            'photo_compliance_bonus' => $validated['photo_compliance_bonus'],
            'package_rate' => $validated['package_rate'],
            'vat_rate' => $vatRate,
            'has_guaranteed_package' => $hasGuaranteed,
            'guaranteed_packages_per_hour' => $guaranteedPerHour,
            'max_guaranteed_packages_per_shift' => $maxGuaranteedPerShift,
        ]);

        $label = $regionId ? (Region::find($regionId)?->name ?? 'Bölge') : 'Varsayılan';
        return redirect()->route('panel.settlement.settings')->with('success', "{$label} hakediş ayarları kaydedildi.");
    }

    /**
     * Vardiya uyumluluk incelemesi listesi (sadece vardiya başlangıç fotoğrafları değerlendirilir, prim ver / tekrar iste)
     */
    public function photoReview(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');
        $nameSearch = $request->get('name', '');

        $query = Shift::query()
            ->whereIn('user_id', $courierIds)
            ->where('photo_compliance_status', Shift::PHOTO_COMPLIANCE_PENDING)
            ->where(function ($q) {
                $q->where('status', Shift::STATUS_COMPLETED)
                    ->orWhere(function ($q2) {
                        $q2->where('status', Shift::STATUS_ACTIVE)
                            ->whereHas('photos', fn ($p) => $p->where('type', 'start'));
                    });
            });

        if ($nameSearch !== '') {
            $query->whereHas('user', function ($q) use ($nameSearch) {
                $q->where('name', 'like', '%' . $nameSearch . '%');
            });
        }

        $shifts = $query
            ->with(['user', 'region', 'photos' => fn ($q) => $q->orderBy('type')->orderBy('is_retry')])
            ->orderBy('started_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('panel.settlement.photo-review', compact('shifts', 'nameSearch'));
    }

    /**
     * Vardiya uyumluluk primini onayla (aktif veya tamamlanmış; sadece başlangıç fotoğrafı değerlendirilir)
     */
    public function approvePhotoBonus(Request $request, Shift $shift)
    {
        $shift->update(['photo_compliance_status' => Shift::PHOTO_COMPLIANCE_APPROVED]);

        return redirect()->back()->with('success', 'Vardiya uyumluluk primi onaylandı.');
    }

    /**
     * Prim verme (inceleme tamamlandı, prim verilmedi; aktif veya tamamlanmış)
     */
    public function noPhotoBonus(Request $request, Shift $shift)
    {
        $shift->update(['photo_compliance_status' => Shift::PHOTO_COMPLIANCE_NO_BONUS]);

        return redirect()->back()->with('success', 'İnceleme kaydedildi, prim verilmedi.');
    }

    /**
     * Tekrar fotoğraf iste (aktif veya tamamlanmış vardiya; kurye vardiya başlangıç fotoğrafını tekrar yükler)
     */
    public function requestPhotoRetry(Request $request, Shift $shift)
    {
        $shift->update(['photo_compliance_status' => Shift::PHOTO_COMPLIANCE_RE_REQUESTED]);

        return redirect()->back()->with('success', 'Kuryeye tekrar vardiya başlangıç fotoğrafı yüklemesi isteği iletildi.');
    }

    /**
     * Vardiya uyumluluk raporu (kurye bazlı uyumlu / uyumsuz sayıları). Varsayılan: son 1 hafta.
     */
    public function photoComplianceReport(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');
        $nameSearch = $request->get('name', '');

        $endDate = Carbon::parse($request->get('end_date', Carbon::today()->format('Y-m-d')))->endOfDay();
        $startDate = Carbon::parse($request->get('start_date', Carbon::today()->subDays(6)->format('Y-m-d')))->startOfDay();
        if ($startDate->gt($endDate)) {
            $startDate = $endDate->copy()->startOfDay();
        }

        $couriersQuery = \App\Models\User::whereIn('id', $courierIds);
        if ($nameSearch !== '') {
            $couriersQuery = $couriersQuery->where('name', 'like', '%' . $nameSearch . '%');
        }
        $couriers = $couriersQuery->orderBy('name')->get();

        $rows = [];
        foreach ($couriers as $courier) {
            $baseQuery = Shift::where('user_id', $courier->id)
                ->where('status', Shift::STATUS_COMPLETED)
                ->whereBetween('started_at', [$startDate, $endDate]);

            $uyumlu = (clone $baseQuery)->where('photo_compliance_status', Shift::PHOTO_COMPLIANCE_APPROVED)->count();
            $uyumsuz = (clone $baseQuery)->where('photo_compliance_status', Shift::PHOTO_COMPLIANCE_NO_BONUS)->count();
            $bekleyen = (clone $baseQuery)->whereIn('photo_compliance_status', [Shift::PHOTO_COMPLIANCE_PENDING, Shift::PHOTO_COMPLIANCE_RE_REQUESTED])->count();

            $rows[] = [
                'courier' => $courier,
                'uyumlu' => $uyumlu,
                'uyumsuz' => $uyumsuz,
                'bekleyen' => $bekleyen,
                'toplam' => $uyumlu + $uyumsuz + $bekleyen,
            ];
        }

        return view('panel.settlement.photo-compliance-report', [
            'rows' => $rows,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'nameSearch' => $nameSearch,
        ]);
    }
}
