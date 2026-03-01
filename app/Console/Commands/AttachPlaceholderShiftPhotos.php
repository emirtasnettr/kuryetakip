<?php

namespace App\Console\Commands;

use App\Models\Shift;
use App\Models\ShiftPhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Fotoğrafı olmayan vardiyalara test/placeholder fotoğrafı ekler.
 * Test verileri ve raporlarda fotoğraf alanının dolu görünmesi için kullanılır.
 */
class AttachPlaceholderShiftPhotos extends Command
{
    protected $signature = 'shifts:attach-placeholder-photos';

    protected $description = 'Fotoğrafı olmayan vardiyalara placeholder (test) fotoğrafı ekler';

    public const PLACEHOLDER_PATH = 'shifts/placeholder.png';

    /**
     * 1x1 pixel PNG (geçerli görsel, storage'da saklanabilir)
     */
    protected static function getPlaceholderPngContent(): string
    {
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );
    }

    public function handle(): int
    {
        $disk = Storage::disk('public');

        if (!$disk->exists(self::PLACEHOLDER_PATH)) {
            $this->info('Placeholder görsel oluşturuluyor...');
            $disk->makeDirectory('shifts');
            $disk->put(self::PLACEHOLDER_PATH, self::getPlaceholderPngContent());
            $this->info('  ✓ ' . self::PLACEHOLDER_PATH);
        }

        $shiftsWithoutPhotos = Shift::whereDoesntHave('photos')->get();

        if ($shiftsWithoutPhotos->isEmpty()) {
            $this->info('Fotoğrafı eksik vardiya yok.');
            return 0;
        }

        $this->info($shiftsWithoutPhotos->count() . ' vardiyaya başlangıç ve bitiş fotoğrafı ekleniyor...');

        foreach ($shiftsWithoutPhotos as $shift) {
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

        $this->info('✓ Tamamlandı. ' . $shiftsWithoutPhotos->count() . ' vardiyaya 2\'şer fotoğraf atandı.');

        return 0;
    }
}
