<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Vardiya Fotoğraf Model
 * 
 * Vardiya başlangıç ve bitiş fotoğraflarını yönetir.
 */
class ShiftPhoto extends Model
{
    use HasFactory;

    // Fotoğraf tipleri
    public const TYPE_START = 'start';
    public const TYPE_END = 'end';

    /**
     * Toplu atama yapılabilecek alanlar
     */
    protected $fillable = [
        'shift_id',
        'type',
        'filename',
        'original_filename',
        'path',
        'disk',
        'mime_type',
        'size',
        'width',
        'height',
        'exif_latitude',
        'exif_longitude',
        'exif_taken_at',
    ];

    /**
     * Tip dönüşümleri
     */
    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'exif_latitude' => 'decimal:8',
        'exif_longitude' => 'decimal:8',
        'exif_taken_at' => 'datetime',
    ];

    // ==================== İLİŞKİLER ====================

    /**
     * Fotoğrafın ait olduğu vardiya
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    // ==================== URL VE DOSYA METODLARİ ====================

    /**
     * Fotoğraf URL'ini getir
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Fotoğraf tam yolunu getir
     */
    public function getFullPathAttribute(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    /**
     * Fotoğraf mevcut mu?
     */
    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    /**
     * Fotoğrafı sil (dosya dahil)
     */
    public function deleteWithFile(): bool
    {
        // Önce dosyayı sil
        if ($this->exists()) {
            Storage::disk($this->disk)->delete($this->path);
        }

        // Sonra kaydı sil
        return $this->delete();
    }

    /**
     * Dosya boyutunu okunabilir formatta getir
     */
    public function getReadableSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    /**
     * Fotoğraf tipinin Türkçe karşılığı
     */
    public function getTypeDisplayAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_START => 'Başlangıç Fotoğrafı',
            self::TYPE_END => 'Bitiş Fotoğrafı',
            default => $this->type,
        };
    }

    // ==================== EXIF METODLARİ ====================

    /**
     * EXIF konumu var mı?
     */
    public function hasExifLocation(): bool
    {
        return $this->exif_latitude !== null && $this->exif_longitude !== null;
    }

    /**
     * EXIF konum URL'i
     */
    public function getExifLocationUrlAttribute(): ?string
    {
        if (!$this->hasExifLocation()) {
            return null;
        }

        return "https://www.google.com/maps?q={$this->exif_latitude},{$this->exif_longitude}";
    }

    // ==================== SCOPE'LAR ====================

    /**
     * Başlangıç fotoğrafları
     */
    public function scopeStart($query)
    {
        return $query->where('type', self::TYPE_START);
    }

    /**
     * Bitiş fotoğrafları
     */
    public function scopeEnd($query)
    {
        return $query->where('type', self::TYPE_END);
    }

    // ==================== STATİK METODLAR ====================

    /**
     * Yüklenen dosyadan fotoğraf oluştur
     */
    public static function createFromUpload(
        Shift $shift,
        string $type,
        $uploadedFile,
        string $disk = 'public'
    ): self {
        // Dosya adı oluştur
        $filename = sprintf(
            '%d_%s_%s.%s',
            $shift->id,
            $type,
            uniqid(),
            $uploadedFile->getClientOriginalExtension()
        );

        // Dosyayı kaydet
        $path = $uploadedFile->storeAs(
            'shifts/' . date('Y/m'),
            $filename,
            $disk
        );

        // Görsel boyutlarını al
        $imageInfo = @getimagesize($uploadedFile->getRealPath());

        // EXIF bilgilerini çıkar (varsa)
        $exifData = self::extractExifData($uploadedFile->getRealPath());

        return self::create([
            'shift_id' => $shift->id,
            'type' => $type,
            'filename' => $filename,
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'disk' => $disk,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'width' => $imageInfo[0] ?? null,
            'height' => $imageInfo[1] ?? null,
            'exif_latitude' => $exifData['latitude'] ?? null,
            'exif_longitude' => $exifData['longitude'] ?? null,
            'exif_taken_at' => $exifData['taken_at'] ?? null,
        ]);
    }

    /**
     * Fotoğraftan EXIF verilerini çıkar
     */
    protected static function extractExifData(string $filepath): array
    {
        $data = [];

        if (!function_exists('exif_read_data')) {
            return $data;
        }

        try {
            $exif = @exif_read_data($filepath, 'GPS,EXIF', true);

            if ($exif === false) {
                return $data;
            }

            // GPS koordinatlarını çıkar
            if (isset($exif['GPS'])) {
                $data['latitude'] = self::getGpsCoordinate(
                    $exif['GPS']['GPSLatitude'] ?? null,
                    $exif['GPS']['GPSLatitudeRef'] ?? 'N'
                );
                $data['longitude'] = self::getGpsCoordinate(
                    $exif['GPS']['GPSLongitude'] ?? null,
                    $exif['GPS']['GPSLongitudeRef'] ?? 'E'
                );
            }

            // Çekim zamanını çıkar
            if (isset($exif['EXIF']['DateTimeOriginal'])) {
                $data['taken_at'] = date('Y-m-d H:i:s', strtotime($exif['EXIF']['DateTimeOriginal']));
            }

        } catch (\Exception $e) {
            // EXIF okuma hatası - sessizce geç
        }

        return $data;
    }

    /**
     * GPS koordinatını ondalık formata çevir
     */
    protected static function getGpsCoordinate(?array $coordinate, string $ref): ?float
    {
        if (!$coordinate) {
            return null;
        }

        $degrees = self::fractionToFloat($coordinate[0] ?? 0);
        $minutes = self::fractionToFloat($coordinate[1] ?? 0);
        $seconds = self::fractionToFloat($coordinate[2] ?? 0);

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        if ($ref === 'S' || $ref === 'W') {
            $decimal = -$decimal;
        }

        return round($decimal, 8);
    }

    /**
     * Kesirli değeri ondalığa çevir
     */
    protected static function fractionToFloat($value): float
    {
        if (is_string($value) && str_contains($value, '/')) {
            $parts = explode('/', $value);
            return (float) $parts[0] / (float) ($parts[1] ?? 1);
        }

        return (float) $value;
    }
}
