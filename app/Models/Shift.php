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
        'region_id',
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
        'auto_closed_at',
        'photo_compliance_status',
    ];

    // Vardiya uyumluluk durumları (hakediş)
    public const PHOTO_COMPLIANCE_PENDING = 'pending_review';
    public const PHOTO_COMPLIANCE_APPROVED = 'bonus_approved';
    public const PHOTO_COMPLIANCE_NO_BONUS = 'no_bonus';      // İncelendi, prim verilmedi
    public const PHOTO_COMPLIANCE_RE_REQUESTED = 're_requested';

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
        'auto_closed_at' => 'datetime',
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
     * Vardiyanın yapıldığı bölge (hakediş bölge ayarları için)
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
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
            'photo_compliance_status' => self::PHOTO_COMPLIANCE_PENDING,
        ]);
    }

    /**
     * Vardiyayı sistem tarafından otomatik kapat (bitiş + 30 dk sonra kurye kapatmadıysa)
     * Paket sayısı 0 yazılır.
     */
    public function completeBySystem(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $endTime = now();
        $totalMinutes = $this->started_at->diffInMinutes($endTime);

        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'ended_at' => $endTime,
            'package_count' => 0,
            'total_minutes' => $totalMinutes,
            'auto_closed_at' => $endTime,
            'photo_compliance_status' => self::PHOTO_COMPLIANCE_PENDING,
            'admin_notes' => ($this->admin_notes ? $this->admin_notes . "\n\n" : '')
                . '[Sistem tarafından otomatik kapatıldı - ' . $endTime->format('d.m.Y H:i') . ']',
        ]);
    }

    /**
     * Vardiya iptal et
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
     * Vardiya uyumluluk incelemesi bekleyen vardiyalar
     */
    public function scopePhotoCompliancePending($query)
    {
        return $query->whereIn('photo_compliance_status', [self::PHOTO_COMPLIANCE_PENDING, self::PHOTO_COMPLIANCE_RE_REQUESTED]);
    }

    /**
     * Hakediş saatlik kazanç (total_minutes * saatlik ücret / 60)
     */
    public function getHourlyEarningsAttribute(): float
    {
        $settings = \App\Models\SettlementSetting::get();
        $minutes = $this->total_minutes ?? 0;
        return round(($minutes / 60) * (float) $settings->hourly_rate, 2);
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
            'region_id' => $data['region_id'] ?? null,
            'status' => self::STATUS_ACTIVE,
            'started_at' => now(),
            'start_latitude' => $data['latitude'] ?? null,
            'start_longitude' => $data['longitude'] ?? null,
            'start_address' => $data['address'] ?? null,
        ]);
    }
}
