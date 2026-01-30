<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * Vardiya Model
 * 
 * Kuryelerin vardiya kayıtlarını yönetir.
 */
class Shift extends Model
{
    use HasFactory, SoftDeletes;

    // Vardiya durumları
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Toplu atama yapılabilecek alanlar
     */
    protected $fillable = [
        'user_id',
        'district_id',
        'status',
        'started_at',
        'start_latitude',
        'start_longitude',
        'start_address',
        'ended_at',
        'end_latitude',
        'end_longitude',
        'end_address',
        'package_count',
        'total_minutes',
        'notes',
        'admin_notes',
    ];

    /**
     * Tip dönüşümleri
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'start_latitude' => 'decimal:8',
        'start_longitude' => 'decimal:8',
        'end_latitude' => 'decimal:8',
        'end_longitude' => 'decimal:8',
        'package_count' => 'integer',
        'total_minutes' => 'integer',
    ];

    // ==================== İLİŞKİLER ====================

    /**
     * Vardiyayı yapan kurye
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Aynı ilişki, daha anlamlı isimle
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Vardiyanın yapıldığı ilçe
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Vardiya logları
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ShiftLog::class)->orderBy('logged_at', 'asc');
    }

    /**
     * Vardiya fotoğrafları
     */
    public function photos(): HasMany
    {
        return $this->hasMany(ShiftPhoto::class);
    }

    /**
     * Başlangıç fotoğrafları
     */
    public function startPhotos(): HasMany
    {
        return $this->hasMany(ShiftPhoto::class)->where('type', 'start');
    }

    /**
     * Bitiş fotoğrafları
     */
    public function endPhotos(): HasMany
    {
        return $this->hasMany(ShiftPhoto::class)->where('type', 'end');
    }

    // ==================== DURUM KONTROL METODLARİ ====================

    /**
     * Vardiya aktif mi?
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Vardiya tamamlandı mı?
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Vardiya iptal edildi mi?
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // ==================== VARDİYA İŞLEMLERİ ====================

    /**
     * Vardiyayı tamamla
     */
    public function complete(array $data): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $endTime = now();
        $totalMinutes = $this->started_at->diffInMinutes($endTime);

        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'ended_at' => $endTime,
            'end_latitude' => $data['latitude'] ?? null,
            'end_longitude' => $data['longitude'] ?? null,
            'end_address' => $data['address'] ?? null,
            'package_count' => $data['package_count'] ?? null,
            'total_minutes' => $totalMinutes,
            'notes' => $data['notes'] ?? $this->notes,
        ]);
    }

    /**
     * Vardiyayı iptal et
     */
    public function cancel(?string $reason = null): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'ended_at' => now(),
            'admin_notes' => $reason,
        ]);
    }

    // ==================== HESAPLAMALAR ====================

    /**
     * Çalışma süresini hesapla (dakika)
     */
    public function getDurationInMinutes(): int
    {
        if (!$this->started_at) {
            return 0;
        }

        $endTime = $this->ended_at ?? now();
        return $this->started_at->diffInMinutes($endTime);
    }

    /**
     * Çalışma süresini formatla
     */
    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->getDurationInMinutes();
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return "{$hours} saat {$mins} dakika";
        }

        return "{$mins} dakika";
    }

    /**
     * Başlangıç konumunu Google Maps linki olarak getir
     */
    public function getStartLocationUrlAttribute(): ?string
    {
        if (!$this->start_latitude || !$this->start_longitude) {
            return null;
        }

        return "https://www.google.com/maps?q={$this->start_latitude},{$this->start_longitude}";
    }

    /**
     * Bitiş konumunu Google Maps linki olarak getir
     */
    public function getEndLocationUrlAttribute(): ?string
    {
        if (!$this->end_latitude || !$this->end_longitude) {
            return null;
        }

        return "https://www.google.com/maps?q={$this->end_latitude},{$this->end_longitude}";
    }

    // ==================== SCOPE'LAR ====================

    /**
     * Aktif vardiyaları filtrele
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Tamamlanan vardiyaları filtrele
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Bugünkü vardiyaları filtrele
     */
    public function scopeToday($query)
    {
        return $query->whereDate('started_at', today());
    }

    /**
     * Tarih aralığına göre filtrele
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('started_at', [$startDate, $endDate]);
    }

    /**
     * İlçeye göre filtrele
     */
    public function scopeInDistrict($query, int $districtId)
    {
        return $query->where('district_id', $districtId);
    }

    /**
     * Kuryeye göre filtrele
     */
    public function scopeForCourier($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ==================== STATİK METODLAR ====================

    /**
     * Yeni vardiya başlat
     */
    public static function startNew(User $courier, array $data): self
    {
        return self::create([
            'user_id' => $courier->id,
            'district_id' => $data['district_id'] ?? null,
            'status' => self::STATUS_ACTIVE,
            'started_at' => now(),
            'start_latitude' => $data['latitude'] ?? null,
            'start_longitude' => $data['longitude'] ?? null,
            'start_address' => $data['address'] ?? null,
        ]);
    }
}
