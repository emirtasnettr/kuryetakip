<?php

namespace App\Console\Commands;

use App\Models\ScheduledShift;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AssignCouriersToShifts extends Command
{
    protected $signature = 'shifts:assign-couriers 
                            {--date= : Sadece bu tarihteki vardiyalar (Y-m-d)}
                            {--dry-run : Sadece listele, atama yapma}';

    protected $description = 'Ataması eksik planlı vardiyalara bölge kuryelerinden kurye atar';

    public function handle(): int
    {
        $query = ScheduledShift::with(['region', 'assignments'])
            ->whereIn('status', [ScheduledShift::STATUS_DRAFT, ScheduledShift::STATUS_PUBLISHED]);

        if ($date = $this->option('date')) {
            $query->whereDate('shift_date', $date);
        }

        $shifts = $query->orderBy('shift_date')->orderBy('start_time')->get();
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info(' [DRY RUN] Atama yapılmayacak, sadece listelenecek.');
        }

        $assignedTotal = 0;
        $adminId = 1;

        foreach ($shifts as $shift) {
            $currentCount = $shift->validAssignments()->count();
            $needed = $shift->required_couriers - $currentCount;

            if ($needed <= 0) {
                continue;
            }

            $region = $shift->region;
            if (!$region) {
                $this->warn("  Vardiya #{$shift->id} bölgesiz, atlanıyor.");
                continue;
            }

            $couriers = User::whereHas('courierRegions', fn($q) => $q->where('region_id', $region->id))
                ->whereHas('role', fn($q) => $q->where('name', Role::COURIER))
                ->active()
                ->orderBy('name')
                ->get();

            $assignedIds = $shift->assignments()->pluck('courier_id')->toArray();
            $shiftStart = Carbon::parse($shift->start_time)->format('H:i');
            $shiftEnd = Carbon::parse($shift->end_time)->format('H:i');

            $busyIds = ShiftAssignment::where('status', '!=', ShiftAssignment::STATUS_CANCELLED)
                ->whereNull('actual_end_time')
                ->whereHas('scheduledShift', function ($q) use ($shift, $shiftStart, $shiftEnd) {
                    $q->whereDate('shift_date', $shift->shift_date)
                        ->where('id', '!=', $shift->id)
                        ->where('start_time', '<', $shiftEnd)
                        ->where('end_time', '>', $shiftStart);
                })
                ->pluck('courier_id')
                ->toArray();

            $available = $couriers->filter(function ($c) use ($assignedIds, $busyIds) {
                return !in_array($c->id, $assignedIds) && !in_array($c->id, $busyIds);
            })->take($needed);

            if ($available->isEmpty()) {
                $this->warn("  {$shift->shift_date} {$shiftStart}-{$shiftEnd} {$region->name}: Yeterli uygun kurye yok (gereken: {$needed}).");
                continue;
            }

            $dateStr = $shift->shift_date;
            $this->line("  {$dateStr} {$shiftStart}-{$shiftEnd} {$region->name}: {$currentCount}/{$shift->required_couriers} → +{$available->count()} kurye atanıyor.");

            if (!$dryRun) {
                foreach ($available as $courier) {
                    ShiftAssignment::create([
                        'scheduled_shift_id' => $shift->id,
                        'courier_id' => $courier->id,
                        'assigned_by' => $adminId,
                        'status' => ShiftAssignment::STATUS_ASSIGNED,
                    ]);
                    $assignedTotal++;
                    $this->info("    ✓ {$courier->name}");
                }
            } else {
                foreach ($available as $courier) {
                    $this->info("    [dry] {$courier->name}");
                }
                $assignedTotal += $available->count();
            }
        }

        if ($assignedTotal > 0) {
            $this->info($dryRun ? "\n[DRY RUN] Toplam {$assignedTotal} atama yapılacaktı." : "\n✓ Toplam {$assignedTotal} kurye ataması yapıldı.");
        } else {
            $this->info('Eksik ataması olan vardiya bulunamadı veya uygun kurye yok.');
        }

        return 0;
    }
}
