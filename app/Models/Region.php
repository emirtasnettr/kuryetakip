<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'city',
        'color',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ==================== İLİŞKİLER ====================

    /**
     * Bölgeye ait planlı vardiyalar
     */
    public function scheduledShifts(): HasMany
    {
        return $this->hasMany(ScheduledShift::class);
    }

    /**
     * Bölgeye atanmış kuryeler
     */
    public function couriers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'courier_regions')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    // ==================== ACCESSOR'LAR ====================

    /**
     * Kurye sayısını döndür
     */
    public function getCourierCountAttribute(): int
    {
        return $this->couriers()->count();
    }

    // ==================== SCOPE'LAR ====================

    /**
     * Sadece aktif bölgeler
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * İsme göre ara
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }
}
