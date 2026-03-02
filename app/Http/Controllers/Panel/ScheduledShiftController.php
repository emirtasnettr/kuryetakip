<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ScheduledShift;
use App\Models\ShiftAssignment;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Planlı Vardiya Controller
 * 
 * Takvim görünümü ve vardiya planlama işlemleri.
 */
class ScheduledShiftController extends Controller
{
    /**
     * Vardiya planlama – basit liste (varsayılan sayfa)
     */
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $regionId = $request->get('region_id');
        $dateObj = Carbon::parse($date);

        $regions = Region::active()->orderBy('name')->get();

        $query = ScheduledShift::with(['region', 'activeAssignments.courier'])
            ->whereDate('shift_date', $date)
            ->whereIn('status', [ScheduledShift::STATUS_PUBLISHED, ScheduledShift::STATUS_COMPLETED])
            ->orderBy('region_id')
            ->orderBy('start_time');

        if ($regionId) {
            $query->where('region_id', $regionId);
        }

        $shifts = $query->get();

        return view('panel.schedule.index', compact('regions', 'shifts', 'date', 'dateObj', 'regionId'));
    }

    /**
     * Tek vardiya detayı (basit HTML – kurye ata/çıkar)
     */
    public function showPage(ScheduledShift $scheduledShift)
    {
        $scheduledShift->load(['region', 'assignments.courier', 'activeAssignments.courier']);
        $regions = Region::active()->orderBy('name')->get();
        $courierIds = auth()->user()->getAccessibleCouriers()->pluck('id');
        $couriers = User::whereIn('id', $courierIds)
            ->whereHas('role', fn($q) => $q->where('name', Role::COURIER))
            ->orderBy('name')
            ->get();
        return view('panel.schedule.show', compact('scheduledShift', 'regions', 'couriers'));
    }

    /**
     * Günlük Operasyon Özeti - Tüm vardiyalar tek bakışta
     */
    public function dailyOverview(Request $request)
    {
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        $date = Carbon::parse($selectedDate);
        
        // Saati geçmiş vardiyaları otomatik tamamlandı olarak işaretle
        $this->markPastShiftsAsCompleted($date);
        
        // Tüm bölgeleri al
        $regions = Region::active()->orderBy('city')->orderBy('name')->get();
        
        // Seçili güne ait vardiyalar: devam eden (published) + tamamlanan (completed) hepsi listelensin.
        // validAssignments kullanılıyor ki tamamlanmış vardiyalarda da atanan kuryeler görünsün (activeAssignments bitişte boşalıyor).
        $shifts = ScheduledShift::with(['region', 'validAssignments.courier'])
            ->whereDate('shift_date', $date->format('Y-m-d'))
            ->whereIn('status', [ScheduledShift::STATUS_PUBLISHED, ScheduledShift::STATUS_COMPLETED])
            ->orderBy('start_time')
            ->get();
        
        // Bölgelere göre grupla
        $shiftsByRegion = $shifts->groupBy('region_id');
        
        // İstatistikler
        $stats = [
            'total_shifts' => $shifts->count(),
            'total_required' => $shifts->sum('required_couriers'),
            'total_assigned' => $shifts->sum('assigned_count'),
            'regions_with_shifts' => $shiftsByRegion->count(),
            'total_regions' => $regions->count(),
        ];
        
        return view('panel.schedule.daily-overview', compact('regions', 'shifts', 'shiftsByRegion', 'selectedDate', 'date', 'stats'));
    }

    /**
     * Saati geçmiş vardiyaları tamamlandı olarak işaretle
     */
    protected function markPastShiftsAsCompleted(Carbon $date): void
    {
        $now = Carbon::now();
        
        $pastShifts = ScheduledShift::whereDate('shift_date', $date->format('Y-m-d'))
            ->whereIn('status', [ScheduledShift::STATUS_DRAFT, ScheduledShift::STATUS_PUBLISHED])
            ->get();

        foreach ($pastShifts as $shift) {
            $shiftEndDateTime = Carbon::parse(
                $shift->shift_date->format('Y-m-d') . ' ' . Carbon::parse($shift->end_time)->format('H:i:s')
            );
            // Gece yarısını geçen vardiya (örn. 15:00-01:00): bitiş ertesi gün
            if (Carbon::parse($shift->end_time)->lte(Carbon::parse($shift->start_time))) {
                $shiftEndDateTime->addDay();
            }

            if ($shiftEndDateTime->lt($now)) {
                // Vardiyayı tamamlandı olarak işaretle
                $shift->update(['status' => ScheduledShift::STATUS_COMPLETED]);
                
                // Aktif atamaları tamamla (erken bitirmemiş olanlar)
                // actual_end_time set edilmez - vardiya bitişine kadar çalıştığı varsayılır
                ShiftAssignment::where('scheduled_shift_id', $shift->id)
                    ->whereNull('actual_end_time')
                    ->whereNotIn('status', [ShiftAssignment::STATUS_CANCELLED, ShiftAssignment::STATUS_COMPLETED])
                    ->each(function (ShiftAssignment $assignment) {
                        $assignment->update([
                            'status' => ShiftAssignment::STATUS_COMPLETED,
                            'completed_at' => now(),
                        ]);
                    });
            }
        }
    }

    /**
     * İle göre ilçeleri getir (AJAX) - Eski sistem için korunuyor
     */
    public function districtsByCity(Request $request)
    {
        $city = $request->get('city', 'İstanbul');
        
        $districts = District::active()
            ->where('city', $city)
            ->orderBy('name')
            ->get(['id', 'name', 'city']);
        
        return response()->json($districts);
    }

    /**
     * Takvim için vardiya eventleri (AJAX)
     */
    public function events(Request $request)
    {
        $start = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end', now()->endOfMonth()->format('Y-m-d'));
        
        // Bölge filtresi
        $regionId = $request->get('region_id');

        $query = ScheduledShift::with(['region', 'activeAssignments'])
            ->betweenDates($start, $end)
            ->active();

        if ($regionId) {
            $query->where('region_id', $regionId);
        }

        $shifts = $query->get();

        $events = $shifts->map(function ($shift) {
            return $shift->toCalendarEvent();
        });

        return response()->json($events);
    }

    /**
     * Vardiya detayı (AJAX modal)
     */
    public function show(ScheduledShift $scheduledShift)
    {
        $scheduledShift->load(['region', 'creator', 'assignments.courier', 'activeAssignments.courier', 'validAssignments.courier']);
        
        return response()->json([
            'id' => $scheduledShift->id,
            'title' => $scheduledShift->display_title,
            'region_id' => $scheduledShift->region_id,
            'region_name' => $scheduledShift->region?->name,
            'region' => $scheduledShift->region ? [
                'id' => $scheduledShift->region->id,
                'name' => $scheduledShift->region->name,
                'city' => $scheduledShift->region->city,
                'color' => $scheduledShift->region->color,
            ] : null,
            'date' => $scheduledShift->shift_date->format('d.m.Y'),
            'date_raw' => $scheduledShift->shift_date->format('Y-m-d'),
            'shift_date' => $scheduledShift->shift_date->format('d.m.Y'),
            'start_time' => Carbon::parse($scheduledShift->start_time)->format('H:i'),
            'end_time' => Carbon::parse($scheduledShift->end_time)->format('H:i'),
            'duration' => $scheduledShift->formatted_duration,
            'required_couriers' => $scheduledShift->required_couriers,
            'assigned_count' => $scheduledShift->validAssignments->count(),
            'remaining_capacity' => max(0, $scheduledShift->required_couriers - $scheduledShift->validAssignments->count()),
            'status' => $scheduledShift->status,
            'color' => $scheduledShift->color,
            'notes' => $scheduledShift->notes,
            'created_by' => $scheduledShift->creator->name,
            'assignments' => $scheduledShift->assignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'courier_id' => $assignment->courier_id,
                    'courier_name' => $assignment->courier->name,
                    'courier_phone' => $assignment->courier->phone,
                    'status' => $assignment->status,
                    'status_label' => $assignment->status_label,
                    'status_color' => $assignment->status_color,
                ];
            }),
            'active_assignments' => $scheduledShift->activeAssignments->map(function ($assignment) use ($scheduledShift) {
                return [
                    'id' => $assignment->id,
                    'courier_id' => $assignment->courier_id,
                    'courier' => [
                        'id' => $assignment->courier->id,
                        'name' => $assignment->courier->name,
                        'phone' => $assignment->courier->phone,
                    ],
                    'status' => $assignment->status,
                    'status_label' => $assignment->status_label,
                    'actual_start_time' => $assignment->actual_start_time,
                    'actual_end_time' => $assignment->actual_end_time,
                    'started_at' => $assignment->started_at ? $assignment->started_at->toIso8601String() : null,
                    'effective_start_time' => $assignment->effective_start_time,
                    'effective_end_time' => $assignment->effective_end_time,
                    'worked_hours' => $assignment->worked_hours,
                    'worked_duration' => $assignment->worked_duration,
                    'has_ended_early' => $assignment->hasEndedEarly(),
                    'end_reason' => $assignment->end_reason,
                    'shift_start_time' => Carbon::parse($scheduledShift->start_time)->format('H:i:s'),
                    'shift_date' => $scheduledShift->shift_date->format('Y-m-d'),
                ];
            }),
            'all_assignments' => $scheduledShift->validAssignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'courier_id' => $assignment->courier_id,
                    'courier' => [
                        'id' => $assignment->courier->id,
                        'name' => $assignment->courier->name,
                        'phone' => $assignment->courier->phone,
                    ],
                    'status' => $assignment->status,
                    'status_label' => $assignment->status_label,
                    'actual_start_time' => $assignment->actual_start_time,
                    'actual_end_time' => $assignment->actual_end_time,
                    'effective_start_time' => $assignment->effective_start_time,
                    'effective_end_time' => $assignment->effective_end_time,
                    'worked_hours' => $assignment->worked_hours,
                    'worked_duration' => $assignment->worked_duration,
                    'has_ended_early' => $assignment->hasEndedEarly(),
                    'end_reason' => $assignment->end_reason,
                ];
            })->values(),
        ]);
    }

    /**
     * Yeni vardiya oluştur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'shift_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'required_couriers' => 'required|integer|min:1|max:50',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:20',
            'status' => 'nullable|in:draft,published',
        ], [
            'region_id.required' => 'Bölge seçimi zorunludur.',
            'region_id.exists' => 'Geçerli bir bölge seçmelisiniz.',
            'shift_date.required' => 'Vardiya tarihi zorunludur.',
            'shift_date.after_or_equal' => 'Vardiya tarihi bugün veya sonrası olmalıdır.',
            'start_time.required' => 'Başlangıç saati zorunludur.',
            'end_time.required' => 'Bitiş saati zorunludur.',
            'required_couriers.required' => 'Gerekli kurye sayısı zorunludur.',
            'required_couriers.min' => 'En az 1 kurye gereklidir.',
        ]);

        try {
            $scheduledShift = ScheduledShift::create([
                'region_id' => $validated['region_id'],
                'shift_date' => $validated['shift_date'],
                'start_time' => Carbon::parse($validated['start_time'])->format('H:i:s'),
                'end_time' => Carbon::parse($validated['end_time'])->format('H:i:s'),
                'required_couriers' => $validated['required_couriers'],
                'title' => $validated['title'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'status' => $validated['status'] ?? ScheduledShift::STATUS_PUBLISHED,
                'color' => $validated['color'] ?? '#3B82F6',
            ]);
        } catch (\Throwable $e) {
            \Log::error('ScheduledShift store error', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vardiya kaydedilirken hata oluştu: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->route('panel.schedule.index')->with('error', 'Vardiya kaydedilemedi.');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vardiya başarıyla oluşturuldu.',
                'shift' => $scheduledShift->fresh(['region', 'activeAssignments'])->toCalendarEvent(),
            ]);
        }

        return redirect()->route('panel.schedule.index', ['date' => $scheduledShift->shift_date->format('Y-m-d')])->with('success', 'Vardiya başarıyla oluşturuldu.');
    }

    /**
     * Vardiya güncelle
     */
    public function update(Request $request, ScheduledShift $scheduledShift)
    {
        $validated = $request->validate([
            'region_id' => 'sometimes|exists:regions,id',
            'shift_date' => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i',
            'required_couriers' => 'sometimes|integer|min:1|max:50',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:20',
            'status' => 'nullable|in:draft,published,completed,cancelled',
        ]);

        $scheduledShift->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vardiya başarıyla güncellendi.',
                'shift' => $scheduledShift->fresh(['region', 'activeAssignments'])->toCalendarEvent(),
            ]);
        }

        return redirect()->route('panel.schedule.index', ['date' => $scheduledShift->shift_date->format('Y-m-d')])->with('success', 'Vardiya başarıyla güncellendi.');
    }

    /**
     * Vardiya sil
     */
    public function destroy(ScheduledShift $scheduledShift)
    {
        $shiftDate = $scheduledShift->shift_date->format('Y-m-d');
        // Atamaları da sil
        $scheduledShift->assignments()->delete();
        $scheduledShift->delete();
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vardiya başarıyla silindi.',
            ]);
        }

        return redirect()->route('panel.schedule.index', ['date' => $shiftDate])->with('success', 'Vardiya başarıyla silindi.');
    }

    /**
     * Vardiyayı sürükle-bırak ile taşı (tarih/saat güncelle)
     */
    public function move(Request $request, ScheduledShift $scheduledShift)
    {
        $validated = $request->validate([
            'shift_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ]);

        $scheduledShift->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vardiya taşındı.',
            'shift' => $scheduledShift->fresh(['region', 'activeAssignments'])->toCalendarEvent(),
        ]);
    }

    /**
     * Vardiyayı çoğalt (kopyala)
     */
    public function duplicate(Request $request, ScheduledShift $scheduledShift)
    {
        $validated = $request->validate([
            'shift_date' => 'required|date|after_or_equal:today',
        ]);

        $newShift = $scheduledShift->replicate(['id', 'created_at', 'updated_at']);
        $newShift->shift_date = $validated['shift_date'];
        $newShift->created_by = auth()->id();
        $newShift->save();

        return response()->json([
            'success' => true,
            'message' => 'Vardiya kopyalandı.',
            'shift' => $newShift->fresh(['region', 'activeAssignments'])->toCalendarEvent(),
        ]);
    }

    // ==================== KURYE ATAMA İŞLEMLERİ ====================

    /**
     * Uygun kuryeleri listele (bölgeye göre)
     */
    public function availableCouriers(Request $request)
    {
        $regionId = $request->get('region_id');
        $shiftId = $request->get('shift_id');
        $date = $request->get('date');

        // Bölgeye atanmış aktif kuryeler
        $query = User::active()
            ->whereHas('role', fn($q) => $q->where('name', Role::COURIER));

        if ($regionId) {
            // Yeni bölge sistemi: courier_regions tablosundan
            $query->whereHas('courierRegions', fn($q) => $q->where('region_id', $regionId));
        }

        $couriers = $query->orderBy('name')->get();

        // Bu vardiyaya zaten atanmış kuryeleri ve atama ID'lerini al
        $assignments = [];
        if ($shiftId) {
            $assignments = ShiftAssignment::where('scheduled_shift_id', $shiftId)
                ->notCancelled()
                ->get()
                ->keyBy('courier_id')
                ->toArray();
        }
        $assignedIds = array_keys($assignments);

        // Aynı gün ve aynı saatlerde başka vardiyası olan kuryeleri işaretle
        $busyIds = [];
        if ($date && $shiftId) {
            $shift = ScheduledShift::find($shiftId);
            if ($shift) {
                $shiftStartTime = Carbon::parse($shift->start_time)->format('H:i');
                $shiftEndTime = Carbon::parse($shift->end_time)->format('H:i');
                
                $busyIds = ShiftAssignment::notCancelled()
                    ->whereNull('actual_end_time') // Sadece aktif (erken bitirmemiş) atamaları kontrol et
                    ->whereHas('scheduledShift', function($q) use ($date, $shift, $shiftStartTime, $shiftEndTime) {
                        $q->whereDate('shift_date', $date)
                          ->where('id', '!=', $shift->id)
                          ->where('start_time', '<', $shiftEndTime)
                          ->where('end_time', '>', $shiftStartTime);
                    })
                    ->pluck('courier_id')
                    ->toArray();
            }
        }

        $courierList = $couriers->map(function ($courier) use ($assignments, $assignedIds, $busyIds) {
            $isAssigned = in_array($courier->id, $assignedIds);
            return [
                'id' => $courier->id,
                'name' => $courier->name,
                'phone' => $courier->phone,
                'vehicle_type' => $courier->vehicle_type,
                'vehicle_plate' => $courier->vehicle_plate,
                'is_assigned' => $isAssigned,
                'is_busy' => in_array($courier->id, $busyIds),
                'assignment_id' => $isAssigned ? ($assignments[$courier->id]['id'] ?? null) : null,
            ];
        });

        // Tüm kuryeleri döndür (atanmış, meşgul, uygun) - arayüz hepsini listeler
        return response()->json([
            'couriers' => $courierList->values()->all(),
        ]);
    }

    /**
     * Kurye ata (drag & drop veya manuel)
     */
    public function assignCourier(Request $request, ScheduledShift $scheduledShift)
    {
        $validated = $request->validate([
            'courier_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        // Aynı gün ve saat aralığında çakışan vardiya kontrolü
        $shiftStartTime = Carbon::parse($scheduledShift->start_time)->format('H:i');
        $shiftEndTime = Carbon::parse($scheduledShift->end_time)->format('H:i');
        
        $overlappingShift = ScheduledShift::where('id', '!=', $scheduledShift->id)
            ->whereDate('shift_date', $scheduledShift->shift_date)
            ->whereHas('activeAssignments', function($q) use ($validated) {
                $q->where('courier_id', $validated['courier_id']);
            })
            ->where('start_time', '<', $shiftEndTime)
            ->where('end_time', '>', $shiftStartTime)
            ->with('region')
            ->first();

        if ($overlappingShift) {
            $regionName = $overlappingShift->region->name ?? 'Bilinmeyen Bölge';
            $startTime = Carbon::parse($overlappingShift->start_time)->format('H:i');
            $endTime = Carbon::parse($overlappingShift->end_time)->format('H:i');
            $msg = "Bu kurye aynı saatlerde başka bir vardiyaya atanmış: {$regionName} ({$startTime} - {$endTime})";
            if (!$request->wantsJson()) {
                return redirect()->route('panel.schedule.shifts.page', $scheduledShift)->with('error', $msg);
            }
            return response()->json(['success' => false, 'message' => $msg], 422);
        }

        // Mevcut atama var mı kontrol et (iptal edilmiş dahil)
        $existing = ShiftAssignment::where('scheduled_shift_id', $scheduledShift->id)
            ->where('courier_id', $validated['courier_id'])
            ->first();

        if ($existing) {
            // Eğer iptal edilmişse, yeniden aktifleştir
            if ($existing->isCancelled()) {
                // Kapasite kontrolü
                if ($scheduledShift->isFullyStaffed()) {
                    if (!$request->wantsJson()) {
                        return redirect()->route('panel.schedule.shifts.page', $scheduledShift)->with('error', 'Vardiya kapasitesi dolu.');
                    }
                    return response()->json(['success' => false, 'message' => 'Vardiya kapasitesi dolu.'], 422);
                }

                $existing->update([
                    'status' => ShiftAssignment::STATUS_ASSIGNED,
                    'assigned_by' => auth()->id(),
                    'notes' => $validated['notes'] ?? $existing->notes,
                    'confirmed_at' => null,
                    'started_at' => null,
                    'completed_at' => null,
                ]);

                $existing->load('courier');

                if (!$request->wantsJson()) {
                    return redirect()->route('panel.schedule.shifts.page', $scheduledShift)->with('success', 'Kurye başarıyla atandı.');
                }
                return response()->json([
                    'success' => true,
                    'message' => 'Kurye başarıyla atandı.',
                    'assignment' => [
                        'id' => $existing->id,
                        'courier_id' => $existing->courier_id,
                        'courier_name' => $existing->courier->name,
                        'courier_phone' => $existing->courier->phone,
                        'status' => $existing->status,
                        'status_label' => $existing->status_label,
                        'status_color' => $existing->status_color,
                    ],
                    'shift' => $scheduledShift->fresh(['district', 'activeAssignments'])->toCalendarEvent(),
                ]);
            }

            // Aktif atama varsa hata döndür
            if (!$request->wantsJson()) {
                return redirect()->route('panel.schedule.shifts.page', $scheduledShift)->with('error', 'Bu kurye zaten bu vardiyaya atanmış.');
            }
            return response()->json([
                'success' => false,
                'message' => 'Bu kurye zaten bu vardiyaya atanmış.',
            ], 422);
        }

        // Kapasite kontrolü
        if ($scheduledShift->isFullyStaffed()) {
            if (!$request->wantsJson()) {
                return redirect()->route('panel.schedule.shifts.page', $scheduledShift)->with('error', 'Vardiya kapasitesi dolu.');
            }
            return response()->json([
                'success' => false,
                'message' => 'Vardiya kapasitesi dolu.',
            ], 422);
        }

        $assignment = ShiftAssignment::create([
            'scheduled_shift_id' => $scheduledShift->id,
            'courier_id' => $validated['courier_id'],
            'assigned_by' => auth()->id(),
            'status' => ShiftAssignment::STATUS_ASSIGNED,
            'notes' => $validated['notes'] ?? null,
        ]);

        $assignment->load('courier');

        if (!$request->wantsJson()) {
            return redirect()->route('panel.schedule.shifts.page', $scheduledShift)->with('success', 'Kurye başarıyla atandı.');
        }
        return response()->json([
            'success' => true,
            'message' => 'Kurye başarıyla atandı.',
            'assignment' => [
                'id' => $assignment->id,
                'courier_id' => $assignment->courier_id,
                'courier_name' => $assignment->courier->name,
                'courier_phone' => $assignment->courier->phone,
                'status' => $assignment->status,
                'status_label' => $assignment->status_label,
                'status_color' => $assignment->status_color,
            ],
            'shift' => $scheduledShift->fresh(['district', 'activeAssignments'])->toCalendarEvent(),
        ]);
    }

    /**
     * Kurye atamasını kaldır
     */
    public function unassignCourier(Request $request, ScheduledShift $scheduledShift, ShiftAssignment $assignment)
    {
        if ($assignment->scheduled_shift_id !== $scheduledShift->id) {
            abort(404);
        }

        $assignment->cancel($request->get('reason'));

        if (!$request->wantsJson()) {
            return redirect()->route('panel.schedule.shifts.page', $scheduledShift)->with('success', 'Kurye ataması kaldırıldı.');
        }
        return response()->json([
            'success' => true,
            'message' => 'Kurye ataması kaldırıldı.',
            'shift' => $scheduledShift->fresh(['district', 'activeAssignments'])->toCalendarEvent(),
        ]);
    }

    /**
     * Kurye atamasını courier_id ile kaldır
     */
    public function unassignCourierById(Request $request, ScheduledShift $scheduledShift)
    {
        $validated = $request->validate([
            'courier_id' => 'required|exists:users,id',
        ]);

        $assignment = ShiftAssignment::where('scheduled_shift_id', $scheduledShift->id)
            ->where('courier_id', $validated['courier_id'])
            ->whereNotIn('status', ['cancelled'])
            ->first();

        if (!$assignment) {
            if (!$request->wantsJson()) {
                return redirect()->route('panel.schedule.shifts.page', $scheduledShift)->with('error', 'Bu kurye vardiyaya atanmamış.');
            }
            return response()->json([
                'success' => false,
                'message' => 'Bu kurye vardiyaya atanmamış.',
            ], 404);
        }

        $assignment->cancel($request->get('reason'));

        if (!$request->wantsJson()) {
            return redirect()->route('panel.schedule.shifts.page', $scheduledShift)->with('success', 'Kurye ataması kaldırıldı.');
        }
        return response()->json([
            'success' => true,
            'message' => 'Kurye ataması kaldırıldı.',
        ]);
    }

    /**
     * Kuryeyi erken bitir (kurye değişikliği için)
     */
    public function endCourierEarly(Request $request, ScheduledShift $scheduledShift)
    {
        $validated = $request->validate([
            'courier_id' => 'required|exists:users,id',
            'end_time' => 'required|date_format:H:i',
            'reason' => 'nullable|string|max:500',
        ]);

        $assignment = ShiftAssignment::where('scheduled_shift_id', $scheduledShift->id)
            ->where('courier_id', $validated['courier_id'])
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif kurye ataması bulunamadı.',
            ], 404);
        }

        // Bitiş saati geçerlilik kontrolü
        $shiftStart = Carbon::parse($scheduledShift->start_time);
        $shiftEnd = Carbon::parse($scheduledShift->end_time);
        $endTime = Carbon::parse($validated['end_time']);
        
        $effectiveStart = $assignment->actual_start_time 
            ? Carbon::parse($assignment->actual_start_time) 
            : $shiftStart;

        if ($endTime->lte($effectiveStart)) {
            return response()->json([
                'success' => false,
                'message' => 'Bitiş saati başlangıç saatinden sonra olmalıdır.',
            ], 422);
        }

        if ($endTime->gt($shiftEnd)) {
            return response()->json([
                'success' => false,
                'message' => 'Bitiş saati vardiya bitiş saatinden sonra olamaz.',
            ], 422);
        }

        // Kuryeyi erken bitir
        $assignment->endEarly($validated['end_time'], $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Kurye vardiyası erken bitirildi.',
            'assignment' => [
                'id' => $assignment->id,
                'courier_name' => $assignment->courier->name,
                'start_time' => $assignment->effective_start_time,
                'end_time' => $assignment->effective_end_time,
                'worked_hours' => $assignment->worked_hours,
                'worked_duration' => $assignment->worked_duration,
            ],
            'remaining_start_time' => $validated['end_time'],
            'remaining_end_time' => Carbon::parse($scheduledShift->end_time)->format('H:i'),
        ]);
    }

    /**
     * Kalan süre için yeni kurye ata (kurye değişikliği için)
     */
    public function assignCourierWithStartTime(Request $request, ScheduledShift $scheduledShift)
    {
        $validated = $request->validate([
            'courier_id' => 'required|exists:users,id',
            'start_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        // Başlangıç saati geçerlilik kontrolü
        $shiftStart = Carbon::parse($scheduledShift->start_time);
        $shiftEnd = Carbon::parse($scheduledShift->end_time);
        $startTime = Carbon::parse($validated['start_time']);

        if ($startTime->lt($shiftStart)) {
            return response()->json([
                'success' => false,
                'message' => 'Başlangıç saati vardiya başlangıcından önce olamaz.',
            ], 422);
        }

        if ($startTime->gte($shiftEnd)) {
            return response()->json([
                'success' => false,
                'message' => 'Başlangıç saati vardiya bitişinden önce olmalıdır.',
            ], 422);
        }

        // Bu kurye zaten bu vardiyaya atanmış mı?
        $existing = ShiftAssignment::where('scheduled_shift_id', $scheduledShift->id)
            ->where('courier_id', $validated['courier_id'])
            ->whereNotIn('status', ['cancelled'])
            ->first();

        if ($existing && !$existing->hasEndedEarly()) {
            return response()->json([
                'success' => false,
                'message' => 'Bu kurye zaten bu vardiyaya atanmış.',
            ], 422);
        }

        // Aynı gün ve saat aralığında çakışan vardiya kontrolü
        // Yeni kuryenin başlangıç saatinden vardiya bitiş saatine kadar olan aralık kontrol edilir
        $newCourierStartTime = $validated['start_time'];
        $shiftEndTimeFormatted = Carbon::parse($scheduledShift->end_time)->format('H:i');
        
        $overlappingShift = ScheduledShift::where('id', '!=', $scheduledShift->id)
            ->whereDate('shift_date', $scheduledShift->shift_date)
            ->whereHas('activeAssignments', function($q) use ($validated) {
                $q->where('courier_id', $validated['courier_id']);
            })
            ->where('start_time', '<', $shiftEndTimeFormatted)
            ->where('end_time', '>', $newCourierStartTime)
            ->with('region')
            ->first();

        if ($overlappingShift) {
            $regionName = $overlappingShift->region->name ?? 'Bilinmeyen Bölge';
            $overlapStart = Carbon::parse($overlappingShift->start_time)->format('H:i');
            $overlapEnd = Carbon::parse($overlappingShift->end_time)->format('H:i');
            
            return response()->json([
                'success' => false,
                'message' => "Bu kurye aynı saatlerde başka bir vardiyaya atanmış: {$regionName} ({$overlapStart} - {$overlapEnd})",
            ], 422);
        }

        // Yeni atama oluştur
        $assignment = ShiftAssignment::create([
            'scheduled_shift_id' => $scheduledShift->id,
            'courier_id' => $validated['courier_id'],
            'assigned_by' => auth()->id(),
            'status' => ShiftAssignment::STATUS_ASSIGNED,
            'actual_start_time' => $validated['start_time'],
            'notes' => $validated['notes'] ?? 'Kurye değişikliği ile atandı',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kurye başarıyla atandı.',
            'assignment' => [
                'id' => $assignment->id,
                'courier_id' => $assignment->courier_id,
                'courier_name' => $assignment->courier->name,
                'start_time' => $assignment->effective_start_time,
                'end_time' => $assignment->effective_end_time,
            ],
        ]);
    }

    /**
     * Toplu bölge vardiyası oluştur (birden fazla bölge için aynı anda)
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'region_ids' => 'required|array|min:1',
            'region_ids.*' => 'exists:regions,id',
            'shift_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'required_couriers' => 'required|integer|min:1|max:50',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $createdCount = 0;

        foreach ($validated['region_ids'] as $regionId) {
            ScheduledShift::create([
                'region_id' => $regionId,
                'shift_date' => $validated['shift_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'required_couriers' => $validated['required_couriers'],
                'title' => $validated['title'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'status' => ScheduledShift::STATUS_PUBLISHED,
                'color' => '#3B82F6',
            ]);
            $createdCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$createdCount} vardiya başarıyla oluşturuldu.",
            'created_count' => $createdCount,
        ]);
    }

    /**
     * Toplu vardiya oluştur (bir hafta boyunca tekrarla vb.)
     */
    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'required_couriers' => 'required|integer|min:1|max:50',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:20',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $createdCount = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            ScheduledShift::create([
                'region_id' => $validated['region_id'],
                'created_by' => auth()->id(),
                'shift_date' => $currentDate->format('Y-m-d'),
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'required_couriers' => $validated['required_couriers'],
                'status' => ScheduledShift::STATUS_PUBLISHED,
                'title' => $validated['title'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'color' => $validated['color'] ?? '#3B82F6',
            ]);
            $createdCount++;
            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'message' => "{$createdCount} vardiya başarıyla oluşturuldu.",
            'created_count' => $createdCount,
        ]);
    }

    /**
     * Excel vardiya şablonu indir (tek sayfada tüm bölgeler için örnek satırlar)
     */
    public function shiftTemplateDownload(): StreamedResponse
    {
        $regions = Region::active()->orderBy('city')->orderBy('name')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Vardiya Planı');

        // Başlık satırı
        $sheet->setCellValue('A1', 'Tarih');
        $sheet->setCellValue('B1', 'Kurye T.C');
        $sheet->setCellValue('C1', 'Bölge');
        $sheet->setCellValue('D1', 'Başlangıç Saati');
        $sheet->setCellValue('E1', 'Bitiş Saati');

        $exampleDate = now()->addDay()->format('d.m.Y');
        $row = 2;

        foreach ($regions as $region) {
            // Her bölge için iki örnek vardiya (sabah ve öğleden sonra)
            $sheet->setCellValue('A' . $row, $exampleDate);
            $sheet->setCellValue('B' . $row, '12345678901');
            $sheet->setCellValue('C' . $row, $region->name);
            $sheet->setCellValue('D' . $row, '09:00');
            $sheet->setCellValue('E' . $row, '13:00');
            $row++;

            $sheet->setCellValue('A' . $row, $exampleDate);
            $sheet->setCellValue('B' . $row, '12345678902');
            $sheet->setCellValue('C' . $row, $region->name);
            $sheet->setCellValue('D' . $row, '14:00');
            $sheet->setCellValue('E' . $row, '22:00');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'vardiya_plani_sablonu_' . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Excel/CSV ile vardiya yükle (Tarih, Kurye T.C, Bölge, Başlangıç Saati, Bitiş Saati)
     * Kurye employee_code (T.C) ile eşleştirilir.
     */
    public function shiftUploadFromExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:5120',
        ], [
            'file.required' => 'Lütfen bir dosya seçin.',
            'file.mimes' => 'Dosya türü Excel (.xlsx) veya CSV olmalıdır.',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            if ($extension === 'csv') {
                $reader = IOFactory::createReader('Csv');
                $reader->setDelimiter($this->detectCsvDelimiter($file));
                $reader->setEnclosure('"');
                $spreadsheet = $reader->load($file->getRealPath());
            } else {
                $spreadsheet = IOFactory::load($file->getRealPath());
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Dosya okunamadı: ' . $e->getMessage());
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $header = array_shift($rows);

        $parsed = [];
        $regionNames = Region::active()->pluck('name')->flip()->all();

        foreach ($rows as $index => $row) {
            $dateStr = trim((string) ($row[0] ?? ''));
            $tc = $this->normalizeTc((string) ($row[1] ?? ''));
            $regionName = trim((string) ($row[2] ?? ''));
            $startTime = $this->normalizeTime((string) ($row[3] ?? ''));
            $endTime = $this->normalizeTime((string) ($row[4] ?? ''));

            if ($dateStr === '' && $regionName === '' && $tc === '' && $startTime === '' && $endTime === '') {
                continue;
            }
            if ($dateStr === '' || $regionName === '' || $tc === '' || $startTime === '' || $endTime === '') {
                continue;
            }

            $date = $this->parseDate($dateStr);
            if (!$date) {
                continue;
            }

            $parsed[] = [
                'date' => $date,
                'tc' => $tc,
                'region_name' => $regionName,
                'start_time' => $this->normalizeTimeToHis($startTime),
                'end_time' => $this->normalizeTimeToHis($endTime),
            ];
        }

        if (empty($parsed)) {
            return redirect()->back()->with('error', 'Geçerli satır bulunamadı. Sütun sırası: Tarih, Kurye T.C, Bölge, Başlangıç Saati, Bitiş Saati. Tarih örnek: 26.02.2025 veya 2025-02-26. Saat örnek: 09:00.');
        }

        // Grupla: (tarih, bölge, başlangıç, bitiş) -> [tc1, tc2, ...]
        $groups = [];
        foreach ($parsed as $item) {
            $key = $item['date'] . '|' . $item['region_name'] . '|' . $item['start_time'] . '|' . $item['end_time'];
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'date' => $item['date'],
                    'region_name' => $item['region_name'],
                    'start_time' => $item['start_time'],
                    'end_time' => $item['end_time'],
                    'tcs' => [],
                ];
            }
            if (!in_array($item['tc'], $groups[$key]['tcs'], true)) {
                $groups[$key]['tcs'][] = $item['tc'];
            }
        }

        $createdShifts = 0;
        $createdAssignments = 0;
        $errors = [];
        $skippedTc = [];
        $skippedRegion = [];

        foreach ($groups as $group) {
            $region = Region::active()->where('name', $group['region_name'])->first();
            if (!$region) {
                $skippedRegion[$group['region_name']] = true;
                continue;
            }

            $shift = ScheduledShift::firstOrCreate(
                [
                    'region_id' => $region->id,
                    'shift_date' => $group['date'],
                    'start_time' => $group['start_time'],
                    'end_time' => $group['end_time'],
                ],
                [
                    'required_couriers' => max(1, count($group['tcs'])),
                    'created_by' => auth()->id(),
                    'status' => ScheduledShift::STATUS_PUBLISHED,
                    'color' => $region->color ?? '#3B82F6',
                ]
            );
            if ($shift->wasRecentlyCreated) {
                $createdShifts++;
            }

            foreach ($group['tcs'] as $tc) {
                $courier = User::active()
                    ->whereHas('role', fn ($q) => $q->where('name', Role::COURIER))
                    ->where('employee_code', $tc)
                    ->first();
                if (!$courier) {
                    $skippedTc[$tc] = true;
                    continue;
                }

                // Bölgeye atanmış mı kontrolü (opsiyonel: aynı bölgede çalışabilir kurye)
                $inRegion = $courier->courierRegions()->where('region_id', $region->id)->exists();
                if (!$inRegion) {
                    $errors[] = "T.C {$tc} ({$courier->name}) '{$group['region_name']}' bölgesine atanmamış; atama yapıldı.";
                }

                $existing = ShiftAssignment::where('scheduled_shift_id', $shift->id)
                    ->where('courier_id', $courier->id)
                    ->whereNotIn('status', [ShiftAssignment::STATUS_CANCELLED])
                    ->exists();
                if ($existing) {
                    continue;
                }

                // Aynı gün çakışan vardiya kontrolü
                $overlap = ScheduledShift::where('id', '!=', $shift->id)
                    ->whereDate('shift_date', $group['date'])
                    ->whereHas('activeAssignments', fn ($q) => $q->where('courier_id', $courier->id))
                    ->where('start_time', '<', $group['end_time'])
                    ->where('end_time', '>', $group['start_time'])
                    ->first();
                if ($overlap) {
                    $errors[] = "T.C {$tc} aynı saatlerde başka vardiyada: atlandı.";
                    continue;
                }

                if ($shift->isFullyStaffed() && !$shift->assignments()->where('courier_id', $courier->id)->exists()) {
                    $shift->update(['required_couriers' => $shift->required_couriers + 1]);
                }

                ShiftAssignment::create([
                    'scheduled_shift_id' => $shift->id,
                    'courier_id' => $courier->id,
                    'assigned_by' => auth()->id(),
                    'status' => ShiftAssignment::STATUS_ASSIGNED,
                ]);
                $createdAssignments++;
            }
        }

        $message = "{$createdShifts} vardiya oluşturuldu/güncellendi, {$createdAssignments} atama yapıldı.";
        if (!empty($skippedRegion)) {
            $message .= ' Bilinmeyen bölge: ' . implode(', ', array_keys($skippedRegion));
        }
        if (!empty($skippedTc)) {
            $message .= ' Bulunamayan T.C: ' . implode(', ', array_keys($skippedTc));
        }
        if (!empty($errors)) {
            $message .= ' Uyarılar: ' . implode(' ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' ...';
            }
        }

        return redirect()->route('panel.schedule.index')->with(
            $createdAssignments > 0 || $createdShifts > 0 ? 'success' : 'warning',
            $message
        );
    }

    private function detectCsvDelimiter($file): string
    {
        $line = fgets(fopen($file->getRealPath(), 'r'));
        if (strpos($line, ';') !== false) {
            return ';';
        }
        return ',';
    }

    private function normalizeTc(string $value): string
    {
        $value = preg_replace('/\D/', '', $value);
        return strlen($value) === 11 ? $value : '';
    }

    private function normalizeTime(string $value): string
    {
        $value = trim($value);
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $value, $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }
        return '';
    }

    /** Saati veritabanı ile eşleşmesi için H:i:s yap */
    private function normalizeTimeToHis(string $value): string
    {
        $hi = $this->normalizeTime($value);
        return $hi ? $hi . ':00' : '';
    }

    private function parseDate(string $value): ?string
    {
        $value = trim($value);
        foreach (['d.m.Y', 'Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            $d = Carbon::createFromFormat($format, $value);
            if ($d instanceof Carbon) {
                return $d->format('Y-m-d');
            }
        }
        if (is_numeric($value)) {
            try {
                $d = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $d ? $d->format('Y-m-d') : null;
            } catch (\Throwable $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Gün görünümü - belirli bir günün detaylı görünümü
     */
    public function dayView(Request $request, $date)
    {
        $date = Carbon::parse($date);
        $districtId = $request->get('district_id');

        $query = ScheduledShift::with(['districts', 'assignments.courier'])
            ->forDate($date)
            ->orderBy('start_time');

        if ($districtId) {
            $query->forDistrict($districtId);
        }

        $shifts = $query->get();
        $districts = District::active()->orderBy('name')->get();

        // Uygun kuryeler
        $couriers = User::active()
            ->whereHas('role', fn($q) => $q->where('name', Role::COURIER))
            ->orderBy('name')
            ->get();

        return view('panel.schedule.day', compact('date', 'shifts', 'districts', 'couriers'));
    }
}
