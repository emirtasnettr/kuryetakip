<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vardiya Log Model
 * 
 * Vardiya işlemlerinin detaylı loglarını tutar.
 * Güvenlik ve denetim amaçlı kullanılır.
 */
class ShiftLog extends Model
{
    use HasFactory;

    // Log tipleri
    public const TYPE_START = 'start';
    public const TYPE_END = 'end';
    public const TYPE_PAUSE = 'pause';
    public const TYPE_RESUME = 'resume';
    public const TYPE_UPDATE = 'update';

    /**
     * Toplu atama yapılabilecek alanlar
     */
    protected $fillable = [
        'shift_id',
        'type',
        'latitude',
        'longitude',
        'address',
        'accuracy',
        'ip_address',
        'user_agent',
        'device_id',
        'device_model',
        'os_version',
        'app_version',
        'metadata',
        'logged_at',
    ];

    /**
     * Tip dönüşümleri
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'integer',
        'metadata' => 'array',
        'logged_at' => 'datetime',
    ];

    // ==================== İLİŞKİLER ====================

    /**
     * Log'un ait olduğu vardiya
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    // ==================== YARDIMCI METODLAR ====================

    /**
     * Konum bilgisi var mı?
     */
    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Google Maps linki
     */
    public function getLocationUrlAttribute(): ?string
    {
        if (!$this->hasLocation()) {
            return null;
        }

        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /**
     * Log tipinin Türkçe karşılığı
     */
    public function getTypeDisplayAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_START => 'Başlangıç',
            self::TYPE_END => 'Bitiş',
            self::TYPE_PAUSE => 'Duraklama',
            self::TYPE_RESUME => 'Devam',
            self::TYPE_UPDATE => 'Güncelleme',
            default => $this->type,
        };
    }

    // ==================== SCOPE'LAR ====================

    /**
     * Tipe göre filtrele
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ==================== STATİK METODLAR ====================

    /**
     * HTTP request'ten log oluştur
     */
    public static function createFromRequest(Shift $shift, string $type, array $data, $request = null): self
    {
        return self::create([
            'shift_id' => $shift->id,
            'type' => $type,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'address' => $data['address'] ?? null,
            'accuracy' => $data['accuracy'] ?? null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'device_id' => $data['device_id'] ?? null,
            'device_model' => $data['device_model'] ?? null,
            'os_version' => $data['os_version'] ?? null,
            'app_version' => $data['app_version'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'logged_at' => now(),
        ]);
    }
}
