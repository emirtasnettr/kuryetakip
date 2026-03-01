<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vardiya Atama Model
 * 
 * Planlı vardiyalara atanan kuryeleri yönetir.
 */
class ShiftAssignment extends Model
{
    use HasFactory;

    // Atama durumları
    public const STATUS_ASSIGNED = 'assigned';      // Atandı, kurye henüz onaylamadı
    public const STATUS_CONFIRMED = 'confirmed';    // Kurye onayladı
    public const STATUS_STARTED = 'started';        // Kurye vardiyayı başlattı
    public const STATUS_COMPLETED = 'completed';    // Vardiya tamamlandı
    public const STATUS_CANCELLED = 'cancelled';    // İptal edildi
    public const STATUS_NO_SHOW = 'no_show';        // Kurye gelmedi

    /**
     * Toplu atama yapılabilecek alanlar
     */
    protected $fillable = [
        'scheduled_shift_id',
        'courier_id',
        'assigned_by',
        'status',
        'actual_shift_id',
        'notes',
        'confirmed_at',
        'started_at',
        'completed_at',
        'auto_closed_at',
        'actual_start_time',
        'actual_end_time',
        'end_reason',
    ];

    /**
     * Tip dönüşümleri
     */
    protected $casts = [
        'confirmed_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'auto_closed_at' => 'datetime',
    ];

    // ==================== İLİŞKİLER ====================

    /**
     * Planlı vardiya
     */
    public function scheduledShift(): BelongsTo
    {
        return $this->belongsTo(ScheduledShift::class);
    }

    /**
     * Atanan kurye
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Atamayı yapan kullanıcı
     */
    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Gerçek vardiya (kurye başlattığında)
     */
    public function actualShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'actual_shift_id');
    }

    // ==================== DURUM KONTROL ====================

    public function isAssigned(): bool
    {
        return $this->status === self::STATUS_ASSIGNED;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isStarted(): bool
    {
        return $this->status === self::STATUS_STARTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isNoShow(): bool
    {
        return $this->status === self::STATUS_NO_SHOW;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_ASSIGNED,
            self::STATUS_CONFIRMED,
            self::STATUS_STARTED,
        ]);
    }

    // ==================== DURUM DEĞİŞİKLİKLERİ ====================

    /**
     * Kuryenin onaylaması
     */
    public function confirm(): bool
    {
        if ($this->status !== self::STATUS_ASSIGNED) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Vardiyayı başlat
     */
    public function start(?Shift $actualShift = null): bool
    {
        if (!in_array($this->status, [self::STATUS_ASSIGNED, self::STATUS_CONFIRMED])) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_STARTED,
            'started_at' => now(),
            'actual_shift_id' => $actualShift?->id,
        ]);
    }

    /**
     * Vardiyayı tamamla
     */
    public function complete(): bool
    {
        if ($this->status !== self::STATUS_STARTED) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * İptal et
     */
    public function cancel(?string $reason = null): bool
    {
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            return false;
        }

        $notes = $this->notes;
        if ($reason) {
            $notes = ($notes ? $notes . "\n" : '') . "[İptal: " . now()->format('d.m.Y H:i') . "] " . $reason;
        }

        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $notes,
        ]);
    }

    /**
     * Gelmedi olarak işaretle
     */
    public function markNoShow(?string $reason = null): bool
    {
        if (!in_array($this->status, [self::STATUS_ASSIGNED, self::STATUS_CONFIRMED])) {
            return false;
        }

        $notes = $this->notes;
        if ($reason) {
            $notes = ($notes ? $notes . "\n" : '') . "[Gelmedi: " . now()->format('d.m.Y H:i') . "] " . $reason;
        }

        return $this->update([
            'status' => self::STATUS_NO_SHOW,
            'notes' => $notes,
        ]);
    }

    // ==================== YARDIMCI METODLAR ====================

    /**
     * Durum etiketi
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ASSIGNED => 'Atandı',
            self::STATUS_CONFIRMED => 'Onaylandı',
            self::STATUS_STARTED => 'Başladı',
            self::STATUS_COMPLETED => 'Tamamlandı',
            self::STATUS_CANCELLED => 'İptal',
            self::STATUS_NO_SHOW => 'Gelmedi',
            default => $this->status,
        };
    }

    /**
     * Durum rengi
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ASSIGNED => 'yellow',
            self::STATUS_CONFIRMED => 'blue',
            self::STATUS_STARTED => 'green',
            self::STATUS_COMPLETED => 'gray',
            self::STATUS_CANCELLED => 'red',
            self::STATUS_NO_SHOW => 'red',
            default => 'gray',
        };
    }

    // ==================== SCOPE'LAR ====================

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_ASSIGNED,
            self::STATUS_CONFIRMED,
            self::STATUS_STARTED,
        ]);
    }

    public function scopeForCourier($query, $courierId)
    {
        return $query->where('courier_id', $courierId);
    }

    public function scopeForScheduledShift($query, $scheduledShiftId)
    {
        return $query->where('scheduled_shift_id', $scheduledShiftId);
    }

    public function scopeNotCancelled($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    // ==================== ÇALIŞMA SÜRESİ HESAPLAMALARI ====================

    /**
     * Kuryenin fiili başlangıç saati
     * actual_start_time yoksa vardiya başlangıcını döndürür
     */
    public function getEffectiveStartTimeAttribute(): string
    {
        if ($this->actual_start_time) {
            return \Carbon\Carbon::parse($this->actual_start_time)->format('H:i');
        }
        
        $shiftTime = $this->scheduledShift?->start_time;
        return $shiftTime ? \Carbon\Carbon::parse($shiftTime)->format('H:i') : '00:00';
    }

    /**
     * Kuryenin fiili bitiş saati
     * actual_end_time yoksa vardiya bitişini döndürür
     */
    public function getEffectiveEndTimeAttribute(): string
    {
        if ($this->actual_end_time) {
            return \Carbon\Carbon::parse($this->actual_end_time)->format('H:i');
        }
        
        $shiftTime = $this->scheduledShift?->end_time;
        return $shiftTime ? \Carbon\Carbon::parse($shiftTime)->format('H:i') : '23:59';
    }

    /**
     * Kuryenin çalıştığı süre (dakika cinsinden)
     */
    public function getWorkedMinutesAttribute(): int
    {
        $start = \Carbon\Carbon::parse($this->effective_start_time);
        $end = \Carbon\Carbon::parse($this->effective_end_time);
        
        return $start->diffInMinutes($end);
    }

    /**
     * Kuryenin çalıştığı süre (saat:dakika formatında)
     */
    public function getWorkedDurationAttribute(): string
    {
        $minutes = $this->worked_minutes;
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        return sprintf('%d saat %d dk', $hours, $mins);
    }

    /**
     * Kuryenin çalıştığı süre (ondalık saat)
     */
    public function getWorkedHoursAttribute(): float
    {
        return round($this->worked_minutes / 60, 2);
    }

    /**
     * Kurye erken mi bitirmiş?
     */
    public function hasEndedEarly(): bool
    {
        return $this->actual_end_time !== null;
    }

    /**
     * Kurye geç mi başlamış?
     */
    public function hasStartedLate(): bool
    {
        return $this->actual_start_time !== null;
    }

    /**
     * Vardiyayı erken bitir (kurye değişikliği için)
     */
    public function endEarly(string $endTime, ?string $reason = null): bool
    {
        return $this->update([
            'actual_end_time' => $endTime,
            'end_reason' => $reason,
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }
}
