<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Event-listener eşleştirmeleri
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Olaylar için listener'ları kaydet
     */
    public function boot(): void
    {
        //
    }

    /**
     * Otomatik event discovery
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
