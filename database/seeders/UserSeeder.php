<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Kullanıcı Seeder
 * 
 * Test kullanıcılarını oluşturur.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Sistem Yöneticisi
        $adminRole = Role::where('name', Role::SYSTEM_ADMIN)->first();
        User::updateOrCreate(
            ['email' => 'admin@kuryetakip.com'],
            [
                'name' => 'Sistem Yöneticisi',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]
        );

        // Operasyon Yöneticisi
        $managerRole = Role::where('name', Role::OPERATION_MANAGER)->first();
        $manager = User::updateOrCreate(
            ['email' => 'yonetici@kuryetakip.com'],
            [
                'name' => 'Operasyon Yöneticisi',
                'password' => Hash::make('password'),
                'role_id' => $managerRole->id,
                'phone' => '0532 111 1111',
                'is_active' => true,
            ]
        );

        // Tüm ilçelere yetki ver
        $allDistricts = District::all();
        foreach ($allDistricts as $district) {
            $manager->authorizedDistricts()->syncWithoutDetaching([
                $district->id => ['access_level' => 'full']
            ]);
        }

        // Operasyon Uzmanı
        $specialistRole = Role::where('name', Role::OPERATION_SPECIALIST)->first();
        $specialist = User::updateOrCreate(
            ['email' => 'uzman@kuryetakip.com'],
            [
                'name' => 'Operasyon Uzmanı',
                'password' => Hash::make('password'),
                'role_id' => $specialistRole->id,
                'phone' => '0532 222 2222',
                'is_active' => true,
            ]
        );

        // Belirli ilçelere yetki ver (Kadıköy, Ataşehir, Üsküdar)
        $anadiluDistricts = District::whereIn('code', ['KDK', 'ATS', 'USK'])->get();
        foreach ($anadiluDistricts as $district) {
            $specialist->authorizedDistricts()->syncWithoutDetaching([
                $district->id => ['access_level' => 'manage']
            ]);
        }

        // İş Ortağı
        $partnerRole = Role::where('name', Role::BUSINESS_PARTNER)->first();
        $partner = User::updateOrCreate(
            ['email' => 'partner@kuryetakip.com'],
            [
                'name' => 'ABC Lojistik',
                'password' => Hash::make('password'),
                'role_id' => $partnerRole->id,
                'phone' => '0532 333 3333',
                'is_active' => true,
            ]
        );

        // Kuryeler
        $courierRole = Role::where('name', Role::COURIER)->first();
        $kadikoy = District::where('code', 'KDK')->first();
        $atasehir = District::where('code', 'ATS')->first();

        // Kurye 1
        $courier1 = User::updateOrCreate(
            ['email' => 'kurye1@kuryetakip.com'],
            [
                'name' => 'Ahmet Yılmaz',
                'password' => Hash::make('password'),
                'role_id' => $courierRole->id,
                'partner_id' => $partner->id,
                'phone' => '0533 111 1111',
                'employee_code' => 'KRY001',
                'vehicle_type' => 'Motosiklet',
                'vehicle_plate' => '34 ABC 123',
                'is_active' => true,
            ]
        );

        // Kurye 1'e ilçe ata
        $courier1->courierDistricts()->sync([
            $kadikoy->id => ['is_primary' => true, 'assigned_by' => $manager->id],
            $atasehir->id => ['is_primary' => false, 'assigned_by' => $manager->id],
        ]);

        // Kurye 2
        $courier2 = User::updateOrCreate(
            ['email' => 'kurye2@kuryetakip.com'],
            [
                'name' => 'Mehmet Demir',
                'password' => Hash::make('password'),
                'role_id' => $courierRole->id,
                'partner_id' => $partner->id,
                'phone' => '0533 222 2222',
                'employee_code' => 'KRY002',
                'vehicle_type' => 'Bisiklet',
                'is_active' => true,
            ]
        );

        // Kurye 2'ye ilçe ata
        $courier2->courierDistricts()->sync([
            $atasehir->id => ['is_primary' => true, 'assigned_by' => $manager->id],
        ]);

        $this->command->info('Test kullanıcıları oluşturuldu:');
        $this->command->info('- admin@kuryetakip.com / password (Sistem Yöneticisi)');
        $this->command->info('- yonetici@kuryetakip.com / password (Operasyon Yöneticisi)');
        $this->command->info('- uzman@kuryetakip.com / password (Operasyon Uzmanı)');
        $this->command->info('- partner@kuryetakip.com / password (İş Ortağı)');
        $this->command->info('- kurye1@kuryetakip.com / password (Kurye)');
        $this->command->info('- kurye2@kuryetakip.com / password (Kurye)');
    }
}
