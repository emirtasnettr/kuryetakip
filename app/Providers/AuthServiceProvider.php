<?php

namespace App\Providers;

use App\Models\District;
use App\Models\Shift;
use App\Models\User;
use App\Policies\DistrictPolicy;
use App\Policies\ShiftPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Authentication & Authorization Service Provider
 * 
 * Policy ve Gate tanımlamalarını yapar.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Model -> Policy eşleştirmeleri
     */
    protected $policies = [
        Shift::class => ShiftPolicy::class,
        User::class => UserPolicy::class,
        District::class => DistrictPolicy::class,
    ];

    /**
     * Yetkilendirme servislerini kaydet
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // ==================== GATE TANIMLARI ====================

        /**
         * Panel erişim yetkisi
         */
        Gate::define('access-panel', function (User $user) {
            return $user->canAccessPanel();
        });

        /**
         * Mobil (kurye) erişim yetkisi
         */
        Gate::define('access-mobile', function (User $user) {
            return $user->isCourier() && $user->is_active;
        });

        /**
         * Kurye yönetimi yetkisi
         */
        Gate::define('manage-couriers', function (User $user) {
            return $user->isOperationManager() || 
                   $user->isBusinessPartner() || 
                   $user->isSystemAdmin();
        });

        /**
         * İlçe yönetimi yetkisi
         */
        Gate::define('manage-districts', function (User $user) {
            return $user->isOperationManager() || $user->isSystemAdmin();
        });

        /**
         * Rapor görüntüleme yetkisi
         */
        Gate::define('view-reports', function (User $user) {
            return $user->isOperationStaff() || 
                   $user->isOperationManager() || 
                   $user->isSystemAdmin();
        });

        /**
         * Detaylı rapor görüntüleme yetkisi
         */
        Gate::define('view-detailed-reports', function (User $user) {
            return $user->isOperationManager() || $user->isSystemAdmin();
        });

        /**
         * Sistem ayarları yetkisi
         */
        Gate::define('manage-settings', function (User $user) {
            return $user->isSystemAdmin();
        });

        /**
         * Rol yönetimi yetkisi
         */
        Gate::define('manage-roles', function (User $user) {
            return $user->isSystemAdmin();
        });

        /**
         * Kullanıcı yönetimi yetkisi (tüm roller)
         */
        Gate::define('manage-users', function (User $user) {
            return $user->isSystemAdmin();
        });

        /**
         * Tüm kullanıcıları görme yetkisi (ilçe kısıtlaması olmadan)
         */
        Gate::define('view-all-users', function (User $user) {
            return $user->isSystemAdmin();
        });

        /**
         * Tüm vardiyaları görme yetkisi (ilçe kısıtlaması olmadan)
         */
        Gate::define('view-all-shifts', function (User $user) {
            return $user->isSystemAdmin();
        });
    }
}
