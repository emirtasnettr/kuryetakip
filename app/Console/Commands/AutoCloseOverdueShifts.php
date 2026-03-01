<?php

namespace App\Console\Commands;

use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ScheduledShift;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Vardiya bitiş saatinden 30 dakika sonra kurye vardiyayı kendisi sonlandırmadıysa
 * sistem tarafından otomatik kapatılır. Paket sayısı 0 yazılır.
 */
class AutoCloseOverdueShifts extends Command
{
    protected $signature = 'shifts:auto-close-overdue';

    protected $description = 'Bitiş saatinden 30 dk sonra kurye kapatmadıysa vardiyayı otomatik kapat (paket=0)';

    public function handle(): int
    {
        $now = Carbon::now();

        // Başlatılmış ama henüz tamamlanmamış atamalar
        $overdue = ShiftAssignment::query()
            ->where('status', ShiftAssignment::STATUS_STARTED)
            ->whereNull('completed_at')
            ->whereNotNull('actual_shift_id')
            ->whereNotNull('started_at')
            ->with(['scheduledShift', 'actualShift'])
            ->get();

        $closed = 0;

        foreach ($overdue as $assignment) {
            $scheduled = $assignment->scheduledShift;
            if (!$scheduled) {
                continue;
            }

            $deadline = $this->getAutoCloseDeadline($scheduled);
            if ($now->lt($deadline)) {
                continue; // Henüz 30 dk dolmadı
            }

            $shift = $assignment->actualShift;
            if (!$shift || !$shift->isActive()) {
                continue;
            }

            DB::beginTransaction();
            try {
                $shift->completeBySystem();

                $assignment->update([
                    'status' => ShiftAssignment::STATUS_COMPLETED,
                    'completed_at' => $now,
                    'actual_end_time' => $now->format('H:i'),
                    'auto_closed_at' => $now,
                ]);

                $closed++;
                $this->info("Vardiya otomatik kapatıldı: Atama #{$assignment->id}, Shift #{$shift->id}, Kurye: {$assignment->courier?->name}");
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("Atama #{$assignment->id} kapatılırken hata: " . $e->getMessage());
            }
        }

        if ($closed > 0) {
            $this->info("Toplam {$closed} vardiya otomatik kapatıldı.");
        }

        return self::SUCCESS;
    }

    /**
     * Planlı vardiya bitiş saatine 30 dakika ekleyerek otomatik kapanış deadline'ını hesaplar.
     */
    private function getAutoCloseDeadline(ScheduledShift $scheduled): Carbon
    {
        $shiftEnd = $scheduled->shift_date->copy()->setTimeFromTimeString(
            Carbon::parse($scheduled->end_time)->format('H:i:s')
        );

        // Gece yarısını geçen vardiya: bitiş saati başlangıçtan küçükse ertesi gün
        $startTime = Carbon::parse($scheduled->start_time);
        $endTime = Carbon::parse($scheduled->end_time);
        if ($endTime->lte($startTime)) {
            $shiftEnd->addDay();
        }

        return $shiftEnd->addMinutes(30);
    }
}
