<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use App\Models\ScheduledShift;
use App\Models\ShiftAssignment;
use App\Models\Shift;
use App\Models\ShiftLog;
use App\Models\ShiftPhoto;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\ExtraBonus;
use App\Models\SettlementDeduction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Tüm alanlar için varyasyonlu test verisi doldurur.
 * Her modül ve filtre detaylı test edilebilsin diye zengin veri üretir.
 */
class FullTestDataSeeder extends Seeder
{
    private array $adminIds = [];
    private array $courierIds = [];
    private array $regionIds = [];
    private ?int $defaultDistrictId = null;

    public function run(): void
    {
        $this->command->info('FullTestDataSeeder başlıyor...');

        $this->ensureRoles();
        $this->ensureDistricts();
        $this->ensureUsers();
        $this->ensureRegions();
        $this->linkCouriersToRegionsAndDistricts();
        $this->ensureScheduledShifts();
        $this->ensureShiftAssignmentsAndActualShifts();
        $this->ensureShiftLogsAndPhotos();
        $this->ensureExpenseRequests();
        $this->ensureExtraBonusesAndDeductions();

        $this->command->info('FullTestDataSeeder tamamlandı.');
    }

    private function ensureRoles(): void
    {
        $this->call(RoleSeeder::class);
    }

    private function ensureDistricts(): void
    {
        $this->call(DistrictSeeder::class);
        $this->defaultDistrictId = District::where('city', 'İstanbul')->value('id');
    }

    private function ensureUsers(): void
    {
        $adminRole = Role::where('name', Role::SYSTEM_ADMIN)->first();
        $managerRole = Role::where('name', Role::OPERATION_MANAGER)->first();
        $specialistRole = Role::where('name', Role::OPERATION_SPECIALIST)->first();
        $partnerRole = Role::where('name', Role::BUSINESS_PARTNER)->first();
        $courierRole = Role::where('name', Role::COURIER)->first();

        $admin = User::updateOrCreate(
            ['email' => 'admin@kuryetakip.com'],
            [
                'name' => 'Sistem Yöneticisi',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]
        );
        $this->adminIds[] = $admin->id;

        $managers = [
            ['email' => 'yonetici1@test.com', 'name' => 'Operasyon Yöneticisi 1', 'phone' => '0532 111 1111'],
            ['email' => 'yonetici2@test.com', 'name' => 'Operasyon Yöneticisi 2', 'phone' => '0532 111 1112'],
        ];
        foreach ($managers as $m) {
            $u = User::updateOrCreate(
                ['email' => $m['email']],
                [
                    'name' => $m['name'],
                    'password' => Hash::make('password'),
                    'role_id' => $managerRole->id,
                    'phone' => $m['phone'],
                    'is_active' => true,
                ]
            );
            $this->adminIds[] = $u->id;
            $u->authorizedDistricts()->syncWithoutDetaching(
                District::where('city', 'İstanbul')->limit(10)->pluck('id')->mapWithKeys(fn ($id) => [$id => ['access_level' => 'full']])->all()
            );
        }

        $specialists = [
            ['email' => 'uzman1@test.com', 'name' => 'Operasyon Uzmanı 1', 'phone' => '0532 222 2221'],
            ['email' => 'uzman2@test.com', 'name' => 'Operasyon Uzmanı 2', 'phone' => '0532 222 2222'],
        ];
        $istanbulDistrictIds = District::where('city', 'İstanbul')->limit(5)->pluck('id');
        foreach ($specialists as $s) {
            $u = User::updateOrCreate(
                ['email' => $s['email']],
                [
                    'name' => $s['name'],
                    'password' => Hash::make('password'),
                    'role_id' => $specialistRole->id,
                    'phone' => $s['phone'],
                    'is_active' => true,
                ]
            );
            $this->adminIds[] = $u->id;
            $u->authorizedDistricts()->syncWithoutDetaching(
                $istanbulDistrictIds->mapWithKeys(fn ($id) => [$id => ['access_level' => 'manage']])->all()
            );
        }

        $partners = [
            ['email' => 'partner1@test.com', 'name' => 'ABC Lojistik', 'phone' => '0532 333 3331'],
            ['email' => 'partner2@test.com', 'name' => 'XYZ Kargo', 'phone' => '0532 333 3332'],
        ];
        $partnerUserIds = [];
        foreach ($partners as $p) {
            $u = User::updateOrCreate(
                ['email' => $p['email']],
                [
                    'name' => $p['name'],
                    'password' => Hash::make('password'),
                    'role_id' => $partnerRole->id,
                    'phone' => $p['phone'],
                    'is_active' => true,
                ]
            );
            $partnerUserIds[] = $u->id;
        }

        $vehicleTypes = ['Motosiklet', 'Bisiklet', 'Elektrikli Scooter', 'Araç', null];
        $names = [
            'Ahmet Yılmaz', 'Ayşe Kaya', 'Mehmet Demir', 'Fatma Şahin', 'Ali Özkan',
            'Zeynep Arslan', 'Mustafa Çelik', 'Elif Aydın', 'Hüseyin Koç', 'Merve Yıldız',
            'Emre Polat', 'Selin Öztürk', 'Burak Kılıç', 'Deniz Aslan', 'Cem Aksoy',
        ];
        for ($i = 0; $i < 15; $i++) {
            $courier = User::updateOrCreate(
                ['email' => "kurye.test" . ($i + 1) . "@test.com"],
                [
                    'name' => $names[$i % count($names)] . ($i >= count($names) ? ' ' . ($i + 1) : ''),
                    'password' => Hash::make('password'),
                    'role_id' => $courierRole->id,
                    'partner_id' => $partnerUserIds[$i % count($partnerUserIds)],
                    'phone' => '05' . rand(32, 55) . ' ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                    'employee_code' => (string) (1e10 + $i),
                    'vehicle_type' => $vehicleTypes[$i % count($vehicleTypes)],
                    'vehicle_plate' => $i % 3 !== 0 ? '34 ' . chr(65 + $i % 26) . chr(65 + ($i + 1) % 26) . ' ' . rand(100, 999) : null,
                    'is_active' => $i < 12,
                ]
            );
            $this->courierIds[] = $courier->id;
        }

        $this->command->info('Kullanıcılar (admin, yönetici, uzman, partner, 15 kurye) oluşturuldu.');
    }

