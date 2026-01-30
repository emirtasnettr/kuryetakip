<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Rol Model
 * 
 * Sistem rollerini tanımlar:
 * - courier: Kurye
 * - operation_specialist: Operasyon Uzmanı
 * - operation_manager: Operasyon Yöneticisi
 * - business_partner: İş Ortağı
 * - system_admin: Sistem Yöneticisi
 */
class Role extends Model
{
    use HasFactory;

    // Rol sabitleri
    public const COURIER = 'courier';
    public const OPERATION_SPECIALIST = 'operation_specialist';
    public const OPERATION_MANAGER = 'operation_manager';
    public const BUSINESS_PARTNER = 'business_partner';
    public const SYSTEM_ADMIN = 'system_admin';

    /**
     * Toplu atama yapılabilecek alanlar
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_active',
    ];

    /**
     * Tip dönüşümleri
     */
    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Bu role sahip kullanıcılar
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Rol adına göre bul
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Panel erişimi olan roller
     */
    public static function panelRoles(): array
    {
        return [
            self::OPERATION_SPECIALIST,
            self::OPERATION_MANAGER,
            self::BUSINESS_PARTNER,
            self::SYSTEM_ADMIN,
        ];
    }

    /**
     * Bu rol panel erişimine sahip mi?
     */
    public function hasPanelAccess(): bool
    {
        return in_array($this->name, self::panelRoles());
    }

    /**
     * Bu rol kurye mi?
     */
    public function isCourier(): bool
    {
        return $this->name === self::COURIER;
    }

    /**
     * Bu rol sistem yöneticisi mi?
     */
    public function isSystemAdmin(): bool
    {
        return $this->name === self::SYSTEM_ADMIN;
    }
}
