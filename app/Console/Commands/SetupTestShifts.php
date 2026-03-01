<?php

namespace App\Console\Commands;

use App\Models\ScheduledShift;
use App\Models\ShiftAssignment;
use App\Models\Region;
use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SetupTestShifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shifts:setup-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bugünün vardiyalarını sil ve önümüzdeki 3 gün için test vardiyaları oluştur';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Test vardiyaları oluşturuluyor...');

        // 1. Bugünün tarihine ait vardiyaları sil
        $today = Carbon::today();
        $todayShifts = ScheduledShift::whereDate('shift_date', $today)->get();
        
        if ($todayShifts->count() > 0) {
            $this->info("Bugün ({$today->format('d.m.Y')}) için {$todayShifts->count()} vardiya siliniyor...");
            foreach ($todayShifts as $shift) {
                // ShiftAssignment'ları da sil
                $shift->assignments()->delete();
                $shift->delete();
            }
            $this->info('✓ Bugünün vardiyaları silindi');
        } else {
            $this->info('Bugün için vardiya bulunamadı');
        }

        // Önümüzdeki 3 gün için mevcut vardiyaları da temizle (yeniden oluşturmak için)
        $futureStart = Carbon::today()->addDay();
        $futureEnd = Carbon::today()->addDays(3);
        $futureShifts = ScheduledShift::whereBetween('shift_date', [$futureStart, $futureEnd])->get();
        
        if ($futureShifts->count() > 0) {
            $this->info("Önümüzdeki 3 gün için {$futureShifts->count()} vardiya temizleniyor...");
            foreach ($futureShifts as $shift) {
                $shift->assignments()->delete();
                $shift->delete();
            }
            $this->info('✓ Gelecek vardiyalar temizlendi');
        }

        // 2. Aktif bölgeleri ve kuryeleri al
        $regions = Region::active()->get();
        if ($regions->isEmpty()) {
            $this->error('Aktif bölge bulunamadı!');
            return 1;
        }

        $couriers = User::active()
            ->whereHas('role', fn($q) => $q->where('name', Role::COURIER))
            ->get();

        if ($couriers->isEmpty()) {
            $this->error('Aktif kurye bulunamadı!');
            return 1;
        }

        // "Kurye 1" adında kurye bul
        $courier1 = $couriers->firstWhere('name', 'Kurye 1');
        if (!$courier1) {
            // İlk kuryeyi Kurye 1 olarak kullan
            $courier1 = $couriers->first();
            $this->warn("'Kurye 1' bulunamadı, '{$courier1->name}' kullanılıyor");
        }

        $this->info("✓ {$regions->count()} bölge ve {$couriers->count()} kurye bulundu");
        $this->info("✓ '{$courier1->name}' için özel vardiyalar oluşturulacak");

        // 3. Önümüzdeki 3 gün için vardiyalar oluştur
        // Her bölge için günde 3 vardiya: sabah, öğle, akşam
        $shiftTimeSlots = [
            ['start' => '08:00', 'end' => '12:00'],
            ['start' => '12:00', 'end' => '16:00'],
            ['start' => '16:00', 'end' => '20:00'],
        ];

        $createdShifts = 0;
        $createdAssignments = 0;

        // Kurye 1 (Emir Taş) için farklı saatlerde vardiya atamak için
        $courier1ShiftSlots = [
            0 => 0, // 1. gün: 08:00-12:00
            1 => 1, // 2. gün: 12:00-16:00
            2 => 2, // 3. gün: 16:00-20:00
        ];

        // Kurye 1'in hangi günde vardiya aldığını takip et
        $courier1AssignedDays = [];

        for ($dayOffset = 1; $dayOffset <= 3; $dayOffset++) {
            $date = Carbon::today()->addDays($dayOffset);
            $this->info("\n--- {$date->translatedFormat('d F Y, l')} ---");

            // Kurye 1 için bu gün vardiya atanacak mı?
            $courier1DaySlot = isset($courier1ShiftSlots[$dayOffset - 1]) ? $courier1ShiftSlots[$dayOffset - 1] : null;
            $courier1AssignedForDay = false;
            $courier1AssignedRegion = null;

            foreach ($regions as $region) {
                // Her bölge için günde tam 3 vardiya oluştur (sabah, öğle, akşam)
                $selectedSlots = [0, 1, 2]; // İlk 3 slot: sabah, öğle, akşam

                // Kurye 1 için bu gün vardiya oluşturulacak mı kontrol et
                // Eğer bu gün Kurye 1'in vardiya günüyse ve henüz atanmadıysa, ilk uygun bölgede vardiya oluştur
                if ($courier1DaySlot !== null && !$courier1AssignedForDay && in_array($courier1DaySlot, $selectedSlots) && $courier1AssignedRegion === null) {
                    // Kurye 1 için özel vardiya oluştur
                    $courier1Slot = $shiftTimeSlots[$courier1DaySlot];
                    $courier1Shift = ScheduledShift::create([
                        'region_id' => $region->id,
                        'shift_date' => $date->format('Y-m-d'),
                        'start_time' => $courier1Slot['start'],
                        'end_time' => $courier1Slot['end'],
                        'required_couriers' => rand(1, 2),
                        'title' => null, // Başlık yok
                        'status' => ScheduledShift::STATUS_PUBLISHED,
                        'color' => $this->getRandomColor(),
                        'created_by' => 1,
                    ]);

                    // Kurye 1'i ata
                    $courier1InRegion = $courier1->courierRegions()->where('region_id', $region->id)->exists();
                    if (!$courier1InRegion) {
                        $courier1->courierRegions()->attach($region->id, ['is_primary' => false]);
                    }

                    ShiftAssignment::create([
                        'scheduled_shift_id' => $courier1Shift->id,
                        'courier_id' => $courier1->id,
                        'assigned_by' => 1,
                        'status' => ShiftAssignment::STATUS_ASSIGNED,
                    ]);
                    $createdShifts++;
                    $createdAssignments++;
                    $courier1AssignedForDay = true;

                    // Diğer kuryeleri de ata
                    $requiredCouriers = $courier1Shift->required_couriers - 1;
                    $courier1ShiftStartTime = Carbon::parse($courier1Shift->start_time)->format('H:i');
                    $courier1ShiftEndTime = Carbon::parse($courier1Shift->end_time)->format('H:i');
                    
                    $availableCouriers = $couriers
                        ->where('id', '!=', $courier1->id)
                        ->filter(function($courier) use ($region, $date, $courier1ShiftStartTime, $courier1ShiftEndTime, $courier1Shift) {
                            $inRegion = $courier->courierRegions()->where('region_id', $region->id)->exists();
                            if (!$inRegion) {
                                $courier->courierRegions()->attach($region->id, ['is_primary' => false]);
                            }
                            
                            // Aynı gün ve saatte başka vardiyası var mı kontrol et
                            $overlappingShift = ScheduledShift::where('id', '!=', $courier1Shift->id)
                                ->whereDate('shift_date', $date->format('Y-m-d'))
                                ->whereHas('activeAssignments', function($q) use ($courier) {
                                    $q->where('courier_id', $courier->id);
                                })
                                ->where('start_time', '<', $courier1ShiftEndTime)
                                ->where('end_time', '>', $courier1ShiftStartTime)
                                ->exists();
                            
                            return !$overlappingShift;
                        })
                        ->shuffle()
                        ->take($requiredCouriers);

                    foreach ($availableCouriers as $courier) {
                        ShiftAssignment::create([
                            'scheduled_shift_id' => $courier1Shift->id,
                            'courier_id' => $courier->id,
                            'assigned_by' => 1,
                            'status' => ShiftAssignment::STATUS_ASSIGNED,
                        ]);
                        $createdAssignments++;
                    }

                    $assignedCount = $courier1Shift->assignments()->count();
                    $this->info("  ✓ {$region->name}: {$courier1Slot['start']}-{$courier1Slot['end']} ({$assignedCount}/{$courier1Shift->required_couriers} kurye) [{$courier1->name}]");
                    $courier1AssignedRegion = $region->id;
                }

                foreach ($selectedSlots as $slotIndex) {
                    // Kurye 1 için özel vardiya bu bölgede zaten oluşturulduysa, aynı saatte başka vardiya oluşturma
                    // Ama diğer bölgelerde aynı saatte vardiya oluşturulabilir
                    if ($courier1DaySlot !== null && $slotIndex == $courier1DaySlot && $courier1AssignedForDay && $courier1AssignedRegion == $region->id) {
                        continue;
                    }

                    $slot = $shiftTimeSlots[$slotIndex];
                    
                    // Vardiya oluştur
                    $shift = ScheduledShift::create([
                        'region_id' => $region->id,
                        'shift_date' => $date->format('Y-m-d'),
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'required_couriers' => rand(1, 3),
                        'title' => null, // Başlık yok
                        'status' => ScheduledShift::STATUS_PUBLISHED,
                        'color' => $this->getRandomColor(),
                        'created_by' => 1, // Sistem yöneticisi
                    ]);

                    $createdShifts++;

                    // Vardiyaya kuryeler ata
                    $requiredCouriers = $shift->required_couriers;
                    $assignedCouriers = collect();

                    // Çakışma kontrolü için vardiya saatleri
                    $shiftStartTime = Carbon::parse($shift->start_time)->format('H:i');
                    $shiftEndTime = Carbon::parse($shift->end_time)->format('H:i');

                    // Kalan kuryeleri rastgele ata
                    $availableCouriers = $couriers
                        ->where('id', '!=', $courier1?->id)
                        ->filter(function($courier) use ($region, $assignedCouriers, $date, $shiftStartTime, $shiftEndTime, $shift) {
                            // Bölge kontrolü - eğer atanmamışsa yine de kullan (test için)
                            $inRegion = $courier->courierRegions()->where('region_id', $region->id)->exists();
                            if (!$inRegion) {
                                // Geçici olarak ata
                                $courier->courierRegions()->attach($region->id, ['is_primary' => false]);
                            }
                            
                            // Zaten bu vardiyaya atanmış mı?
                            if ($assignedCouriers->contains('id', $courier->id)) {
                                return false;
                            }
                            
                            // Aynı gün ve saatte başka vardiyası var mı kontrol et
                            $overlappingShift = ScheduledShift::where('id', '!=', $shift->id)
                                ->whereDate('shift_date', $date->format('Y-m-d'))
                                ->whereHas('activeAssignments', function($q) use ($courier) {
                                    $q->where('courier_id', $courier->id);
                                })
                                ->where('start_time', '<', $shiftEndTime)
                                ->where('end_time', '>', $shiftStartTime)
                                ->exists();
                            
                            return !$overlappingShift;
                        })
                        ->shuffle()
                        ->take($requiredCouriers);

                    foreach ($availableCouriers as $courier) {
                        ShiftAssignment::create([
                            'scheduled_shift_id' => $shift->id,
                            'courier_id' => $courier->id,
                            'assigned_by' => 1,
                            'status' => ShiftAssignment::STATUS_ASSIGNED,
                        ]);
                        $createdAssignments++;
                    }

                    $assignedCount = $shift->assignments()->count();
                    $this->info("  ✓ {$region->name}: {$slot['start']}-{$slot['end']} ({$assignedCount}/{$shift->required_couriers} kurye)");
                }
            }
        }

        $this->info("\n✓ Tamamlandı!");
        $this->info("  - {$createdShifts} vardiya oluşturuldu");
        $this->info("  - {$createdAssignments} kurye ataması yapıldı");
        
        if ($courier1) {
            $courier1Shifts = ShiftAssignment::where('courier_id', $courier1->id)
                ->whereHas('scheduledShift', function($q) {
                    $q->whereDate('shift_date', '>=', Carbon::today()->addDay())
                      ->whereDate('shift_date', '<=', Carbon::today()->addDays(3));
                })
                ->count();
            $this->info("  - '{$courier1->name}' için {$courier1Shifts} vardiya atandı");
        }

        return 0;
    }

    /**
     * Rastgele renk seç
     */
    private function getRandomColor(): string
    {
        $colors = array_keys(ScheduledShift::COLORS);
        return $colors[array_rand($colors)];
    }
}
