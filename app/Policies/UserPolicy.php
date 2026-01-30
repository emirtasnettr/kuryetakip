<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;

/**
 * Kullanıcı Yetkilendirme Policy
 * 
 * Kullanıcı yönetimi için yetkilendirme kurallarını tanımlar.
 */
class UserPolicy
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
     * Kullanıcı listesini görüntüleme yetkisi
     */
    public function viewAny(User $user): bool
    {
        return $user->canAccessPanel();
    }

    /**
     * Tek bir kullanıcıyı görüntüleme yetkisi
     */
    public function view(User $user, User $model): bool
    {
        // Kendi profilini her zaman görebilir
        if ($user->id === $model->id) {
            return true;
        }

        // İş ortağı sadece kendi kuryelerini görebilir
        if ($user->isBusinessPartner()) {
            return $model->partner_id === $user->id;
        }

        // Operasyon uzmanı/yöneticisi yetkili ilçelerdeki kuryeleri görebilir
        if ($user->isOperationStaff()) {
            // Sadece kuryeleri kontrol et
            if (!$model->isCourier()) {
                return false;
            }

            // Kuryenin ilçelerine yetki var mı?
            $courierDistrictIds = $model->courierDistricts()->pluck('districts.id');
            $userDistrictIds = $user->authorizedDistricts()->pluck('districts.id');
            
            return $courierDistrictIds->intersect($userDistrictIds)->isNotEmpty();
        }

        return false;
    }

    /**
     * Yeni kullanıcı oluşturma yetkisi
     */
    public function create(User $user): bool
    {
        // Operasyon yöneticisi kurye oluşturabilir
        if ($user->isOperationManager()) {
            return true;
        }

        // İş ortağı kendi kuryesini oluşturabilir
        if ($user->isBusinessPartner()) {
            return true;
        }

        return false;
    }

    /**
     * Kullanıcı güncelleme yetkisi
     */
    public function update(User $user, User $model): bool
    {
        // Kendi profilini güncelleyebilir (sınırlı alanlar)
        if ($user->id === $model->id) {
            return true;
        }

        // İş ortağı kendi kuryesini güncelleyebilir
        if ($user->isBusinessPartner()) {
            return $model->partner_id === $user->id && $model->isCourier();
        }

        // Operasyon yöneticisi yetkili ilçedeki kuryeleri güncelleyebilir
        if ($user->isOperationManager()) {
            return $this->view($user, $model);
        }

        return false;
    }

    /**
     * Kullanıcı silme yetkisi
     */
    public function delete(User $user, User $model): bool
    {
        // Kendini silemez
        if ($user->id === $model->id) {
            return false;
        }

        // Sadece operasyon yöneticisi silebilir
        return $user->isOperationManager() && $this->view($user, $model);
    }

    /**
     * Kullanıcı rolünü değiştirme yetkisi
     */
    public function changeRole(User $user, User $model): bool
    {
        // Sadece sistem yöneticisi (before'da handle edilir)
        return false;
    }

    /**
     * Kullanıcıyı aktif/pasif yapma yetkisi
     */
    public function toggleActive(User $user, User $model): bool
    {
        // Kendini pasif yapamaz
        if ($user->id === $model->id) {
            return false;
        }

        // İş ortağı kendi kuryesini
        if ($user->isBusinessPartner()) {
            return $model->partner_id === $user->id;
        }

        // Operasyon yöneticisi
        return $user->isOperationManager() && $this->view($user, $model);
    }

    /**
     * Kuryeye ilçe atama yetkisi
     */
    public function assignDistrict(User $user, User $model): bool
    {
        // Sadece kuryeye atanabilir
        if (!$model->isCourier()) {
            return false;
        }

        // Operasyon yöneticisi
        if ($user->isOperationManager()) {
            return true;
        }

        // İş ortağı kendi kuryesine
        if ($user->isBusinessPartner()) {
            return $model->partner_id === $user->id;
        }

        return false;
    }
}
