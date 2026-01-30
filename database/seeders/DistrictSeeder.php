<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;

/**
 * İlçe Seeder
 * 
 * İstanbul ilçelerini örnek olarak oluşturur.
 */
class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [
            // Avrupa Yakası
            ['name' => 'Arnavutköy', 'city' => 'İstanbul', 'code' => 'ARN'],
            ['name' => 'Avcılar', 'city' => 'İstanbul', 'code' => 'AVC'],
            ['name' => 'Bağcılar', 'city' => 'İstanbul', 'code' => 'BAG'],
            ['name' => 'Bahçelievler', 'city' => 'İstanbul', 'code' => 'BAH'],
            ['name' => 'Bakırköy', 'city' => 'İstanbul', 'code' => 'BAK'],
            ['name' => 'Başakşehir', 'city' => 'İstanbul', 'code' => 'BSK'],
            ['name' => 'Bayrampaşa', 'city' => 'İstanbul', 'code' => 'BYP'],
            ['name' => 'Beşiktaş', 'city' => 'İstanbul', 'code' => 'BES'],
            ['name' => 'Beylikdüzü', 'city' => 'İstanbul', 'code' => 'BYL'],
            ['name' => 'Beyoğlu', 'city' => 'İstanbul', 'code' => 'BYG'],
            ['name' => 'Büyükçekmece', 'city' => 'İstanbul', 'code' => 'BYC'],
            ['name' => 'Çatalca', 'city' => 'İstanbul', 'code' => 'CAT'],
            ['name' => 'Esenler', 'city' => 'İstanbul', 'code' => 'ESN'],
            ['name' => 'Esenyurt', 'city' => 'İstanbul', 'code' => 'ESY'],
            ['name' => 'Eyüpsultan', 'city' => 'İstanbul', 'code' => 'EYP'],
            ['name' => 'Fatih', 'city' => 'İstanbul', 'code' => 'FAT'],
            ['name' => 'Gaziosmanpaşa', 'city' => 'İstanbul', 'code' => 'GOP'],
            ['name' => 'Güngören', 'city' => 'İstanbul', 'code' => 'GNG'],
            ['name' => 'Kağıthane', 'city' => 'İstanbul', 'code' => 'KGT'],
            ['name' => 'Küçükçekmece', 'city' => 'İstanbul', 'code' => 'KCK'],
            ['name' => 'Sarıyer', 'city' => 'İstanbul', 'code' => 'SAR'],
            ['name' => 'Silivri', 'city' => 'İstanbul', 'code' => 'SIL'],
            ['name' => 'Sultangazi', 'city' => 'İstanbul', 'code' => 'SGZ'],
            ['name' => 'Şişli', 'city' => 'İstanbul', 'code' => 'SIS'],
            ['name' => 'Zeytinburnu', 'city' => 'İstanbul', 'code' => 'ZYT'],
            
            // Anadolu Yakası
            ['name' => 'Adalar', 'city' => 'İstanbul', 'code' => 'ADL'],
            ['name' => 'Ataşehir', 'city' => 'İstanbul', 'code' => 'ATS'],
            ['name' => 'Beykoz', 'city' => 'İstanbul', 'code' => 'BYK'],
            ['name' => 'Çekmeköy', 'city' => 'İstanbul', 'code' => 'CKM'],
            ['name' => 'Kadıköy', 'city' => 'İstanbul', 'code' => 'KDK'],
            ['name' => 'Kartal', 'city' => 'İstanbul', 'code' => 'KRT'],
            ['name' => 'Maltepe', 'city' => 'İstanbul', 'code' => 'MLT'],
            ['name' => 'Pendik', 'city' => 'İstanbul', 'code' => 'PND'],
            ['name' => 'Sancaktepe', 'city' => 'İstanbul', 'code' => 'SNK'],
            ['name' => 'Sultanbeyli', 'city' => 'İstanbul', 'code' => 'SLT'],
            ['name' => 'Şile', 'city' => 'İstanbul', 'code' => 'SLE'],
            ['name' => 'Tuzla', 'city' => 'İstanbul', 'code' => 'TZL'],
            ['name' => 'Ümraniye', 'city' => 'İstanbul', 'code' => 'UMR'],
            ['name' => 'Üsküdar', 'city' => 'İstanbul', 'code' => 'USK'],
        ];

        foreach ($districts as $district) {
            District::updateOrCreate(
                ['name' => $district['name'], 'city' => $district['city']],
                $district
            );
        }
    }
}
