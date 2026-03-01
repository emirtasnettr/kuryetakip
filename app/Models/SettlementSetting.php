<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettlementSetting extends Model
{
    protected $fillable = [
        'region_id', 'hourly_rate', 'photo_compliance_bonus', 'package_rate', 'vat_rate',
        'has_guaranteed_package', 'guaranteed_packages_per_hour', 'max_guaranteed_packages_per_shift',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'photo_compliance_bonus' => 'decimal:2',
        'package_rate' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'has_guaranteed_package' => 'boolean',
        'guaranteed_packages_per_hour' => 'decimal:2',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /** KDV hariç tutar (girilen tutarlar KDV dahil kabul edilir) */
    public function toExclVat(float $amountInclVat): float
    {
        $rate = (float) ($this->vat_rate ?? 0);
        if ($rate <= 0) {
            return $amountInclVat;
        }
        return round($amountInclVat / (1 + $rate / 100), 2);
    }

    /**
     * Bölgeye göre ayar getir. region_id null ise varsayılan (bölgesiz) ayar.
     */
    public static function getForRegion(?int $regionId): self
    {
        $query = $regionId
            ? self::where('region_id', $regionId)
            : self::whereNull('region_id');

        $row = $query->first();
        if (!$row) {
            $row = self::create([
                'region_id' => $regionId,
                'hourly_rate' => 0,
                'photo_compliance_bonus' => 0,
                'package_rate' => 0,
                'vat_rate' => 18,
                'has_guaranteed_package' => false,
                'guaranteed_packages_per_hour' => null,
                'max_guaranteed_packages_per_shift' => null,
            ]);
        }
        return $row;
    }

    /** Geriye uyumluluk: tek global ayar (varsayılan) */
    public static function get(): self
    {
        return self::getForRegion(null);
    }
}
