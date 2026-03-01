<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Zamanlanmış görevleri tanımla
     */
    protected function schedule(Schedule $schedule): void
    {
        // Vardiya bitiş + 30 dk sonra kurye kapatmadıysa otomatik kapat
        $schedule->command('shifts:auto-close-overdue')->everyFiveMinutes();
    }

    /**
     * Artisan komutlarını kaydet
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
