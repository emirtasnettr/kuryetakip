<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * İlçe Model
 * 
 * Kuryelerin ve operasyon uzmanlarının çalışma bölgelerini tanımlar.
 */
class District extends Model
{
    use HasFactory;

    /**
     * Toplu atama yapılabilecek alanlar
     */
    protected $fillable = [
        'name',
        'city',
        'code',
        'is_active',
    ];

    /**
     * Tip dönüşümleri
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Bu ilçeye atanmış kuryeler
     */
    public function couriers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'courier_districts')
            ->withPivot(['assigned_by', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Bu ilçeye yetkili kullanıcılar (operasyon uzmanları vb.)
     */
    public function authorizedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_districts')
            ->withPivot(['access_level'])
            ->withTimestamps();
    }

    /**
     * Bu ilçede yapılan vardiyalar
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    /**
     * Aktif ilçeleri getir
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Şehre göre filtrele
     */
    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Tam adı getir (İlçe, Şehir formatında)
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name}, {$this->city}";
    }
}
