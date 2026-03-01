<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\District;
use Illuminate\Database\Seeder;

/**
 * Bölge Seeder
 *
 * İstanbul bölgelerini ve bölge-ilçe ilişkilerini oluşturur.
 */
class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            [
                'name' => 'Kadıköy Bölgesi',
                'city' => 'İstanbul',
                'color' => '#3B82F6',
                'description' => 'Kadıköy ve çevresi teslimat bölgesi',
                'districts' => ['Kadıköy'],
            ],
            [
                'name' => 'Ataşehir Bölgesi',
                'city' => 'İstanbul',
                'color' => '#10B981',
                'description' => 'Ataşehir ve çevresi teslimat bölgesi',
                'districts' => ['Ataşehir'],
            ],
            [
                'name' => 'Üsküdar Bölgesi',
                'city' => 'İstanbul',
                'color' => '#F59E0B',
                'description' => 'Üsküdar ve çevresi teslimat bölgesi',
                'districts' => ['Üsküdar'],
            ],
            [
                'name' => 'Kartal-Maltepe Bölgesi',
                'city' => 'İstanbul',
                'color' => '#EF4444',
                'description' => 'Kartal ve Maltepe teslimat bölgesi',
                'districts' => ['Kartal', 'Maltepe'],
            ],
            [
                'name' => 'Beşiktaş-Şişli Bölgesi',
                'city' => 'İstanbul',
                'color' => '#8B5CF6',
                'description' => 'Beşiktaş ve Şişli teslimat bölgesi',
                'districts' => ['Beşiktaş', 'Şişli'],
            ],
        ];

        foreach ($regions as $data) {
            $districtNames = $data['districts'];
            unset($data['districts']);

            $region = Region::updateOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['is_active' => true])
            );

            $districtIds = District::where('city', 'İstanbul')
                ->whereIn('name', $districtNames)
                ->pluck('id');

            foreach ($districtIds as $districtId) {
                \DB::table('region_districts')->insertOrIgnore([
                    'region_id' => $region->id,
                    'district_id' => $districtId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->command->info("✓ Bölge: {$region->name}");
        }
    }
}
