<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * Planlı Vardiya Model
 * 
 * Sistem yöneticisinin önceden planladığı vardiyaları yönetir.
 */
class ScheduledShift extends Model
{
    use HasFactory, SoftDeletes;

    // Vardiya durumları
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    // Renk seçenekleri
    public const COLORS = [
        '#3B82F6' => 'Mavi',
        '#10B981' => 'Yeşil',
        '#F59E0B' => 'Turuncu',
        '#EF4444' => 'Kırmızı',
        '#8B5CF6' => 'Mor',
        '#EC4899' => 'Pembe',
        '#6366F1' => 'İndigo',
        '#14B8A6' => 'Teal',
    ];

    /**
     * Toplu atama yapılabilecek alanlar
     */
    protected $fillable = [
        'region_id',
        'district_id',
        'created_by',
        'shift_date',
        'start_time',
        'end_time',
        'required_couriers',
        'status',
        'title',
        'notes',
        'color',
    ];

    /**
     * Tip dönüşümleri
     */
    protected $casts = [
        'shift_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'required_couriers' => 'integer',
    ];

    // ==================== İLİŞKİLER ====================

    /**
     * Vardiyanın bölgesi/ilçesi (eski tek bölge - geriye uyumluluk)
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Vardiyanın bölgesi
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Vardiyanın bölgeleri (çoklu bölge desteği - eski sistem)
     */
    public function districts(): BelongsToMany
    {
        return $this->belongsToMany(District::class, 'scheduled_shift_districts')
            ->withTimestamps();
    }

    /**
     * Vardiyayı oluşturan kullanıcı
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Vardiya atamaları
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    /**
     * Aktif atamalar (iptal edilmemiş ve erken bitirilmemiş)
     */
    public function activeAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class)
            ->whereNotIn('status', ['cancelled'])
            ->whereNull('actual_end_time'); // Erken bitirilenleri dahil etme
    }
    
    /**
     * Tüm geçerli atamalar (iptal edilmemiş, erken bitirenler dahil)
     */
    public function validAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class)
            ->whereNotIn('status', ['cancelled']);
    }

    /**
     * Atanan kuryeler
     */
    public function assignedCouriers()
    {
        return $this->belongsToMany(User::class, 'shift_assignments', 'scheduled_shift_id', 'courier_id')
            ->withPivot(['status', 'assigned_by', 'notes', 'confirmed_at', 'started_at', 'completed_at'])
            ->withTimestamps();
    }

    // ==================== HESAPLAMALAR ====================

    /**
     * Atanan kurye sayısı
     */
    public function getAssignedCountAttribute(): int
    {
        return $this->activeAssignments()->count();
    }

    /**
     * Kalan kapasite
     */
    public function getRemainingCapacityAttribute(): int
    {
        return max(0, $this->required_couriers - $this->assigned_count);
    }

    /**
     * Kapasite dolu mu?
     */
    public function isFullyStaffed(): bool
    {
        return $this->assigned_count >= $this->required_couriers;
    }

    /**
     * Vardiya süresi (dakika).
     * Ham DB değerleri kullanılır; cast (H:i vs H:i:s) nedeniyle 0 çıkmaması için.
     */
    public function getDurationInMinutesAttribute(): int
    {
        $rawStart = $this->getRawOriginal('start_time');
        $rawEnd = $this->getRawOriginal('end_time');
        if ($rawStart === null || $rawEnd === null) {
            return 0;
        }
        $start = Carbon::parse($rawStart);
        $end = Carbon::parse($rawEnd);
        if ($end->lt($start)) {
            $end = $end->copy()->addDay();
        }
        return (int) $start->diffInMinutes($end);
    }

    /**
     * Vardiya süresi formatlanmış
     */
    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->duration_in_minutes;
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours} saat {$mins} dk";
        } elseif ($hours > 0) {
            return "{$hours} saat";
        }

        return "{$mins} dakika";
    }

    /**
     * Bölge isimlerini getir
     */
    public function getDistrictNamesAttribute(): string
    {
        // Önce çoklu bölgeleri kontrol et
        if ($this->relationLoaded('districts') && $this->districts->isNotEmpty()) {
            return $this->districts->pluck('name')->join(', ');
        }
        
        // Yoksa tek bölgeyi kullan (geriye uyumluluk)
        if ($this->district) {
            return $this->district->name;
        }
        
        return '-';
    }

    /**
     * Bölge ID'lerini getir
     */
    public function getDistrictIdsAttribute(): array
    {
        if ($this->relationLoaded('districts') && $this->districts->isNotEmpty()) {
            return $this->districts->pluck('id')->toArray();
        }
        
        return $this->district_id ? [$this->district_id] : [];
    }

    /**
     * Başlık veya otomatik oluşturulan isim
     */
    public function getDisplayTitleAttribute(): string
    {
        if ($this->title) {
            return $this->title;
        }

        $startTime = Carbon::parse($this->start_time)->format('H:i');
        $endTime = Carbon::parse($this->end_time)->format('H:i');
        $regionName = $this->region?->name ?? 'Bölge Yok';
        
        return "{$regionName} ({$startTime} - {$endTime})";
    }

    /**
     * Takvim için event objesi
     */
    public function toCalendarEvent(): array
    {
        $startTime = Carbon::parse($this->start_time)->format('H:i');
        $endTime = Carbon::parse($this->end_time)->format('H:i');
        
        return [
            'id' => $this->id,
            'title' => $this->display_title,
            'start' => $this->shift_date->format('Y-m-d') . 'T' . Carbon::parse($this->start_time)->format('H:i:s'),
            'end' => $this->shift_date->format('Y-m-d') . 'T' . Carbon::parse($this->end_time)->format('H:i:s'),
            'color' => $this->color,
            'extendedProps' => [
                'region_id' => $this->region_id,
                'region_name' => $this->region?->name,
                'required_couriers' => $this->required_couriers,
                'assigned_count' => $this->assigned_count,
                'remaining_capacity' => $this->remaining_capacity,
                'status' => $this->status,
                'notes' => $this->notes,
            ],
        ];
    }

    // ==================== DURUM KONTROL ====================

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Geçmiş tarihli mi?
     */
    public function isPast(): bool
    {
        return $this->shift_date->lt(today());
    }

    /**
     * Bugün mü?
     */
    public function isToday(): bool
    {
        return $this->shift_date->isToday();
    }

    // ==================== SCOPE'LAR ====================

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_PUBLISHED]);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('shift_date', $date);
    }

    public function scopeForDistrict($query, $districtId)
    {
        return $query->whereHas('districts', fn($q) => $q->where('districts.id', $districtId));
    }

    public function scopeForDistricts($query, array $districtIds)
    {
        return $query->whereHas('districts', fn($q) => $q->whereIn('districts.id', $districtIds));
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('shift_date', [$startDate, $endDate]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('shift_date', '>=', today());
    }

    public function scopeNeedsStaff($query)
    {
        return $query->whereRaw('required_couriers > (
            SELECT COUNT(*) FROM shift_assignments 
            WHERE shift_assignments.scheduled_shift_id = scheduled_shifts.id 
            AND shift_assignments.status != "cancelled"
        )');
    }
}
