<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Test Kurye Seeder
 * 
 * İstanbul'daki her ilçe için 5 adet test kuryesi oluşturur.
 * Tüm kuryelerin şifresi: password
 */
class TestCourierSeeder extends Seeder
{
    public function run(): void
    {
        $courierRole = Role::where('name', Role::COURIER)->first();
        
        if (!$courierRole) {
            $this->command->error('Kurye rolü bulunamadı!');
            return;
        }

        // İstanbul ilçeleri
        $istanbulDistricts = District::where('city', 'İstanbul')->get();
        
        $this->command->info("İstanbul'da {$istanbulDistricts->count()} ilçe bulundu.");
        
        // Türkçe isimler
        $firstNames = [
            'Ahmet', 'Mehmet', 'Mustafa', 'Ali', 'Hüseyin', 'Hasan', 'İbrahim', 'Osman',
            'Yusuf', 'Murat', 'Ömer', 'Emre', 'Burak', 'Cem', 'Deniz', 'Eren',
            'Fatih', 'Gökhan', 'Halil', 'İsmail', 'Kemal', 'Levent', 'Mert', 'Necati',
            'Onur', 'Polat', 'Recep', 'Serkan', 'Tuncay', 'Uğur', 'Volkan', 'Yavuz',
            'Zafer', 'Barış', 'Caner', 'Doğan', 'Erdem', 'Ferhat', 'Gürsel', 'Haydar'
        ];
        
        $lastNames = [
            'Yılmaz', 'Kaya', 'Demir', 'Çelik', 'Şahin', 'Yıldız', 'Yıldırım', 'Öztürk',
            'Aydın', 'Özdemir', 'Arslan', 'Doğan', 'Kılıç', 'Aslan', 'Çetin', 'Kara',
            'Koç', 'Kurt', 'Özkan', 'Şimşek', 'Polat', 'Korkmaz', 'Çakır', 'Erdoğan',
            'Ünal', 'Acar', 'Aktaş', 'Avcı', 'Aksoy', 'Bayrak', 'Bozkurt', 'Bulut'
        ];

        $vehicleTypes = ['Motosiklet', 'Bisiklet', 'Elektrikli Scooter'];
        
        $createdCount = 0;
        $nameIndex = 0;

        foreach ($istanbulDistricts as $district) {
            for ($i = 1; $i <= 5; $i++) {
                $firstName = $firstNames[$nameIndex % count($firstNames)];
                $lastName = $lastNames[($nameIndex + $i) % count($lastNames)];
                $fullName = $firstName . ' ' . $lastName;
                
                // Benzersiz email oluştur
                $email = strtolower(str_replace(
                    ['ı', 'ö', 'ü', 'ş', 'ç', 'ğ', ' '],
                    ['i', 'o', 'u', 's', 'c', 'g', ''],
                    $firstName
                )) . '.' . strtolower(str_replace(
                    ['ı', 'ö', 'ü', 'ş', 'ç', 'ğ', ' '],
                    ['i', 'o', 'u', 's', 'c', 'g', ''],
                    $lastName
                )) . $district->id . $i . '@test.com';
                
                // Telefon numarası
                $phone = '05' . rand(30, 59) . ' ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99);
                
                // Kurye oluştur
                $courier = User::create([
                    'name' => $fullName,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'phone' => $phone,
                    'role_id' => $courierRole->id,
                    'vehicle_type' => $vehicleTypes[array_rand($vehicleTypes)],
                    'vehicle_plate' => '34 ' . chr(rand(65, 90)) . chr(rand(65, 90)) . ' ' . rand(100, 999),
                    'is_active' => true,
                ]);
                
                // İlçeye ata
                $courier->courierDistricts()->attach($district->id, [
                    'assigned_by' => 1, // Sistem tarafından
                    'is_primary' => true,
                ]);
                
                $createdCount++;
                $nameIndex++;
            }
            
            $this->command->info("✓ {$district->name}: 5 kurye eklendi");
        }
        
        $this->command->newLine();
        $this->command->info("Toplam {$createdCount} test kuryesi oluşturuldu.");
        $this->command->info("Tüm kuryelerin şifresi: password");
    }
}
