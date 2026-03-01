<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Tüm alanlarda varyasyonlu test verisi için:
     *   php artisan db:seed --class=FullTestDataSeeder
     * (Önce php artisan data:wipe --force ile temizleyebilirsiniz.)
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DistrictSeeder::class,
            UserSeeder::class,
            RegionSeeder::class,
            TestShiftDataSeeder::class,
        ]);
    }
}
