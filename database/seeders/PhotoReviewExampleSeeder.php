<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Role;
use App\Models\Shift;
use App\Models\ShiftPhoto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Vardiya Uyumluluk İncelemesi için 10 örnek vardiya ekler.
 * Bu vardiyalar inceleme listesinde (pending_review) görünür; başlangıç/bitiş placeholder fotoğraflı.
 */
class PhotoReviewExampleSeeder extends Seeder
{
    private const PLACEHOLDER_PATH = 'shifts/placeholder.png';

    public function run(): void
    {
        $courierRole = Role::where('name', Role::COURIER)->first();
        if (! $courierRole) {
            $this->command->error('Kurye rolü bulunamadı.');
            return;
        }

        $couriers = User::where('role_id', $courierRole->id)->take(10)->get();
        if ($couriers->isEmpty()) {
            $this->command->error('Hiç kurye bulunamadı. Önce kurye oluşturun.');
            return;
        }

        $districtId = District::first()?->id;

        $this->ensurePlaceholderPhotoExists();

        $created = 0;
        for ($i = 0; $i < 10; $i++) {
            $courier = $couriers[$i % $couriers->count()];
            $daysAgo = $i + 1;
            $startedAt = Carbon::today()->subDays($daysAgo)->setTime(9, 0);
            $endedAt = $startedAt->copy()->addHours(4);

            $shift = Shift::create([
                'user_id' => $courier->id,
                'district_id' => $districtId,
                'status' => Shift::STATUS_COMPLETED,
                'started_at' => $startedAt,
                'start_latitude' => 41.0082,
                'start_longitude' => 28.9784,
                'start_address' => 'Örnek başlangıç adresi',
                'ended_at' => $endedAt,
                'end_latitude' => 41.0082,
                'end_longitude' => 28.9784,
                'end_address' => 'Örnek bitiş adresi',
                'package_count' => rand(5, 20),
                'total_minutes' => 240,
                'photo_compliance_status' => Shift::PHOTO_COMPLIANCE_PENDING,
            ]);

            $this->attachPlaceholderPhotos($shift);
            $created++;
        }

        $this->command->info("Vardiya Uyumluluk İncelemesi için {$created} örnek vardiya eklendi.");
    }

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

    private function attachPlaceholderPhotos(Shift $shift): void
    {
        ShiftPhoto::create([
            'shift_id' => $shift->id,
            'type' => ShiftPhoto::TYPE_START,
            'is_retry' => false,
            'filename' => 'placeholder.png',
            'original_filename' => 'baslangic.png',
            'path' => self::PLACEHOLDER_PATH,
            'disk' => 'public',
            'mime_type' => 'image/png',
            'size' => 68,
        ]);
        ShiftPhoto::create([
            'shift_id' => $shift->id,
            'type' => ShiftPhoto::TYPE_END,
            'is_retry' => false,
            'filename' => 'placeholder.png',
            'original_filename' => 'bitis.png',
            'path' => self::PLACEHOLDER_PATH,
            'disk' => 'public',
            'mime_type' => 'image/png',
            'size' => 68,
        ]);
    }
}
