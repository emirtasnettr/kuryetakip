<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Kullanıcı Model
 * 
 * Tüm sistem kullanıcılarını temsil eder.
 * Rol bazlı yetkilendirme ile farklı işlevler kazanır.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Toplu atama yapılabilecek alanlar
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role_id',
        'partner_id',
        'employee_code',
        'vehicle_type',
        'vehicle_plate',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * Gizlenecek alanlar
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Tip dönüşümleri
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // ==================== İLİŞKİLER ====================

    /**
     * Kullanıcının rolü
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Kuryenin bağlı olduğu iş ortağı
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    /**
     * İş ortağının kuryeleri
     */
    public function couriers(): HasMany
    {
        return $this->hasMany(User::class, 'partner_id');
    }

    /**
     * Kuryenin çalıştığı ilçeler
     */
    public function courierDistricts(): BelongsToMany
    {
        return $this->belongsToMany(District::class, 'courier_districts')
            ->withPivot(['assigned_by', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Kullanıcının yetkili olduğu ilçeler (operasyon için)
     */
    public function authorizedDistricts(): BelongsToMany
    {
        return $this->belongsToMany(District::class, 'user_districts')
            ->withPivot(['access_level'])
            ->withTimestamps();
    }

    /**
     * Kuryenin vardiyaları
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    // ==================== ROL KONTROL METODLARİ ====================

    /**
     * Kullanıcı kurye mi?
     */
    public function isCourier(): bool
    {
        return $this->role?->name === Role::COURIER;
    }

    /**
     * Kullanıcı operasyon uzmanı mı?
     */
    public function isOperationSpecialist(): bool
    {
        return $this->role?->name === Role::OPERATION_SPECIALIST;
    }

    /**
     * Kullanıcı operasyon yöneticisi mi?
     */
    public function isOperationManager(): bool
    {
        return $this->role?->name === Role::OPERATION_MANAGER;
    }

    /**
     * Kullanıcı iş ortağı mı?
     */
    public function isBusinessPartner(): bool
    {
        return $this->role?->name === Role::BUSINESS_PARTNER;
    }

    /**
     * Kullanıcı sistem yöneticisi mi?
     */
    public function isSystemAdmin(): bool
    {
        return $this->role?->name === Role::SYSTEM_ADMIN;
    }

    /**
     * Kullanıcı panele erişebilir mi?
     */
    public function canAccessPanel(): bool
    {
        return $this->role?->hasPanelAccess() ?? false;
    }

    /**
     * Kullanıcı operasyon tarafı mı? (uzman veya yönetici)
     */
    public function isOperationStaff(): bool
    {
        return $this->isOperationSpecialist() || $this->isOperationManager();
    }

    // ==================== VARDİYA METODLARİ ====================

    /**
     * Aktif vardiyası var mı?
     */
    public function hasActiveShift(): bool
    {
        return $this->activeShift() !== null;
    }

    /**
     * Aktif vardiyayı getir
     */
    public function activeShift(): ?Shift
    {
        return $this->shifts()
            ->where('status', 'active')
            ->latest('started_at')
            ->first();
    }

    /**
     * Bugünkü vardiyaları getir
     */
    public function todayShifts()
    {
        return $this->shifts()
            ->whereDate('started_at', today())
            ->orderBy('started_at', 'desc');
    }

    // ==================== İLÇE YETKİ METODLARİ ====================

    /**
     * Belirtilen ilçeye yetkisi var mı?
     */
    public function hasDistrictAccess(int $districtId): bool
    {
        // Sistem yöneticisi her yere erişebilir
        if ($this->isSystemAdmin()) {
            return true;
        }

        // İş ortağı kendi kuryelerini görebilir (ilçe bazlı değil)
        if ($this->isBusinessPartner()) {
            return true;
        }

        // Operasyon uzmanı/yöneticisi yetkili ilçelerini kontrol et
        if ($this->isOperationStaff()) {
            return $this->authorizedDistricts()->where('districts.id', $districtId)->exists();
        }

        // Kurye kendi ilçelerini kontrol et
        if ($this->isCourier()) {
            return $this->courierDistricts()->where('districts.id', $districtId)->exists();
        }

        return false;
    }

    /**
     * Görüntüleyebileceği kuryeleri getir
     */
    public function getAccessibleCouriers()
    {
        // Sistem yöneticisi tüm kuryeleri görebilir
        if ($this->isSystemAdmin()) {
            return User::whereHas('role', fn($q) => $q->where('name', Role::COURIER));
        }

        // İş ortağı sadece kendi kuryelerini görebilir
        if ($this->isBusinessPartner()) {
            return $this->couriers();
        }

        // Operasyon - yetkili ilçelerdeki kuryeleri görebilir
        if ($this->isOperationStaff()) {
            $districtIds = $this->authorizedDistricts()->pluck('districts.id');
            
            return User::whereHas('role', fn($q) => $q->where('name', Role::COURIER))
                ->whereHas('courierDistricts', fn($q) => $q->whereIn('district_id', $districtIds));
        }

        // Varsayılan: hiçbir şey döndürme
        return User::whereRaw('1 = 0');
    }

    // ==================== SCOPE'LAR ====================

    /**
     * Aktif kullanıcıları filtrele
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Role göre filtrele
     */
    public function scopeWithRole($query, string $roleName)
    {
        return $query->whereHas('role', fn($q) => $q->where('name', $roleName));
    }

    /**
     * Kuryeleri filtrele
     */
    public function scopeCouriers($query)
    {
        return $query->withRole(Role::COURIER);
    }

    // ==================== YARDIMCI METODLAR ====================

    /**
     * Son giriş bilgilerini güncelle
     */
    public function updateLoginInfo(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    /**
     * Tam bilgi (ad + rol) getir
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->role?->display_name})";
    }
}