    private function ensureRegions(): void
    {
        $regionsData = [
            ['name' => 'Kadikoy Bolgesi', 'city' => 'İstanbul', 'color' => '#3B82F6', 'description' => 'Kadıköy teslimat', 'is_active' => true],
            ['name' => 'Atasehir Bolgesi', 'city' => 'İstanbul', 'color' => '#10B981', 'description' => 'Ataşehir teslimat', 'is_active' => true],
            ['name' => 'Uskudar Bolgesi', 'city' => 'İstanbul', 'color' => '#F59E0B', 'description' => 'Üsküdar teslimat', 'is_active' => true],
            ['name' => 'Kartal-Maltepe Bolgesi', 'city' => 'İstanbul', 'color' => '#EF4444', 'description' => 'Kartal Maltepe', 'is_active' => true],
            ['name' => 'Pasif Bolge Test', 'city' => 'Ankara', 'color' => '#8B5CF6', 'description' => 'İnaktif bölge', 'is_active' => false],
        ];
        foreach ($regionsData as $r) {
            $region = Region::updateOrCreate(
                ['name' => $r['name']],
                array_merge($r, ['is_active' => $r['is_active']])
            );
            $this->regionIds[] = $region->id;
        }

        $istanbulDistricts = District::where('city', 'İstanbul')->pluck('id', 'name');
        $regionDistrictMap = [
            'Kadikoy Bolgesi' => ['Kadıköy'],
            'Atasehir Bolgesi' => ['Ataşehir'],
            'Uskudar Bolgesi' => ['Üsküdar'],
            'Kartal-Maltepe Bolgesi' => ['Kartal', 'Maltepe'],
            'Pasif Bolge Test' => [],
        ];
        foreach (Region::whereIn('id', $this->regionIds)->get() as $region) {
            $names = $regionDistrictMap[$region->name] ?? [];
            foreach ($names as $dName) {
                $did = $istanbulDistricts->get($dName);
                if ($did && !DB::table('region_districts')->where('region_id', $region->id)->where('district_id', $did)->exists()) {
                    DB::table('region_districts')->insert([
                        'region_id' => $region->id,
                        'district_id' => $did,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        $this->command->info('Bölgeler (4 aktif, 1 pasif) oluşturuldu.');
    }

    private function linkCouriersToRegionsAndDistricts(): void
    {
        $regions = Region::whereIn('id', $this->regionIds)->where('is_active', true)->get();
        $couriers = User::whereIn('id', $this->courierIds)->get();
        $managerId = $this->adminIds[1] ?? $this->adminIds[0];
        $districtIds = District::where('city', 'İstanbul')->limit(8)->pluck('id');

        foreach ($couriers as $i => $courier) {
            $region = $regions[$i % $regions->count()];
            $courier->courierRegions()->syncWithoutDetaching([
                $region->id => ['is_primary' => $i % 2 === 0],
            ]);
            $courier->courierDistricts()->syncWithoutDetaching(
                $districtIds->random(min(3, $districtIds->count()))->mapWithKeys(fn ($id) => [
                    $id => ['assigned_by' => $managerId, 'is_primary' => $id === $districtIds->first()],
                ])->all()
            );
        }
    }

    private function ensureScheduledShifts(): void
    {
        $statuses = [
            ScheduledShift::STATUS_DRAFT,
            ScheduledShift::STATUS_PUBLISHED,
            ScheduledShift::STATUS_PUBLISHED,
            ScheduledShift::STATUS_COMPLETED,
            ScheduledShift::STATUS_CANCELLED,
        ];
        $colors = array_keys(ScheduledShift::COLORS);
        $titles = [null, 'Sabah Vardiyası', 'Öğle', 'Akşam', 'Ek Vardiya', 'Hafta sonu'];
        $notes = [null, 'Test notu', 'Acil', 'Normal mesai'];

        $slots = [
            ['start' => '08:00', 'end' => '12:00'],
            ['start' => '09:00', 'end' => '13:00'],
            ['start' => '12:00', 'end' => '16:00'],
            ['start' => '14:00', 'end' => '18:00'],
            ['start' => '16:00', 'end' => '20:00'],
        ];

        $created = 0;
        foreach ([-3, -2, -1, 0, 1, 2, 3] as $dayOffset) {
            $date = Carbon::today()->addDays($dayOffset);
            $activeRegions = Region::whereIn('id', $this->regionIds)->where('is_active', true)->get();
            foreach ($activeRegions as $region) {
                foreach ($slots as $si => $slot) {
                    $status = $statuses[abs($dayOffset + $si) % count($statuses)];
                    ScheduledShift::create([
                        'region_id' => $region->id,
                        'district_id' => $this->defaultDistrictId,
                        'created_by' => $this->adminIds[0],
                        'shift_date' => $date->format('Y-m-d'),
                        'start_time' => $slot['start'] . ':00',
                        'end_time' => $slot['end'] . ':00',
                        'required_couriers' => rand(1, 3),
                        'status' => $status,
                        'title' => $titles[$si % count($titles)],
                        'notes' => $notes[$si % count($notes)],
                        'color' => $colors[$si % count($colors)] ?? '#3B82F6',
                    ]);
                    $created++;
                }
            }
        }
        $this->command->info("{$created} planlı vardiya (draft, published, completed, cancelled) oluşturuldu.");
    }

    private function ensureShiftAssignmentsAndActualShifts(): void
    {
        $assignmentStatuses = [
            ShiftAssignment::STATUS_ASSIGNED,
            ShiftAssignment::STATUS_CONFIRMED,
            ShiftAssignment::STATUS_STARTED,
            ShiftAssignment::STATUS_COMPLETED,
            ShiftAssignment::STATUS_CANCELLED,
            ShiftAssignment::STATUS_NO_SHOW,
        ];
        $shiftStatuses = [Shift::STATUS_ACTIVE, Shift::STATUS_COMPLETED, Shift::STATUS_COMPLETED, Shift::STATUS_CANCELLED];
        $photoCompliance = [
            Shift::PHOTO_COMPLIANCE_PENDING,
            Shift::PHOTO_COMPLIANCE_APPROVED,
            Shift::PHOTO_COMPLIANCE_NO_BONUS,
            Shift::PHOTO_COMPLIANCE_RE_REQUESTED,
        ];

        $scheduledShifts = ScheduledShift::whereIn('region_id', $this->regionIds)
            ->with('region', 'assignments')
            ->orderBy('shift_date')
            ->orderBy('start_time')
            ->get()
            ->filter(fn ($ss) => $ss->assignments->isEmpty());

        $couriers = User::whereIn('id', $this->courierIds)->where('is_active', true)->get();
        if ($couriers->isEmpty()) {
            return;
        }

        $this->ensurePlaceholderPhoto();

        $createdAssignments = 0;
        $createdShifts = 0;
        $idx = 0;
        foreach ($scheduledShifts as $ss) {
            $toTake = min($ss->required_couriers, $couriers->count());
            $assignCouriers = $couriers->random($toTake);
            $startDt = Carbon::parse($ss->shift_date->format('Y-m-d') . ' ' . $ss->start_time->format('H:i:s'));
            $endDt = Carbon::parse($ss->shift_date->format('Y-m-d') . ' ' . $ss->end_time->format('H:i:s'));

            foreach ($assignCouriers as $courier) {
                $astatus = $assignmentStatuses[$idx % count($assignmentStatuses)];
                $assignment = ShiftAssignment::create([
                    'scheduled_shift_id' => $ss->id,
                    'courier_id' => $courier->id,
                    'assigned_by' => $this->adminIds[0],
                    'status' => $astatus,
                    'notes' => $idx % 3 === 0 ? 'Test notu' : null,
                    'actual_start_time' => in_array($astatus, [ShiftAssignment::STATUS_STARTED, ShiftAssignment::STATUS_COMPLETED]) ? $ss->start_time->format('H:i') : null,
                    'actual_end_time' => $astatus === ShiftAssignment::STATUS_COMPLETED ? $ss->end_time->format('H:i') : null,
                    'end_reason' => $astatus === ShiftAssignment::STATUS_CANCELLED ? 'Test iptal' : null,
                ]);
                $createdAssignments++;

                if (in_array($astatus, [ShiftAssignment::STATUS_STARTED, ShiftAssignment::STATUS_COMPLETED]) && $ss->shift_date->lte(Carbon::today())) {
                    $sstatus = $astatus === ShiftAssignment::STATUS_COMPLETED ? Shift::STATUS_COMPLETED : Shift::STATUS_ACTIVE;
                    $actualShift = Shift::create([
                        'user_id' => $courier->id,
                        'district_id' => $this->defaultDistrictId,
                        'region_id' => $ss->region_id,
                        'status' => $sstatus,
                        'started_at' => $startDt,
                        'start_latitude' => 41.0 + rand(0, 99) / 1000,
                        'start_longitude' => 29.0 + rand(0, 99) / 1000,
                        'start_address' => 'Test başlangıç ' . $ss->region?->name,
                        'ended_at' => $sstatus === Shift::STATUS_COMPLETED ? $endDt : null,
                        'end_latitude' => $sstatus === Shift::STATUS_COMPLETED ? 41.0 + rand(0, 99) / 1000 : null,
                        'end_longitude' => $sstatus === Shift::STATUS_COMPLETED ? 29.0 + rand(0, 99) / 1000 : null,
                        'end_address' => $sstatus === Shift::STATUS_COMPLETED ? 'Test bitiş ' . $ss->region?->name : null,
                        'package_count' => rand(5, 40),
                        'total_minutes' => $startDt->diffInMinutes($endDt),
                        'notes' => $idx % 4 === 0 ? 'Kurye notu' : null,
                        'admin_notes' => $idx % 5 === 0 ? 'Admin notu' : null,
                        'photo_compliance_status' => $photoCompliance[$idx % count($photoCompliance)],
                    ]);
                    $assignment->update(['actual_shift_id' => $actualShift->id, 'confirmed_at' => $startDt, 'started_at' => $startDt, 'completed_at' => $sstatus === Shift::STATUS_COMPLETED ? $endDt : null]);
                    $this->attachPlaceholderPhotos($actualShift);
                    $createdShifts++;
                }
                $idx++;
            }
        }

        $this->command->info("{$createdAssignments} atama, {$createdShifts} fiili vardiya (Shift) oluşturuldu.");
    }

    private function ensurePlaceholderPhoto(): void
    {
        $path = 'shifts/placeholder.png';
        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            $disk->makeDirectory('shifts');
            $disk->put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
        }
    }

    private function attachPlaceholderPhotos(Shift $shift): void
    {
        ShiftPhoto::create([
            'shift_id' => $shift->id,
            'type' => ShiftPhoto::TYPE_START,
            'is_retry' => false,
            'filename' => 'placeholder.png',
            'path' => 'shifts/placeholder.png',
            'disk' => 'public',
            'mime_type' => 'image/png',
            'size' => 68,
        ]);
        ShiftPhoto::create([
            'shift_id' => $shift->id,
            'type' => ShiftPhoto::TYPE_END,
            'is_retry' => rand(0, 1) === 1,
            'filename' => 'placeholder.png',
            'path' => 'shifts/placeholder.png',
            'disk' => 'public',
            'mime_type' => 'image/png',
            'size' => 68,
        ]);
    }

    private function ensureShiftLogsAndPhotos(): void
    {
        $logTypes = [ShiftLog::TYPE_START, ShiftLog::TYPE_END, ShiftLog::TYPE_PAUSE, ShiftLog::TYPE_RESUME, ShiftLog::TYPE_UPDATE];
        $shifts = Shift::limit(30)->get();
        $count = 0;
        foreach ($shifts as $shift) {
            ShiftLog::create([
                'shift_id' => $shift->id,
                'type' => $logTypes[$count % count($logTypes)],
                'latitude' => 41.01 + $count * 0.001,
                'longitude' => 29.01 + $count * 0.001,
                'address' => 'Log adres ' . $count,
                'accuracy' => 10,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'TestAgent',
                'logged_at' => $shift->started_at,
            ]);
            $count++;
        }
        $this->command->info("{$count} ShiftLog kaydı oluşturuldu.");
    }

    private function ensureExpenseRequests(): void
    {
        $approvalTypes = [
            null,
            ExpenseRequest::APPROVAL_DEDUCT_FROM_SETTLEMENT,
            ExpenseRequest::APPROVAL_DEBT_BALANCE,
            ExpenseRequest::APPROVAL_CLOSED,
        ];
        $couriers = User::whereIn('id', $this->courierIds)->get();
        if ($couriers->isEmpty()) {
            return;
        }
        $this->ensureExpensePlaceholder();
        $created = 0;
        for ($i = 0; $i < 12; $i++) {
            $courier = $couriers->random();
            $status = $i % 3 === 0 ? ExpenseRequest::STATUS_PENDING : ExpenseRequest::STATUS_APPROVED;
            $total = rand(50, 500) + rand(0, 99) / 100;
            $req = ExpenseRequest::create([
                'user_id' => $courier->id,
                'receipt_photo_path' => 'expenses/placeholder.png',
                'order_number' => 'EXP-' . (1000 + $i),
                'reason' => ['Yakıt', 'Yemek', 'Bakım', 'Diğer'][$i % 4],
                'source' => ['Shell Kadıköy', 'Migros Ataşehir', 'Opet İstasyonu', 'Carrefour'][$i % 4],
                'total_amount' => $total,
                'status' => $status,
                'approval_type' => $status === ExpenseRequest::STATUS_APPROVED ? $approvalTypes[$i % count($approvalTypes)] : null,
                'approved_at' => $status === ExpenseRequest::STATUS_APPROVED ? now()->subDays(rand(1, 10)) : null,
                'approved_by' => $status === ExpenseRequest::STATUS_APPROVED ? $this->adminIds[0] : null,
                'notes' => $i % 2 === 0 ? 'Not ' . $i : null,
            ]);
            ExpenseRequestItem::create([
                'expense_request_id' => $req->id,
                'product_name' => 'Kalem ' . $i,
                'quantity_or_kg' => (string) rand(1, 5),
                'price' => round($total / 2, 2),
                'sort_order' => 1,
            ]);
            ExpenseRequestItem::create([
                'expense_request_id' => $req->id,
                'product_name' => 'Diğer ' . $i,
                'quantity_or_kg' => '1',
                'price' => round($total - $total / 2, 2),
                'sort_order' => 2,
            ]);
            $created++;
        }
        $this->command->info("{$created} masraf talebi (pending/approved, approval_type varyasyonlu) oluşturuldu.");
    }

    private function ensureExpensePlaceholder(): void
    {
        $path = 'expenses/placeholder.png';
        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            $disk->makeDirectory('expenses');
            $disk->put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
        }
    }

    private function ensureExtraBonusesAndDeductions(): void
    {
        $couriers = User::whereIn('id', $this->courierIds)->get();
        if ($couriers->isEmpty()) {
            return;
        }
        foreach ($couriers->take(5) as $i => $c) {
            ExtraBonus::create([
                'user_id' => $c->id,
                'amount' => rand(50, 200),
                'reason' => ['Performans', 'Özel gün', 'Fazla mesai'][$i % 3],
                'bonus_date' => now()->subDays(rand(1, 30)),
            ]);
        }
        foreach ($couriers->take(3) as $i => $c) {
            SettlementDeduction::create([
                'user_id' => $c->id,
                'amount' => rand(20, 100),
                'reason' => ['Eksik teslimat', 'Gecikme', 'Diğer'][$i % 3],
                'deduction_date' => now()->subDays(rand(1, 20)),
            ]);
        }
        $this->command->info('Ekstra bonus ve kesinti kayıtları oluşturuldu.');
    }
}
