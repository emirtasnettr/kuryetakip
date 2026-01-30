<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;
use App\Models\Role;

/**
 * Vardiya Yetkilendirme Policy
 * 
 * Vardiya işlemleri için yetkilendirme kurallarını tanımlar.
 */
class ShiftPolicy
{
    /**
     * Tüm yetkileri atla (admin için)
     */
    public function before(User $user, string $ability): ?bool
    {
        // Sistem yöneticisi her şeyi yapabilir
        if ($user->isSystemAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Vardiya listesini görüntüleme yetkisi
     */
    public function viewAny(User $user): bool
    {
        // Kurye kendi vardiyalarını görebilir
        if ($user->isCourier()) {
            return true;
        }

        // Panel erişimi olan herkes liste görebilir
        return $user->canAccessPanel();
    }

    /**
     * Tek bir vardiyayı görüntüleme yetkisi
     */
    public function view(User $user, Shift $shift): bool
    {
        // Kurye sadece kendi vardiyasını görebilir
        if ($user->isCourier()) {
            return $shift->user_id === $user->id;
        }

        // İş ortağı sadece kendi kuryelerinin vardiyasını görebilir
        if ($user->isBusinessPartner()) {
            $courierIds = $user->couriers()->pluck('id');
            return $courierIds->contains($shift->user_id);
        }

        // Operasyon uzmanı/yöneticisi yetkili ilçeleri kontrol eder
        if ($user->isOperationStaff()) {
            // İlçe belirtilmemişse, kuryenin ilçelerine bak
            if ($shift->district_id) {
                return $user->hasDistrictAccess($shift->district_id);
            }

            // Kuryenin herhangi bir ilçesine yetkisi var mı?
            $courierDistrictIds = $shift->user->courierDistricts()->pluck('districts.id');
            $userDistrictIds = $user->authorizedDistricts()->pluck('districts.id');
            
            return $courierDistrictIds->intersect($userDistrictIds)->isNotEmpty();
        }

        return false;
    }

    /**
     * Yeni vardiya oluşturma yetkisi
     */
    public function create(User $user): bool
    {
        // Sadece kuryeler vardiya oluşturabilir
        if (!$user->isCourier()) {
            return false;
        }

        // Aktif bir vardiya varsa yeni başlatılamaz
        if ($user->hasActiveShift()) {
            return false;
        }

        // Kullanıcı aktif olmalı
        return $user->is_active;
    }

    /**
     * Vardiya başlatma yetkisi (create ile aynı)
     */
    public function start(User $user): bool
    {
        return $this->create($user);
    }

    /**
     * Vardiya güncelleme yetkisi
     */
    public function update(User $user, Shift $shift): bool
    {
        // Kurye sadece kendi aktif vardiyasını güncelleyebilir
        if ($user->isCourier()) {
            return $shift->user_id === $user->id && $shift->isActive();
        }

        // Operasyon yöneticisi yetkili ilçelerdeki vardiyaları güncelleyebilir
        if ($user->isOperationManager()) {
            return $this->view($user, $shift);
        }

        return false;
    }

    /**
     * Vardiya bitirme yetkisi
     */
    public function end(User $user, Shift $shift): bool
    {
        // Sadece kuryeler kendi aktif vardiyalarını bitirebilir
        if (!$user->isCourier()) {
            return false;
        }

        // Kendi vardiyası olmalı
        if ($shift->user_id !== $user->id) {
            return false;
        }

        // Vardiya aktif olmalı
        return $shift->isActive();
    }

    /**
     * Vardiya silme yetkisi
     */
    public function delete(User $user, Shift $shift): bool
    {
        // Sadece operasyon yöneticisi silebilir
        if (!$user->isOperationManager()) {
            return false;
        }

        return $this->view($user, $shift);
    }

    /**
     * Vardiya iptal etme yetkisi
     */
    public function cancel(User $user, Shift $shift): bool
    {
        // Kurye kendi aktif vardiyasını iptal edebilir
        if ($user->isCourier()) {
            return $shift->user_id === $user->id && $shift->isActive();
        }

        // Operasyon yöneticisi yetkili ilçedeki vardiyaları iptal edebilir
        if ($user->isOperationManager()) {
            return $this->view($user, $shift) && $shift->isActive();
        }

        return false;
    }

    /**
     * Yönetici notu ekleme yetkisi
     */
    public function addAdminNote(User $user, Shift $shift): bool
    {
        // Operasyon staff ve üzeri
        if (!$user->isOperationStaff() && !$user->isOperationManager()) {
            return false;
        }

        return $this->view($user, $shift);
    }
}
