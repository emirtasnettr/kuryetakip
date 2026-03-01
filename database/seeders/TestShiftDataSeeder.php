<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\User;
use App\Models\Role;
use App\Models\District;
use App\Models\ScheduledShift;
use App\Models\ShiftAssignment;
use App\Models\Shift;
use App\Models\ShiftPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Test Vardiya Verisi Seeder
 *
 * Her bölge için farklı kuryelere vardiya atamaları ve vardiya girişleri (fiili Shift)
 * oluşturur. Yazılımı incelemek için test verileriyle doldurur.
 */
class TestShiftDataSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = 1;
        $courierRole = Role::where('name', Role::COURIER)->first();
        if (!$courierRole) {
            $this->command->error('Kurye rolü bulunamadı. Önce RoleSeeder çalıştırın.');
            return;
        }

        $regions = Region::active()->get();
        if ($regions->isEmpty()) {
            $this->command->error('Bölge bulunamadı. Önce RegionSeeder çalıştırın.');
            return;
        }

        $istanbulDistrictIds = District::where('city', 'İstanbul')->pluck('id');
        $defaultDistrictId = $istanbulDistrictIds->first();

        // Her bölge için 2-3 test kuryesi oluştur (bölgeye özel)
        $regionCouriers = $this->ensureCouriersPerRegion($regions, $courierRole, $adminId);

        // Planlı vardiya saat dilimleri
        $slots = [
            ['start' => '08:00', 'end' => '12:00'],
            ['start' => '12:00', 'end' => '16:00'],
            ['start' => '16:00', 'end' => '20:00'],
        ];

        $colors = array_keys(ScheduledShift::COLORS);
        $createdShifts = 0;
        $createdAssignments = 0;
        $createdShiftEntries = 0;

        $this->ensurePlaceholderPhotoExists();

        // Dün, bugün, yarın, +2 gün için vardiyalar
        foreach ([-1, 0, 1, 2] as $dayOffset) {
            $date = Carbon::today()->addDays($dayOffset);

            foreach ($regions as $region) {
                $couriers = $regionCouriers->get($region->id);
                if (!$couriers || $couriers->isEmpty()) {
                    continue;
                }

                foreach ($slots as $slotIndex => $slot) {
                    $shift = ScheduledShift::create([
                        'region_id' => $region->id,
                        'district_id' => $defaultDistrictId,
                        'created_by' => $adminId,
                        'shift_date' => $date->format('Y-m-d'),
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'required_couriers' => min(2, $couriers->count()),
                        'status' => ScheduledShift::STATUS_PUBLISHED,
                        'title' => null,
                        'notes' => 'Test verisi',
                        'color' => $colors[$slotIndex % count($colors)],
                    ]);
                    $createdShifts++;

                    $toAssign = $couriers->shuffle()->take($shift->required_couriers);

                    foreach ($toAssign as $courier) {
                        $assignment = ShiftAssignment::create([
                            'scheduled_shift_id' => $shift->id,
                            'courier_id' => $courier->id,
                            'assigned_by' => $adminId,
                            'status' => ShiftAssignment::STATUS_ASSIGNED,
                        ]);
                        $createdAssignments++;

                        // Geçmiş ve bugünün geçmiş saatleri için vardiya girişi (fiili Shift) oluştur
                        $shiftDate = Carbon::parse($shift->shift_date);
                        $startDt = $shiftDate->copy()->setTimeFromTimeString($slot['start']);
                        $endDt = $shiftDate->copy()->setTimeFromTimeString($slot['end']);

                        if ($dayOffset < 0 || ($dayOffset === 0 && $endDt->lt(now()))) {
                            $actualShift = Shift::create([
                                'user_id' => $courier->id,
                                'district_id' => $defaultDistrictId,
                                'status' => Shift::STATUS_COMPLETED,
                                'started_at' => $startDt,
                                'start_latitude' => 41.0 + (rand(0, 99) / 1000),
                                'start_longitude' => 29.0 + (rand(0, 99) / 1000),
                                'start_address' => "Test başlangıç - {$region->name}",
                                'ended_at' => $endDt,
                                'end_latitude' => 41.0 + (rand(0, 99) / 1000),
                                'end_longitude' => 29.0 + (rand(0, 99) / 1000),
                                'end_address' => "Test bitiş - {$region->name}",
                                'package_count' => rand(5, 25),
                                'total_minutes' => (int) $startDt->diffInMinutes($endDt),
                            ]);

                            $assignment->update([
                                'status' => ShiftAssignment::STATUS_COMPLETED,
                                'actual_shift_id' => $actualShift->id,
                                'confirmed_at' => $startDt,
                                'started_at' => $startDt,
                                'completed_at' => $endDt,
                                'actual_start_time' => $slot['start'],
                                'actual_end_time' => $slot['end'],
                            ]);
                            $this->attachPlaceholderPhotosToShift($actualShift);
                            $createdShiftEntries++;
                        }
                    }
                }
            }
        }

        $this->command->info('Test vardiya verileri oluşturuldu:');
        $this->command->info("  - {$createdShifts} planlı vardiya (ScheduledShift)");
        $this->command->info("  - {$createdAssignments} kurye ataması (ShiftAssignment)");
        $this->command->info("  - {$createdShiftEntries} vardiya girişi (Shift - fiili giriş)");
    }

    private const PLACEHOLDER_PATH = 'shifts/placeholder.png';

    private function ensurePlaceholderPhotoExists(): void
    {
        $disk = Storage::disk('public');
        if ($disk->exists(self::PLACEHOLDER_PATH)) {
            return;
        }
        $disk->makeDirectory('shifts');
        $disk->put(self::PLACEHOLDER_PATH, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));
    }

    private function attachPlaceholderPhotosToShift(Shift $shift): void
    {
        ShiftPhoto::create([
            'shift_id' => $shift->id,
            'type' => ShiftPhoto::TYPE_START,
            'filename' => 'placeholder.png',
            'path' => self::PLACEHOLDER_PATH,
            'disk' => 'public',
            'mime_type' => 'image/png',
            'size' => 68,
        ]);
        ShiftPhoto::create([
            'shift_id' => $shift->id,
            'type' => ShiftPhoto::TYPE_END,
            'filename' => 'placeholder.png',
            'path' => self::PLACEHOLDER_PATH,
            'disk' => 'public',
            'mime_type' => 'image/png',
            'size' => 68,
        ]);
    }

    /**
     * Her bölge için en az 2 kurye sağla; yoksa bölgeye özel test kuryeleri oluşturur.
     */
    private function ensureCouriersPerRegion($regions, $courierRole, $adminId): \Illuminate\Support\Collection
    {
        $regionCouriers = collect();
        $usedEmails = [];

        foreach ($regions as $region) {
            $existing = User::whereHas('courierRegions', fn($q) => $q->where('region_id', $region->id))
                ->whereHas('role', fn($q) => $q->where('name', Role::COURIER))
                ->get();

            if ($existing->count() >= 2) {
                $regionCouriers[$region->id] = $existing;
                continue;
            }

            $need = 2 - $existing->count();
            $names = [
                ['Adı' => 'Ali', 'Soyadı' => 'Veli'],
                ['Adı' => 'Ayşe', 'Soyadı' => 'Yılmaz'],
                ['Adı' => 'Fatma', 'Soyadı' => 'Demir'],
                ['Adı' => 'Zeynep', 'Soyadı' => 'Kaya'],
                ['Adı' => 'Emre', 'Soyadı' => 'Çelik'],
            ];
            $slug = \Str::slug($region->name);
            $base = substr($slug, 0, 8);

            for ($i = 0; $i < $need; $i++) {
                $n = $names[($region->id + $i) % count($names)];
                $name = $n['Adı'] . ' ' . $n['Soyadı'];
                $email = $base . '.kurye' . ($i + 1) . '@test.com';
                if (in_array($email, $usedEmails)) {
                    $email = $base . $region->id . '.k' . $i . '@test.com';
                }
                $usedEmails[] = $email;

                $courier = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'phone' => '05' . rand(30, 59) . ' ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                    'role_id' => $courierRole->id,
                    'employee_code' => 'T' . $region->id . rand(100, 999),
                    'vehicle_type' => ['Motosiklet', 'Bisiklet'][rand(0, 1)],
                    'vehicle_plate' => '34 ' . chr(rand(65, 90)) . chr(rand(65, 90)) . ' ' . rand(100, 999),
                    'is_active' => true,
                ]);

                $courier->courierRegions()->attach($region->id, [
                    'is_primary' => $existing->isEmpty() && $i === 0,
                ]);

                $existing = $existing->push($courier);
            }

            $regionCouriers[$region->id] = $existing;
        }

        return $regionCouriers;
    }
}
