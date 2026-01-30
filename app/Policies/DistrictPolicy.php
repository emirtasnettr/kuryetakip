<?php

namespace App\Policies;

use App\Models\District;
use App\Models\User;

/**
 * İlçe Yetkilendirme Policy
 * 
 * İlçe yönetimi için yetkilendirme kurallarını tanımlar.
 */
class DistrictPolicy
{
    /**
     * Tüm yetkileri atla (admin için)
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSystemAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * İlçe listesini görüntüleme yetkisi
     */
    public function viewAny(User $user): bool
    {
        return $user->canAccessPanel();
    }

    /**
     * Tek bir ilçeyi görüntüleme yetkisi
     */
    public function view(User $user, District $district): bool
    {
        // Panel erişimi olan herkes görebilir
        if (!$user->canAccessPanel()) {
            return false;
        }

        // İş ortağı tüm ilçeleri görebilir (kurye atamak için)
        if ($user->isBusinessPartner()) {
            return true;
        }

        // Operasyon uzmanı/yöneticisi yetkili ilçelerini görebilir
        if ($user->isOperationStaff()) {
            return $user->authorizedDistricts()->where('districts.id', $district->id)->exists();
        }

        return false;
    }

    /**
     * Yeni ilçe oluşturma yetkisi
     */
    public function create(User $user): bool
    {
        // Sadece operasyon yöneticisi
        return $user->isOperationManager();
    }

    /**
     * İlçe güncelleme yetkisi
     */
    public function update(User $user, District $district): bool
    {
        // Sadece operasyon yöneticisi
        return $user->isOperationManager();
    }

    /**
     * İlçe silme yetkisi
     */
    public function delete(User $user, District $district): bool
    {
        // Sadece operasyon yöneticisi ve ilçede kurye/vardiya yoksa
        if (!$user->isOperationManager()) {
            return false;
        }

        // İlçede aktif kurye varsa silinemez
        if ($district->couriers()->exists()) {
            return false;
        }

        return true;
    }
}
