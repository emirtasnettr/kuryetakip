<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Rol Seeder
 * 
 * Sistemdeki 5 temel rolü oluşturur.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::COURIER,
                'display_name' => 'Kurye',
                'description' => 'Mobil uygulama üzerinden vardiya başlatıp bitirebilir.',
                'permissions' => ['shift.start', 'shift.end', 'shift.view.own'],
            ],
            [
                'name' => Role::OPERATION_SPECIALIST,
                'display_name' => 'Operasyon Uzmanı',
                'description' => 'Yetkili ilçelerdeki kuryeleri ve vardiyaları görüntüleyebilir.',
                'permissions' => ['courier.view', 'shift.view', 'report.view'],
            ],
            [
                'name' => Role::OPERATION_MANAGER,
                'display_name' => 'Operasyon Yöneticisi',
                'description' => 'Kurye ve vardiya yönetimi, raporlama yetkilerine sahiptir.',
                'permissions' => ['courier.manage', 'shift.manage', 'report.view', 'district.manage'],
            ],
            [
                'name' => Role::BUSINESS_PARTNER,
                'display_name' => 'İş Ortağı',
                'description' => 'Kendi kuryelerini görüntüleyip yönetebilir.',
                'permissions' => ['courier.view.own', 'courier.manage.own', 'shift.view.own'],
            ],
            [
                'name' => Role::SYSTEM_ADMIN,
                'display_name' => 'Sistem Yöneticisi',
                'description' => 'Tüm sistem yetkilerine sahiptir.',
                'permissions' => ['*'],
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
