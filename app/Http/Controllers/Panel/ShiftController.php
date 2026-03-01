<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\District;
use Carbon\Carbon;
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
            ->with(['user', 'district', 'region', 'photos']);

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
     * Vardiyaya girmeyenler: Seçilen tarih aralığında atanmış ama vardiyayı başlatmamış kuryeler.
     * Varsayılan: bugün (bugün girmemiş kişiler listelenir).
     */
    public function noShow(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');

        $today = Carbon::today()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->format('Y-m-d')
            : $today;
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->format('Y-m-d')
            : $today;
        if ($startDate > $endDate) {
            $startDate = $endDate;
        }

        // Parametre yoksa varsayılan bugün olsun; URL'de görünsün
        if (! $request->hasAny(['start_date', 'end_date'])) {
            return redirect()->route('panel.shifts.no-show', [
                'start_date' => $today,
                'end_date' => $today,
            ]);
        }

        $assignments = ShiftAssignment::with(['courier', 'scheduledShift.region', 'assignedByUser'])
            ->whereHas('scheduledShift', fn ($q) => $q->whereBetween('shift_date', [$startDate, $endDate]))
            ->whereIn('courier_id', $courierIds)
            ->whereIn('status', [
                ShiftAssignment::STATUS_ASSIGNED,
                ShiftAssignment::STATUS_CONFIRMED,
                ShiftAssignment::STATUS_NO_SHOW,
            ])
            ->get()
            ->sortBy(fn ($a) => ($a->scheduledShift->shift_date ?? '') . ' ' . ($a->scheduledShift->start_time ?? ''));

        return view('panel.shifts.no-show', compact('assignments', 'startDate', 'endDate'));
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
     * Vardiyayı zorla sonlandır (yönetici)
     */
    public function forceComplete(Request $request, Shift $shift)
    {
        $this->authorize('forceComplete', $shift);

        $validated = $request->validate([
            'package_count' => 'required|integer|min:0',
            'admin_notes' => 'nullable|string|max:1000',
        ], [
            'package_count.required' => 'Paket sayısı zorunludur.',
            'package_count.integer' => 'Paket sayısı tam sayı olmalıdır.',
            'package_count.min' => 'Paket sayısı 0 veya daha büyük olmalıdır.',
        ]);

        $endTime = now();
        $totalMinutes = $shift->started_at->diffInMinutes($endTime);

        $shift->update([
            'status' => Shift::STATUS_COMPLETED,
            'ended_at' => $endTime,
            'package_count' => $validated['package_count'],
            'total_minutes' => $totalMinutes,
            'photo_compliance_status' => Shift::PHOTO_COMPLIANCE_PENDING,
            'admin_notes' => $validated['admin_notes'] 
                ? ($shift->admin_notes ? $shift->admin_notes . "\n\n" : '') . "[Yönetici tarafından sonlandırıldı]\n" . $validated['admin_notes']
                : ($shift->admin_notes ? $shift->admin_notes . "\n\n" : '') . "[Yönetici tarafından sonlandırıldı - " . now()->format('d.m.Y H:i') . "]",
        ]);

        return redirect()->route('panel.shifts.show', $shift)
            ->with('success', 'Vardiya başarıyla sonlandırıldı.');
    }

    /**
     * Paket sayısını güncelle (sadece sistem yöneticisi)
     */
    public function updatePackageCount(Request $request, Shift $shift)
    {
        $this->authorize('updatePackageCount', $shift);

        $validated = $request->validate([
            'package_count' => 'required|integer|min:0',
        ], [
            'package_count.required' => 'Paket sayısı zorunludur.',
            'package_count.integer' => 'Paket sayısı tam sayı olmalıdır.',
            'package_count.min' => 'Paket sayısı 0 veya daha büyük olmalıdır.',
        ]);

        $oldCount = $shift->package_count;
        
        $shift->update([
            'package_count' => $validated['package_count'],
            'admin_notes' => ($shift->admin_notes ? $shift->admin_notes . "\n\n" : '') 
                . "[Paket sayısı güncellendi - " . now()->format('d.m.Y H:i') . "]\n"
                . "Eski: " . ($oldCount ?? '0') . " → Yeni: " . $validated['package_count'],
        ]);

        return redirect()->route('panel.shifts.show', $shift)
            ->with('success', 'Paket sayısı başarıyla güncellendi.');
    }

    /**
     * Kuryeye ek çalışma saati ekle / bitiş saatini uzat
     * Tamamlanmış vardiyada bitiş saatini ileri alır; aktif vardiyada planlanan bitiş güncellenebilir.
     */
    public function extendHours(Request $request, Shift $shift)
    {
        $this->authorize('extendHours', $shift);

        $validated = $request->validate([
            'end_date' => 'required|date',
            'end_time' => ['required', 'string', 'regex:#^([0-1]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$#'],
        ], [
            'end_date.required' => 'Bitiş tarihi zorunludur.',
            'end_time.required' => 'Bitiş saati zorunludur.',
            'end_time.regex' => 'Bitiş saati HH:MM formatında olmalıdır.',
        ]);

        $time = strlen($validated['end_time']) === 5 ? $validated['end_time'] . ':00' : $validated['end_time'];
        $newEndAt = \Carbon\Carbon::parse($validated['end_date'] . ' ' . $time);

        if ($newEndAt->lte($shift->started_at)) {
            return redirect()->back()
                ->with('error', 'Bitiş saati, vardiya başlangıcından sonra olmalıdır.');
        }

        $totalMinutes = (int) $shift->started_at->diffInMinutes($newEndAt);

        $updates = [
            'ended_at' => $newEndAt,
            'total_minutes' => $totalMinutes,
            'admin_notes' => ($shift->admin_notes ? $shift->admin_notes . "\n\n" : '')
                . '[Bitiş saati uzatıldı - ' . now()->format('d.m.Y H:i') . '] Yeni bitiş: ' . $newEndAt->format('d.m.Y H:i'),
        ];
        if ($shift->isActive()) {
            $updates['status'] = Shift::STATUS_COMPLETED;
            $updates['photo_compliance_status'] = Shift::PHOTO_COMPLIANCE_PENDING;
        }
        $shift->update($updates);

        // Planlı vardiya ataması varsa fiili bitiş saatini güncelle
        $assignment = \App\Models\ShiftAssignment::where('actual_shift_id', $shift->id)->first();
        if ($assignment) {
            $assignment->update([
                'actual_end_time' => $newEndAt->format('H:i'),
                'end_reason' => ($assignment->end_reason ? $assignment->end_reason . ' | ' : '') . 'Panelden ek süre eklendi.',
                'status' => \App\Models\ShiftAssignment::STATUS_COMPLETED,
                'completed_at' => $newEndAt,
            ]);
        }

        return redirect()->route('panel.shifts.show', $shift)
            ->with('success', 'Mesai süresi eklendi.');
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

    /**
     * Vardiya raporları listesini Excel (CSV) olarak indir
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $accessibleCouriers = $user->getAccessibleCouriers();
        $query = Shift::whereIn('user_id', $accessibleCouriers->pluck('id'))
            ->with(['user', 'district', 'region']);

        if ($request->filled('start_date')) {
            $query->whereDate('started_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('started_at', '<=', $request->end_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('courier_id')) {
            $query->where('user_id', $request->courier_id);
        }
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }
        $sortBy = $request->get('sort_by', 'started_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $shifts = $query->orderBy($sortBy, $sortOrder)->get();

        $filename = 'vardiya_raporlari_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($shifts) {
            $stream = fopen('php://output', 'w');
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($stream, [
                'Kurye', 'Sicil No', 'E-posta', 'İlçe', 'Bölge', 'Başlangıç', 'Bitiş', 'Süre (dk)', 'Paket',
                'Foto Uyumluluk', 'Notlar', 'Durum', 'Sistem Kapatma',
            ], ';');
            foreach ($shifts as $s) {
                $photoLabel = $s->status === 'completed' && $s->photo_compliance_status
                    ? ($s->photo_compliance_status === Shift::PHOTO_COMPLIANCE_APPROVED ? 'Onaylı' : ($s->photo_compliance_status === Shift::PHOTO_COMPLIANCE_NO_BONUS ? 'Prim yok' : ($s->photo_compliance_status === Shift::PHOTO_COMPLIANCE_RE_REQUESTED ? 'Tekrar istenecek' : 'Beklemede')))
                    : '—';
                fputcsv($stream, [
                    $s->user?->name ?? '',
                    $s->user?->employee_code ?? '',
                    $s->user?->email ?? '',
                    $s->district?->name ?? '',
                    $s->region?->name ?? '',
                    $s->started_at?->format('d.m.Y H:i') ?? '',
                    $s->ended_at ? $s->ended_at->format('d.m.Y H:i') : '',
                    $s->total_minutes ?? '',
                    $s->package_count ?? '',
                    $photoLabel,
                    $s->notes ?? '',
                    $s->status ?? '',
                    $s->auto_closed_at ? $s->auto_closed_at->format('d.m.Y H:i') : '',
                ], ';');
            }
            fclose($stream);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Vardiyaya girmeyenler listesini Excel (CSV) olarak indir
     */
    public function exportNoShow(Request $request)
    {
        $user = $request->user();
        $courierIds = $user->getAccessibleCouriers()->pluck('id');
        $today = Carbon::today()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->format('Y-m-d') : $today;
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->format('Y-m-d') : $today;
        if ($startDate > $endDate) {
            $startDate = $endDate;
        }

        $assignments = ShiftAssignment::with(['courier', 'scheduledShift.region', 'assignedByUser'])
            ->whereHas('scheduledShift', fn ($q) => $q->whereBetween('shift_date', [$startDate, $endDate]))
            ->whereIn('courier_id', $courierIds)
            ->whereIn('status', [
                ShiftAssignment::STATUS_ASSIGNED,
                ShiftAssignment::STATUS_CONFIRMED,
                ShiftAssignment::STATUS_NO_SHOW,
            ])
            ->get()
            ->sortBy(fn ($a) => ($a->scheduledShift->shift_date ?? '') . ' ' . ($a->scheduledShift->start_time ?? ''));

        $filename = 'vardiyaya_girmeyenler_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($assignments) {
            $stream = fopen('php://output', 'w');
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($stream, [
                'Tarih', 'Kurye', 'Sicil No', 'E-posta', 'Telefon', 'Bölge', 'Vardiya Saati', 'Vardiya Adı', 'Atayan', 'Atama Durumu', 'Notlar',
            ], ';');
            foreach ($assignments as $a) {
                $statusLabel = $a->status === ShiftAssignment::STATUS_NO_SHOW ? 'Gelmedi'
                    : ($a->status === ShiftAssignment::STATUS_CONFIRMED ? 'Onayladı, girmedi' : 'Atandı, girmedi');
                fputcsv($stream, [
                    $a->scheduledShift->shift_date->format('d.m.Y'),
                    $a->courier->name ?? '',
                    $a->courier->employee_code ?? '',
                    $a->courier->email ?? '',
                    $a->courier->phone ?? '',
                    $a->scheduledShift->region->name ?? '—',
                    (\Carbon\Carbon::parse($a->scheduledShift->start_time)->format('H:i') . ' – ' . \Carbon\Carbon::parse($a->scheduledShift->end_time)->format('H:i')),
                    $a->scheduledShift->title ?? '—',
                    $a->assignedByUser?->name ?? '—',
                    $statusLabel,
                    $a->notes ?? '',
                ], ';');
            }
            fclose($stream);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Raporlar sayfası verisini Excel (CSV) olarak indir
     */
    public function exportReports(Request $request)
    {
        $user = $request->user();
        $accessibleCouriers = $user->getAccessibleCouriers();
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

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

        $filename = 'raporlar_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($courierReport) {
            $stream = fopen('php://output', 'w');
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($stream, ['Kurye', 'Vardiya Sayısı', 'Toplam Paket', 'Çalışma Süresi (saat)', 'Ort. Paket/Saat'], ';');
            foreach ($courierReport as $data) {
                $hours = round($data['total_minutes'] / 60, 1);
                $avg = $data['total_minutes'] > 0 ? round($data['total_packages'] / ($data['total_minutes'] / 60), 1) : '';
                fputcsv($stream, [
                    $data['courier']->name,
                    $data['shift_count'],
                    $data['total_packages'],
                    $hours,
                    $avg,
                ], ';');
            }
            fclose($stream);
        };

        return response()->stream($callback, 200, $headers);
    }
}
